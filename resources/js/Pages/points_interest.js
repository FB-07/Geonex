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
        setDisabled(municipioEl, true);
        const municipios = await fetch(`/api/municipios?distrito=${encodeURIComponent(distrito)}`).then(r => r.json());
        setOptions(municipioEl, municipios, 'Selecione um município');
        setDisabled(municipioEl, false);
    }

    async function loadFreguesias(municipio) {
        setDisabled(freguesiaEl, true);
        const freguesias = await fetch(`/api/freguesias?municipio=${encodeURIComponent(municipio)}`).then(r => r.json());
        setOptions(freguesiaEl, freguesias, 'Selecione uma freguesia');
        setDisabled(freguesiaEl, false);
    }
});