(function () {
    const searchInput = document.getElementById('pelanggan_search');
    const selectedInput = document.getElementById('pelanggan_id');
    const list = document.getElementById('pelanggan_suggestions');
    const status = document.getElementById('pelanggan_search_status');
    if (!searchInput || !selectedInput || !list || !status) return;

    const endpoint = searchInput.dataset.searchUrl;
    let debounceTimer = null;
    let requestController = null;
    let activeIndex = -1;
    let currentResults = [];

    function closeList() {
        list.hidden = true;
        list.replaceChildren();
        searchInput.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    }

    function showStatus(message) {
        status.textContent = message;
    }

    function formatCustomer(customer) {
        const name = String(customer.nama || '');
        const phone = String(customer.no_hp || '').trim();
        return phone ? name + ' · ' + phone : name;
    }

    function selectCustomer(customer) {
        selectedInput.value = String(customer.id);
        searchInput.value = formatCustomer(customer);
        showStatus('Pelanggan dipilih: ' + String(customer.nama || ''));
        closeList();
    }

    function renderResults(results) {
        currentResults = Array.isArray(results) ? results.slice(0, 10) : [];
        list.replaceChildren();

        if (!currentResults.length) {
            closeList();
            showStatus('Pelanggan tidak ditemukan. Periksa ejaan atau coba nomor HP.');
            return;
        }

        currentResults.forEach((customer, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'customer-suggestion';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');

            const name = document.createElement('span');
            name.textContent = String(customer.nama || '');
            button.appendChild(name);

            if (customer.no_hp) {
                const phone = document.createElement('small');
                phone.textContent = String(customer.no_hp);
                button.appendChild(phone);
            }

            button.addEventListener('mousedown', event => event.preventDefault());
            button.addEventListener('click', () => selectCustomer(customer));
            list.appendChild(button);
        });

        list.hidden = false;
        searchInput.setAttribute('aria-expanded', 'true');
        showStatus(currentResults.length === 10
            ? 'Menampilkan 10 hasil teratas. Ketik lebih spesifik jika belum menemukan pelanggan.'
            : currentResults.length + ' pelanggan ditemukan.');
    }

    async function searchCustomers(query) {
        if (requestController) requestController.abort();

        if (query.length < 2) {
            closeList();
            showStatus('Ketik minimal 2 huruf untuk mencari pelanggan.');
            return;
        }

        showStatus('Mencari pelanggan…');

        if (!navigator.onLine && window.AppDB && typeof AppDB.getPelangganList === 'function') {
            try {
                const cached = await AppDB.getPelangganList('aktif', query);
                renderResults(cached.filter(p => {
                    const name = String(p.nama || '').toLowerCase();
                    const phone = String(p.no_hp || '').toLowerCase();
                    return name.includes(query.toLowerCase()) || phone.includes(query.toLowerCase());
                }));
            } catch (error) {
                closeList();
                showStatus('Data pelanggan offline belum tersedia. Sambungkan internet untuk mencari.');
            }
            return;
        }

        requestController = new AbortController();
        try {
            const url = new URL(endpoint, window.location.href);
            url.searchParams.set('q', query);
            const response = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                signal: requestController.signal,
                cache: 'no-store'
            });

            if (!response.ok) throw new Error('Search request failed');
            const data = await response.json();
            renderResults(data.results || []);
        } catch (error) {
            if (error.name === 'AbortError') return;

            // If connection drops during typing, try the previously synced local list.
            if (window.AppDB && typeof AppDB.getPelangganList === 'function') {
                try {
                    const cached = await AppDB.getPelangganList('aktif', query);
                    renderResults(cached.filter(p => {
                        const name = String(p.nama || '').toLowerCase();
                        const phone = String(p.no_hp || '').toLowerCase();
                        return name.includes(query.toLowerCase()) || phone.includes(query.toLowerCase());
                    }));
                    showStatus('Hasil dari data offline tersimpan.');
                    return;
                } catch (offlineError) {
                    // Show the regular connection message below.
                }
            }

            closeList();
            showStatus('Tidak bisa mencari pelanggan. Periksa koneksi internet.');
        }
    }

    searchInput.addEventListener('input', function () {
        selectedInput.value = '';
        const query = searchInput.value.trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => searchCustomers(query), 250);
    });

    searchInput.addEventListener('keydown', function (event) {
        if (list.hidden || !currentResults.length) return;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = Math.min(activeIndex + 1, currentResults.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            selectCustomer(currentResults[activeIndex]);
            return;
        } else if (event.key === 'Escape') {
            closeList();
            return;
        } else {
            return;
        }

        const buttons = list.querySelectorAll('.customer-suggestion');
        buttons.forEach((button, index) => {
            button.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');
            if (index === activeIndex) button.focus();
        });
    });

    list.addEventListener('keydown', function (event) {
        const buttons = Array.from(list.querySelectorAll('.customer-suggestion'));
        const current = buttons.indexOf(document.activeElement);

        if (event.key === 'Escape') {
            event.preventDefault();
            closeList();
            searchInput.focus();
        } else if (event.key === 'ArrowDown' && buttons.length) {
            event.preventDefault();
            buttons[Math.min(current + 1, buttons.length - 1)].focus();
        } else if (event.key === 'ArrowUp' && buttons.length) {
            event.preventDefault();
            if (current <= 0) searchInput.focus();
            else buttons[current - 1].focus();
        }
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.customer-search-wrap')) closeList();
    });

    const form = searchInput.closest('form');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!selectedInput.value) {
                event.preventDefault();
                showStatus('Pilih pelanggan dari hasil pencarian terlebih dahulu.');
                searchInput.focus();
            }
        });
    }
})();
