/**
 * IndexedDB Database Manager for Catatan Hutang (Offline Support)
 * Manages stores: 'pelanggan', 'utang', 'antrean_utang', 'metadata'
 */
const AppDB = (function () {
    const DB_NAME = 'CatatanHutangDB';
    const DB_VERSION = 1;
    let dbInstance = null;

    function openDB() {
        if (dbInstance) {
            return Promise.resolve(dbInstance);
        }

        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function (event) {
                const db = event.target.result;

                // Object store for Pelanggan
                if (!db.objectStoreNames.contains('pelanggan')) {
                    const pelangganStore = db.createObjectStore('pelanggan', { keyPath: 'id' });
                    pelangganStore.createIndex('nama', 'nama', { unique: false });
                    pelangganStore.createIndex('aktif', 'aktif', { unique: false });
                }

                // Object store for Utang transactions
                if (!db.objectStoreNames.contains('utang')) {
                    const utangStore = db.createObjectStore('utang', { keyPath: 'id' });
                    utangStore.createIndex('nomor_transaksi', 'nomor_transaksi', { unique: false });
                    utangStore.createIndex('tanggal_utang', 'tanggal_utang', { unique: false });
                    utangStore.createIndex('pelanggan_id', 'pelanggan_id', { unique: false });
                    utangStore.createIndex('status', 'status', { unique: false });
                }

                // Object store for Offline Transaction Queue (Antrean)
                if (!db.objectStoreNames.contains('antrean_utang')) {
                    const antreanStore = db.createObjectStore('antrean_utang', { keyPath: 'local_id' });
                    antreanStore.createIndex('created_at', 'created_at', { unique: false });
                    antreanStore.createIndex('status', 'status', { unique: false });
                }

                // Object store for Metadata (e.g. last_sync)
                if (!db.objectStoreNames.contains('metadata')) {
                    db.createObjectStore('metadata', { keyPath: 'key' });
                }
            };

            request.onsuccess = function (event) {
                dbInstance = event.target.result;
                resolve(dbInstance);
            };

            request.onerror = function (event) {
                console.error('IndexedDB open error:', event.target.error);
                reject(event.target.error);
            };
        });
    }

    // Generic helper for transaction execution
    function executeTx(storeName, mode, callback) {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction(storeName, mode);
                const store = tx.objectStore(storeName);

                let result;
                try {
                    result = callback(store);
                } catch (err) {
                    reject(err);
                    return;
                }

                tx.oncomplete = () => resolve(result);
                tx.onerror = (e) => reject(e.target.error);
            });
        });
    }

    /* ----------------------------------------------------------------------
       Pelanggan Operations
       ---------------------------------------------------------------------- */
    function savePelangganList(list) {
        if (!Array.isArray(list) || list.length === 0) return Promise.resolve([]);
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('pelanggan', 'readwrite');
                const store = tx.objectStore('pelanggan');

                list.forEach(item => {
                    // Normalize id to integer or string
                    item.id = Number(item.id) || item.id;
                    item.aktif = Number(item.aktif ?? 1);
                    item.total_sisa_hutang = Number(item.total_sisa_hutang || 0);
                    store.put(item);
                });

                tx.oncomplete = () => resolve(list);
                tx.onerror = e => reject(e.target.error);
            });
        });
    }

    function getPelangganList(statusFilter = 'aktif', searchQuery = '') {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('pelanggan', 'readonly');
                const store = tx.objectStore('pelanggan');
                const request = store.getAll();

                request.onsuccess = () => {
                    let results = request.result || [];
                    const q = searchQuery.toLowerCase().trim();

                    // Filter by active status if specified
                    if (statusFilter === 'aktif') {
                        results = results.filter(p => Number(p.aktif) === 1);
                    } else if (statusFilter === 'tidak_aktif') {
                        results = results.filter(p => Number(p.aktif) === 0);
                    }

                    // Search filter
                    if (q !== '') {
                        results = results.filter(p => {
                            const nama = (p.nama || '').toLowerCase();
                            const noHp = (p.no_hp || '').toLowerCase();
                            const alamat = (p.alamat || '').toLowerCase();
                            const catatan = (p.catatan || '').toLowerCase();
                            return nama.includes(q) || noHp.includes(q) || alamat.includes(q) || catatan.includes(q);
                        });
                    }

                    // Sort by name
                    results.sort((a, b) => (a.nama || '').localeCompare(b.nama || ''));
                    resolve(results);
                };

                request.onerror = e => reject(e.target.error);
            });
        });
    }

    function getPelangganById(id) {
        const numId = Number(id) || id;
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('pelanggan', 'readonly');
                const store = tx.objectStore('pelanggan');
                const req = store.get(numId);
                req.onsuccess = () => resolve(req.result || null);
                req.onerror = e => reject(e.target.error);
            });
        });
    }

    /* ----------------------------------------------------------------------
       Utang Operations
       ---------------------------------------------------------------------- */
    function saveUtangList(list) {
        if (!Array.isArray(list) || list.length === 0) return Promise.resolve([]);
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('utang', 'readwrite');
                const store = tx.objectStore('utang');

                list.forEach(item => {
                    item.id = Number(item.id) || item.id;
                    item.total_hutang = Number(item.total_hutang || 0);
                    item.total_bayar = Number(item.total_bayar || 0);
                    item.sisa_hutang = Math.max(0, item.total_hutang - item.total_bayar);
                    item.dibatalkan = Number(item.dibatalkan || 0);
                    store.put(item);
                });

                tx.oncomplete = () => resolve(list);
                tx.onerror = e => reject(e.target.error);
            });
        });
    }

    function getUtangList(searchQuery = '') {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('utang', 'readonly');
                const store = tx.objectStore('utang');
                const req = store.getAll();

                req.onsuccess = () => {
                    let results = req.result || [];
                    const q = searchQuery.toLowerCase().trim();

                    if (q !== '') {
                        results = results.filter(u => {
                            const nama = (u.nama_pelanggan || '').toLowerCase();
                            const noTrx = (u.nomor_transaksi || '').toLowerCase();
                            return nama.includes(q) || noTrx.includes(q);
                        });
                    }

                    // Sort newest first
                    results.sort((a, b) => new Date(b.tanggal_utang || 0) - new Date(a.tanggal_utang || 0));
                    resolve(results);
                };

                req.onerror = e => reject(e.target.error);
            });
        });
    }

    function getUtangById(id) {
        const numId = Number(id) || id;
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('utang', 'readonly');
                const store = tx.objectStore('utang');
                const req = store.get(numId);
                req.onsuccess = () => resolve(req.result || null);
                req.onerror = e => reject(e.target.error);
            });
        });
    }

    /* ----------------------------------------------------------------------
       Antrean Utang Offline (Queue Operations)
       ---------------------------------------------------------------------- */
    function addToAntreanUtang(data) {
        // Ensure unique local_id
        if (!data.local_id) {
            data.local_id = 'OFFLINE-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
        }
        data.status = 'menunggu_sinkronisasi';
        data.created_at = data.created_at || new Date().toISOString();

        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction(['antrean_utang', 'utang'], 'readwrite');
                const antreanStore = tx.objectStore('antrean_utang');
                const utangStore = tx.objectStore('utang');

                // Put in queue
                antreanStore.put(data);

                // Also put in utang store with local_id so it displays immediately in the list
                const localUtangItem = {
                    id: data.local_id,
                    pelanggan_id: data.pelanggan_id,
                    nama_pelanggan: data.nama_pelanggan,
                    nomor_transaksi: data.local_id,
                    tanggal_utang: data.tanggal_utang,
                    tanggal_jatuh_tempo: data.tanggal_jatuh_tempo || null,
                    total_hutang: Number(data.total_hutang || 0),
                    total_bayar: 0,
                    sisa_hutang: Number(data.total_hutang || 0),
                    dibatalkan: 0,
                    catatan: data.catatan || null,
                    rincian: data.rincian || [],
                    is_offline: true,
                    status: 'menunggu_sinkronisasi'
                };
                utangStore.put(localUtangItem);

                tx.oncomplete = () => resolve(data);
                tx.onerror = e => reject(e.target.error);
            });
        });
    }

    function getAntreanUtang() {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('antrean_utang', 'readonly');
                const store = tx.objectStore('antrean_utang');
                const req = store.getAll();
                req.onsuccess = () => resolve(req.result || []);
                req.onerror = e => reject(e.target.error);
            });
        });
    }

    function removeAntreanUtang(localId) {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction(['antrean_utang', 'utang'], 'readwrite');
                tx.objectStore('antrean_utang').delete(localId);
                // Also remove temporary local utang item once replaced by real server record
                tx.objectStore('utang').delete(localId);

                tx.oncomplete = () => resolve(true);
                tx.onerror = e => reject(e.target.error);
            });
        });
    }

    function countAntreanUtang() {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('antrean_utang', 'readonly');
                const store = tx.objectStore('antrean_utang');
                const req = store.count();
                req.onsuccess = () => resolve(req.result || 0);
                req.onerror = () => resolve(0);
            });
        });
    }

    /* ----------------------------------------------------------------------
       Metadata
       ---------------------------------------------------------------------- */
    function setMeta(key, value) {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('metadata', 'readwrite');
                tx.objectStore('metadata').put({ key, value, updated_at: new Date().toISOString() });
                tx.oncomplete = () => resolve(value);
                tx.onerror = e => reject(e.target.error);
            });
        });
    }

    function getMeta(key) {
        return openDB().then(db => {
            return new Promise((resolve, reject) => {
                const tx = db.transaction('metadata', 'readonly');
                const req = tx.objectStore('metadata').get(key);
                req.onsuccess = () => resolve(req.result ? req.result.value : null);
                req.onerror = () => resolve(null);
            });
        });
    }

    return {
        open: openDB,
        savePelangganList,
        getPelangganList,
        getPelangganById,
        saveUtangList,
        getUtangList,
        getUtangById,
        addToAntreanUtang,
        getAntreanUtang,
        removeAntreanUtang,
        countAntreanUtang,
        setMeta,
        getMeta
    };
})();

// Attach to window
window.AppDB = AppDB;
