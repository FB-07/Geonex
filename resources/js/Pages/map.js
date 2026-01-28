let map;
let service;
let markers = [];
let userCircle = null;

const PORTUGAL_CENTER = { lat: 39.9, lng: -8.0 };
const PORTUGAL_ZOOM = 7;

const el = (id) => document.getElementById(id);
const placeholderOption = (text = "Selecione uma das opções") =>
  `<option value="" disabled selected>${text}</option>`;

const RECENTS_KEY = "recent_searches";
const MAX_RECENTS = 10;

function getRecents() {
  return JSON.parse(localStorage.getItem(RECENTS_KEY) || "[]");
}

function saveRecent(term) {
  term = (term || "").trim();
  if (!term) return;

  let recents = getRecents();
  recents = recents.filter((r) => r.toLowerCase() !== term.toLowerCase());
  recents.unshift(term);
  recents = recents.slice(0, MAX_RECENTS);

  localStorage.setItem(RECENTS_KEY, JSON.stringify(recents));
}

function renderRecents() {
  const container = el("recentList");
  const wrapper = el("recentInfo");
  const recents = getRecents();

  if (!container || !wrapper) return;

  if (!recents.length) {
    wrapper.classList.add("hidden");
    container.innerHTML = "";
    return;
  }

  container.innerHTML = "";

  recents.forEach((term) => {
    const div = document.createElement("div");
    div.className = "cursor-pointer p-1 hover:bg-gray-200 rounded text-sm";
    div.textContent = term;
    div.dataset.q = term;
    container.appendChild(div);
  });
}

function onSearch(term) {
  saveRecent(term);
  renderRecents();
}

function initRecent() {
  const btn = el("recent");
  const box = el("recentInfo");
  const list = el("recentList");

  btn?.addEventListener("click", (e) => {
    e.stopPropagation();

    if (!getRecents().length) {
      box?.classList.add("hidden");
      return;
    }

    box?.classList.toggle("hidden");
  });

  document.addEventListener("click", () => box?.classList.add("hidden"));

  list?.addEventListener("click", (e) => {
    const item = e.target.closest("[data-q]");
    if (!item) return;

    const q = item.dataset.q;

    const input = el("search");
    if (input) input.value = q;

    saveRecent(q);
    renderRecents();

    box?.classList.add("hidden");
    searchPlaces();
  });
}

