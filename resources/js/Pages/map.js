let map;
let userCircle = null;
let municipiosReqId = 0;
let freguesiasReqId = 0;

const PORTUGAL_CENTER = { lat: 39.9, lng: -8.0 };
const PORTUGAL_ZOOM = 7;

const el = (id) => document.getElementById(id);
const placeholderOption = (text = "Selecione uma das opções") =>
  `<option value="" disabled selected>${text}</option>`;

const RECENTS_KEY = "recent_searches";
const MAX_RECENTS = 10;

const recentMarkers = new Map();

function getActiveFilters() {
  const cbTipo = el("filtro-tipo");
  const category = el("category")?.value || "";

  return {
    category: cbTipo?.checked ? category : "",
  };
}

function rerunSearchIfAny({ fitBounds = true } = {}) {
  const input = el("search");
  const query = input?.value?.trim();
  if (!query) return;
  searchPlacesByTerm(query, { fitBounds, save: true });
}

function getRecents() {
  try {
    return JSON.parse(localStorage.getItem(RECENTS_KEY) || "[]");
  } catch {
    return [];
  }
}

function saveRecent(term) {
  term = (term || "").trim();
  if (!term) return;

  let recents = getRecents();
  recents = recents.filter((r) => (r || "").toLowerCase() !== term.toLowerCase());
  recents.unshift(term);
  recents = recents.slice(0, MAX_RECENTS);

  localStorage.setItem(RECENTS_KEY, JSON.stringify(recents));
}

function removeRecent(term) {
  term = (term || "").trim();
  if (!term) return;

  let recents = getRecents();
  recents = recents.filter((r) => (r || "").toLowerCase() !== term.toLowerCase());

  if (recents.length) localStorage.setItem(RECENTS_KEY, JSON.stringify(recents));
  else localStorage.removeItem(RECENTS_KEY);

  const entry = recentMarkers.get(term);
  if (entry?.markers?.length) entry.markers.forEach((m) => m.setMap(null));
  recentMarkers.delete(term);

  renderRecents();
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

  const iconTemplate = el("noRecent");

  recents.forEach((term) => {
    const row = document.createElement("div");
    row.className =
      "flex items-center justify-between gap-2 p-1 hover:bg-gray-100 rounded";

    const left = document.createElement("label");
    left.className = "flex items-center gap-2 cursor-pointer min-w-0";

    const cb = document.createElement("input");
    cb.type = "checkbox";
    cb.className = "cursor-pointer";
    cb.dataset.term = term;

    const entry = recentMarkers.get(term);
    cb.checked = !!entry?.visible;

    const text = document.createElement("span");
    text.className = "text-sm truncate";
    text.textContent = term;

    left.appendChild(cb);
    left.appendChild(text);

    let iconWrap;
    if (iconTemplate) {
      iconWrap = iconTemplate.cloneNode(true);
      iconWrap.classList.remove("hidden");
      iconWrap.removeAttribute("id");
      iconWrap.dataset.remove = "1";
      iconWrap.dataset.term = term;

      const innerSvg = iconWrap.querySelector("svg");
      if (innerSvg) {
        innerSvg.dataset.remove = "1";
        innerSvg.dataset.term = term;
      }
    } else {
      iconWrap = document.createElement("span");
      iconWrap.textContent = "X";
      iconWrap.className = "text-red-500 hover:text-red-700 cursor-pointer";
      iconWrap.dataset.remove = "1";
      iconWrap.dataset.term = term;
    }

    row.appendChild(left);
    row.appendChild(iconWrap);
    container.appendChild(row);
  });
}

function onSearch(term) {
  saveRecent(term);
  renderRecents();
}

async function toggleRecentTerm(term, checked) {
  if (!map) return;

  term = (term || "").trim();
  if (!term) return;

  const entry = recentMarkers.get(term);

  if (checked && !entry) {
    const { places } = await searchPlacesByTerm(term, { fitBounds: false, save: false });
    if (!places.length) {
      const cb = document.querySelector(
        `input[type="checkbox"][data-term="${CSS.escape(term)}"]`
      );
      if (cb) cb.checked = false;
    }
    return;
  }

  if (!entry) return;

  entry.visible = !!checked;
  entry.markers.forEach((m) => m.setMap(entry.visible ? map : null));
  recentMarkers.set(term, entry);
}

