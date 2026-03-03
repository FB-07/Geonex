document.addEventListener('DOMContentLoaded', async () => {

    const distritoEl = document.querySelector('select[name="distrito"]');
    const municipioEl = document.querySelector('select[name="municipio"]');
    const freguesiaEl = document.querySelector('select[name="freguesia"]');

    function setOptions(selectEl, items, placeholder) {
        selectEl.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        selectEl.appendChild(opt0);

        items.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            selectEl.appendChild(opt);
        });
    }

    function setDisabled(selectEl, disabled) {
        selectEl.disabled = disabled;
        selectEl.classList.toggle('opacity-60', disabled);
    }

    setOptions(distritoEl, [], 'Selecione um distrito');
    setOptions(municipioEl, [], 'Selecione um município');
    setOptions(freguesiaEl, [], 'Selecione uma freguesia');
    setDisabled(municipioEl, true);
    setDisabled(freguesiaEl, true);

    const distritos = await fetch('/api/distritos').then(r => r.json());
    setOptions(distritoEl, distritos, 'Selecione um distrito');

    distritoEl.addEventListener('change', async (e) => {
        const distrito = e.target.value;

        setOptions(municipioEl, [], 'Selecione um município');
        setOptions(freguesiaEl, [], 'Selecione uma freguesia');
        setDisabled(freguesiaEl, true);

        if (!distrito) {
            setDisabled(municipioEl, true);
            return;
        }

        await loadMunicipios(distrito);
    });

    municipioEl.addEventListener('change', async (e) => {
        const municipio = e.target.value;

        setOptions(freguesiaEl, [], 'Selecione uma freguesia');

        if (!municipio) {
            setDisabled(freguesiaEl, true);
            return;
        }

        await loadFreguesias(municipio);
    });

    async function loadMunicipios(distrito) {
        setOptions(municipioEl, [], 'A carregar municípios...');
        setDisabled(municipioEl, true);

        try {
            const municipios = await fetch(`/api/municipios?distrito=${encodeURIComponent(distrito)}`).then(r => r.json());
            setOptions(municipioEl, municipios, 'Selecione um município');
            setDisabled(municipioEl, false);
        } catch (err) {
            setOptions(municipioEl, [], 'Erro ao carregar municípios');
            setDisabled(municipioEl, true);
            console.error(err);
        }
    }

    async function loadFreguesias(municipio) {
        setOptions(freguesiaEl, [], 'A carregar freguesias...');
        setDisabled(freguesiaEl, true);

        try {
            const freguesias = await fetch(`/api/freguesias?municipio=${encodeURIComponent(municipio)}`).then(r => r.json());
            setOptions(freguesiaEl, freguesias, 'Selecione uma freguesia');
            setDisabled(freguesiaEl, false);
        } catch (err) {
            setOptions(freguesiaEl, [], 'Erro ao carregar freguesias');
            setDisabled(freguesiaEl, true);
            console.error(err);
        }
    }
    
    const nomeInput = document.querySelector('input[name="nome"]');
    const form = document.querySelector('#zone-form');
    const button = form?.querySelector('button[type="submit"]');

    if (nomeInput && form && button) {
        const message = document.createElement("div");
        message.className = "text-red-600 text-sm mt-1";
        nomeInput.parentElement.appendChild(message);

        let timeout = null;

        const checkDuplicate = async () => {
            const value = nomeInput.value.trim();
            if (!value) {
                message.textContent = "";
                button.disabled = false;
                button.classList.remove("opacity-50", "cursor-not-allowed");
                return;
            }

            const response = await fetch(window.checkInterestUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                },
                body: JSON.stringify({ nome: value })
            });

            const data = await response.json();

            if (data.exists) {
                message.textContent = "Já existe um ponto de interesse com esse nome.";
                button.disabled = true;
                button.classList.add("opacity-50", "cursor-not-allowed");
            } else {
                message.textContent = "";
                button.disabled = false;
                button.classList.remove("opacity-50", "cursor-not-allowed");
            }
        };

        nomeInput.addEventListener("input", () => {
            clearTimeout(timeout);
            timeout = setTimeout(checkDuplicate, 400);
        });

        form.addEventListener("submit", async (e) => {
            await checkDuplicate();
            if (button.disabled) e.preventDefault();
        });
    }
});