function loadGoogleMaps() {
  if (window.google?.maps) return Promise.resolve();

  return new Promise((resolve, reject) => {
    const key = window.APP?.googleMapsKey;
    if (!key) {
      reject(
        new Error(
          "Google Maps API key não encontrada (window.APP.googleMapsKey)."
        )
      );
      return;
    }

    window.__initMapLoaded = () => resolve();

    const script = document.createElement("script");
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(
      key
    )}&libraries=places&callback=__initMapLoaded`;
    script.async = true;
    script.defer = true;
    script.onerror = () =>
      reject(new Error("Falha ao carregar Google Maps script."));
    document.head.appendChild(script);
  });
}

function initMap() {
  map = new google.maps.Map(el("map"), {
    center: PORTUGAL_CENTER,
    zoom: PORTUGAL_ZOOM,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
    mapTypeId: "hybrid",
  });

  service = new google.maps.places.PlacesService(map);

  bindSearch();
  bindMapButtons();
  initFiltros();
  initRecent();
  renderRecents();
}

function bindSearch() {
  const searchInput = el("search");
  if (!searchInput) return;

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      searchPlaces();
    }
  });
}

function clearMarkers() {
  markers.forEach((m) => m.setMap(null));
  markers = [];
}

function searchPlaces() {
  const input = el("search");
  if (!input || !service) return;

  const query = input.value.trim();
  if (!query) return;

  onSearch(query);

  clearMarkers();

  service.textSearch(
    {
      query,
      location: { lat: 39.3999, lng: -8.2245 },
      radius: 500000,
    },
    (results, status, pagination) => {
      if (
        status !== google.maps.places.PlacesServiceStatus.OK ||
        !results?.length
      )
        return;

      const bounds = new google.maps.LatLngBounds();

      results.forEach((place) => {
        if (!place.geometry?.location) return;

        const marker = new google.maps.Marker({
          map,
          position: place.geometry.location,
          title: place.name,
        });

        markers.push(marker);
        bounds.extend(place.geometry.location);
      });

      map.fitBounds(bounds);

      if (pagination?.hasNextPage) {
        setTimeout(() => pagination.nextPage(), 1500);
      }
    }
  );
}

function mostrarLocalizacaoNoMapa() {
  if (!("geolocation" in navigator)) {
    alert("O teu browser não suporta geolocalização.");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      const accuracy = pos.coords.accuracy;

      const userPos = { lat, lng };

      map.setCenter(userPos);
      map.setZoom(16);

      if (userCircle) userCircle.setMap(null);

      userCircle = new google.maps.Circle({
        map,
        center: userPos,
        radius: accuracy,
        strokeOpacity: 1,
        strokeWeight: 2,
        fillOpacity: 0.25,
      });
    },
    () => alert("Não foi possível obter a tua localização."),
    { enableHighAccuracy: true }
  );
}

function voltarPortugal() {
  map.setCenter(PORTUGAL_CENTER);
  map.setZoom(PORTUGAL_ZOOM);

  if (userCircle) {
    userCircle.setMap(null);
    userCircle = null;
  }
}

function bindMapButtons() {
  el("btn-localizacao")?.addEventListener("click", mostrarLocalizacaoNoMapa);
  el("btn-locPais")?.addEventListener("click", voltarPortugal);
}

async function fetchDistritos() {
  const base = window.APP?.apiBase ?? "";
  const res = await fetch(`${base}/distritos`);
  if (!res.ok) return [];
  return res.json();
}

async function fetchMunicipios(distrito) {
  const base = window.APP?.apiBase ?? "";
  const res = await fetch(
    `${base}/municipios?distrito=${encodeURIComponent(distrito)}`
  );
  if (!res.ok) return [];
  return res.json();
}

async function fetchFreguesias(municipio) {
  const base = window.APP?.apiBase ?? "";
  const res = await fetch(
    `${base}/freguesias?municipio=${encodeURIComponent(municipio)}`
  );
  if (!res.ok) return [];
  return res.json();
}

function initFiltros() {
  const btnFiltros = el("btn-filtros");
  const dropdown = el("filtros-dropdown");

  btnFiltros?.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown?.classList.toggle("hidden");
  });

  document.addEventListener("click", () => dropdown?.classList.add("hidden"));

  const cbZona = el("filtro-zona");
  const cb2 = el("filtro-2");
  const cb3 = el("filtro-3");

  const distritoDiv = el("distrito");
  const distritoSelect = el("distritos-select");

  const municipioDiv = el("municipios");
  const municipioSelect = el("municipios-select");

  const freguesiaDiv = el("freguesias");
  const freguesiaSelect = el("freguesias-select");

  const noFiltro = el("nofiltro");
  const btnLimpar = el("btn-limpar");

  const show = (node) => {
    node?.classList.remove("hidden");
    node?.classList.add("block");
  };

  const hide = (node) => {
    node?.classList.remove("block");
    node?.classList.add("hidden");
  };

  const resetMunicipios = () => {
    if (!municipioSelect) return;
    municipioSelect.disabled = true;
    municipioSelect.innerHTML = placeholderOption();
    hide(municipioDiv);
  };

  const resetFreguesias = () => {
    if (!freguesiaSelect) return;
    freguesiaSelect.disabled = true;
    freguesiaSelect.innerHTML = placeholderOption();
    hide(freguesiaDiv);
  };

  const toggleNoFiltro = () => {
    const anyChecked = !!(cbZona?.checked || cb2?.checked || cb3?.checked);
    if (!noFiltro) return;

    noFiltro.classList.toggle("hidden", !anyChecked);
    noFiltro.classList.toggle("block", anyChecked);
  };

  (async () => {
    if (!distritoSelect) return;

    distritoSelect.disabled = true;
    distritoSelect.innerHTML = placeholderOption("A carregar distritos...");

    const distritos = await fetchDistritos();

    distritoSelect.innerHTML = placeholderOption();
    distritos.forEach((d) => {
      const opt = document.createElement("option");
      opt.value = d;
      opt.textContent = d;
      distritoSelect.appendChild(opt);
    });

    distritoSelect.disabled = false;
  })();

  cbZona?.addEventListener("change", () => {
    if (cbZona.checked) {
      show(distritoDiv);
    } else {
      hide(distritoDiv);
      if (distritoSelect) distritoSelect.value = "";
      resetMunicipios();
      resetFreguesias();
    }
    toggleNoFiltro();
  });

  cb2?.addEventListener("change", toggleNoFiltro);
  cb3?.addEventListener("change", toggleNoFiltro);

  btnLimpar?.addEventListener("click", (e) => {
    e.preventDefault();

    if (cbZona) cbZona.checked = false;
    if (cb2) cb2.checked = false;
    if (cb3) cb3.checked = false;

    hide(distritoDiv);
    if (distritoSelect) distritoSelect.value = "";

    resetMunicipios();
    resetFreguesias();
    toggleNoFiltro();
  });

  distritoSelect?.addEventListener("change", async (e) => {
    const distrito = e.target.value;

    resetMunicipios();
    resetFreguesias();
    if (!distrito) return;

    const municipios = await fetchMunicipios(distrito);

    municipioSelect.disabled = false;
    municipioSelect.innerHTML = placeholderOption();

    municipios.forEach((m) => {
      const opt = document.createElement("option");
      opt.value = m;
      opt.textContent = m;
      municipioSelect.appendChild(opt);
    });

    show(municipioDiv);
  });

  municipioSelect?.addEventListener("change", async (e) => {
    const municipio = e.target.value;

    resetFreguesias();
    if (!municipio) return;

    const freguesias = await fetchFreguesias(municipio);

    freguesiaSelect.disabled = false;
    freguesiaSelect.innerHTML = placeholderOption();

    freguesias.forEach((f) => {
      const opt = document.createElement("option");
      opt.value = f;
      opt.textContent = f;
      freguesiaSelect.appendChild(opt);
    });

    show(freguesiaDiv);
  });

  /*freguesiaSelect?.addEventListener("change", (e) => {
    const freguesia = e.target.value;
    if (!freguesia) return;

    const input = el("search");
    if (input) input.value = freguesia;

    searchPlaces();
  });*/
}

document.addEventListener("DOMContentLoaded", async () => {
  await loadGoogleMaps();
  initMap();
});