function initRecent() {
  const btn = el("recent");
  const box = el("recentInfo");
  const list = el("recentList");

  btn?.addEventListener("click", (e) => {
    e.stopPropagation();
    if (!getRecents().length) return box?.classList.add("hidden");
    box?.classList.toggle("hidden");
  });

  document.addEventListener("click", () => box?.classList.add("hidden"));

  list?.addEventListener("click", (e) => {
    e.stopPropagation();
    const removeBtn = e.target.closest('[data-remove="1"]');
    if (removeBtn) removeRecent(removeBtn.dataset.term);
  });

  list?.addEventListener("change", async (e) => {
    e.stopPropagation();
    const cb = e.target.closest('input[type="checkbox"][data-term]');
    if (!cb) return;
    await toggleRecentTerm(cb.dataset.term, cb.checked);
  });
}

function loadGoogleMaps() {
  if (window.google?.maps) return Promise.resolve();

  return new Promise((resolve, reject) => {
    const key = window.APP?.googleMapsKey;
    if (!key) return reject(new Error("Google Maps API key não encontrada."));

    window.__initMapLoaded = () => resolve();

    const script = document.createElement("script");
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(
      key
    )}&libraries=places&callback=__initMapLoaded`;
    script.async = true;
    script.defer = true;
    script.onerror = () => reject(new Error("Falha ao carregar Google Maps script."));
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

function colorFromString(str) {
  str = (str || "").trim().toLowerCase();
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  const hue = Math.abs(hash) % 360;
  return `hsl(${hue}, 80%, 45%)`;
}

function makeMarkerIcon(term) {
  return {
    path: google.maps.SymbolPath.CIRCLE,
    scale: 8,
    fillColor: colorFromString(term),
    fillOpacity: 1,
    strokeColor: "#ffffff",
    strokeWeight: 2,
  };
}

async function searchPlacesByTerm(query, { fitBounds = true, save = true } = {}) {
  if (!map) return { places: [] };

  query = (query || "").trim();
  if (!query) return { places: [] };

  if (save) onSearch(query);

  const filters = getActiveFilters();

  let res;
  try {
    res = await fetch("/api/places/search", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        query,
        lat: 39.3999,
        lng: -8.2245,
        radius: 50000,
        ...filters,
      }),
    });
  } catch (e) {
    console.error("Network error:", e);
    return { places: [] };
  }

  if (!res.ok) {
    console.error("API error:", res.status, await res.text());
    return { places: [] };
  }

  const data = await res.json();
  const places = data.places || [];
  if (!places.length) return { places: [] };

  const prev = recentMarkers.get(query);
  if (prev?.markers?.length) prev.markers.forEach((m) => m.setMap(null));

  const created = [];
  const bounds = new google.maps.LatLngBounds();

  places.forEach((p) => {
    const lat = Number(p.lat);
    const lng = Number(p.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    const pos = { lat, lng };

    const marker = new google.maps.Marker({
      map,
      position: pos,
      title: p.name,
      icon: makeMarkerIcon(query),
    });

    created.push(marker);
    bounds.extend(pos);
  });

  recentMarkers.set(query, { markers: created, visible: true });
  renderRecents();

  if (fitBounds) map.fitBounds(bounds);

  return { places };
}

async function searchPlaces() {
  const input = el("search");
  const query = input?.value?.trim();
  if (!query) return;

  await searchPlacesByTerm(query, { fitBounds: true, save: true });
}

function mostrarLocalizacaoNoMapa() {
  if (!("geolocation" in navigator)) {
    alert("O teu browser não suporta geolocalização.");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const userPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      const accuracy = pos.coords.accuracy;

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
  const res = await fetch(`${base}/municipios?distrito=${encodeURIComponent(distrito)}`);
  if (!res.ok) return [];
  return res.json();
}

async function fetchFreguesias(municipio) {
  const base = window.APP?.apiBase ?? "";
  const res = await fetch(`${base}/freguesias?municipio=${encodeURIComponent(municipio)}`);
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
  const cbTipo = el("filtro-tipo");
  //const cb3 = el("filtro-3");

  const distritoDiv = el("distrito");
  const distritoSelect = el("distritos-select");

  const municipioDiv = el("municipios");
  const municipioSelect = el("municipios-select");

  const freguesiaDiv = el("freguesias");
  const freguesiaSelect = el("freguesias-select");

  const categoriaDiv = el("categoria");
  const categoriaSelect = el("category");

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

  const resetCategoria = () => {
    if (!categoriaSelect) return;
    categoriaSelect.value = "";
    hide(categoriaDiv);
  };

  const toggleNoFiltro = () => {
    const anyChecked = !!(cbZona?.checked || cbTipo?.checked /*cb3?.checked*/);
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

  cbTipo?.addEventListener("change", () => {
    if (cbTipo.checked) {
      show(categoriaDiv);
      rerunSearchIfAny({ fitBounds: true });
    } else {
      resetCategoria();
      rerunSearchIfAny({ fitBounds: true });
    }
    toggleNoFiltro();
  });

  //cb3?.addEventListener("change", toggleNoFiltro);

  btnLimpar?.addEventListener("click", (e) => {
    e.preventDefault();

    if (cbZona) cbZona.checked = false;
    if (cbTipo) cbTipo.checked = false;
    //if (cb3) cb3.checked = false;

    hide(distritoDiv);
    if (distritoSelect) distritoSelect.value = "";
    resetMunicipios();
    resetFreguesias();

    if (categoriaSelect) categoriaSelect.value = "";
    hide(categoriaDiv);

    toggleNoFiltro();
    rerunSearchIfAny({ fitBounds: true });
  });

  distritoSelect?.addEventListener("change", async (e) => {
    const distrito = e.target.value;

    resetMunicipios();
    resetFreguesias();
    if (!distrito) return;

    show(municipioDiv);
    municipioSelect.disabled = true;
    municipioSelect.innerHTML = placeholderOption("A carregar municípios...");

    const myId = ++municipiosReqId;

    try {
      const municipios = await fetchMunicipios(distrito);
      if (myId !== municipiosReqId) return;

      municipioSelect.innerHTML = placeholderOption();
      municipios.forEach((m) => {
        const opt = document.createElement("option");
        opt.value = m;
        opt.textContent = m;
        municipioSelect.appendChild(opt);
      });

      municipioSelect.disabled = false;
    } catch {
      if (myId !== municipiosReqId) return;
      municipioSelect.innerHTML = placeholderOption("Erro a carregar municípios");
    }
  });

  municipioSelect?.addEventListener("change", async (e) => {
    const municipio = e.target.value;

    resetFreguesias();
    if (!municipio) return;

    show(freguesiaDiv);
    freguesiaSelect.disabled = true;
    freguesiaSelect.innerHTML = placeholderOption("A carregar freguesias...");

    const myId = ++freguesiasReqId;

    try {
      const freguesias = await fetchFreguesias(municipio);
      if (myId !== freguesiasReqId) return;

      freguesiaSelect.innerHTML = placeholderOption();
      freguesias.forEach((f) => {
        const opt = document.createElement("option");
        opt.value = f;
        opt.textContent = f;
        freguesiaSelect.appendChild(opt);
      });

      freguesiaSelect.disabled = false;
    } catch {
      if (myId !== freguesiasReqId) return;
      freguesiaSelect.innerHTML = placeholderOption("Erro a carregar freguesias");
    }
  });

  categoriaSelect?.addEventListener("change", () => {
    rerunSearchIfAny({ fitBounds: true });
  });
}

document.addEventListener("DOMContentLoaded", async () => {
  await loadGoogleMaps();
  initMap();
});
