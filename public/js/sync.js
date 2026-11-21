/**
 * PWA & Offline Synchronization Manager - Catatan Hutang
 * Handles Service Worker registration, IndexedDB caching, offline form queueing, and background sync.
 */
const SyncManager = (function () {
    let deferredInstallPrompt = null;
    let isSyncing = false;

    // Toast Notification System
    function showToast(message, type = 'info', duration = 4000) {
        let container = document.getElementById('appToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'appToastContainer';
            container.className = 'app-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `app-toast toast-${type}`;
        
        let icon = 'ℹ️';
        if (type === 'success') icon = '✅';
        if (type === 'warning') icon = '⚠️';
        if (type === 'error') icon = '❌';

        toast.innerHTML = `<span class="toast-icon">${icon}</span><span class="toast-msg">${message}</span>`;
        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Register Service Worker
    function registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => {
                        // Check for background sync
                        if ('sync' in reg) {
                            window.swRegistration = reg;
                        }
                    })
                    .catch(err => {
                        console.warn('[PWA] Service Worker registration failed:', err);
                    });
            });

            // Listen for messages from SW
            navigator.serviceWorker.addEventListener('message', event => {
                if (event.data && event.data.type === 'TRIGGER_OFFLINE_SYNC') {
                    syncPendingTransactions();
                }
            });
        }
    }

    // Handle Add to Home Screen (A2HS) Prompt
    function initInstallPrompt() {
        window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            deferredInstallPrompt = e;

            // Show install button in topbar or sidebar if available
            const installBtns = document.querySelectorAll('.btn-install-pwa');
            installBtns.forEach(btn => {
                btn.style.display = 'inline-flex';
                btn.onclick = () => {
                    if (deferredInstallPrompt) {
                        deferredInstallPrompt.prompt();
                        deferredInstallPrompt.userChoice.then(choice => {
                            if (choice.outcome === 'accepted') {
                                showToast('Aplikasi berhasil dipasang di layar utama!', 'success');
                            }
                            deferredInstallPrompt = null;
                            btn.style.display = 'none';
                        });
                    }
                };
            });
        });

        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            document.querySelectorAll('.btn-install-pwa').forEach(btn => btn.style.display = 'none');
            showToast('Aplikasi Catatan Hutang siap digunakan dari layar utama.', 'success');
        });
    }

    // Update Connection & Queue Status Badge in Topbar
    async function updateStatusUI() {
        const isOnline = navigator.onLine;
        const statusBadge = document.getElementById('connectionStatusBadge');
        const queueBadge = document.getElementById('syncQueueBadge');
        const syncBtn = document.getElementById('manualSyncBtn');

        if (statusBadge) {
            if (isOnline) {
                statusBadge.className = 'status-pill-online';
                statusBadge.innerHTML = '<span class="status-indicator-dot online"></span> Online';
                statusBadge.title = 'Terhubung ke internet';
            } else {
                statusBadge.className = 'status-pill-offline';
                statusBadge.innerHTML = '<span class="status-indicator-dot offline"></span> Offline';
                statusBadge.title = 'Tidak ada koneksi internet (Data disimpan lokal)';
            }
        }

        // Count pending queue
        if (window.AppDB) {
            try {
                const count = await window.AppDB.countAntreanUtang();
                if (queueBadge) {
                    if (count > 0) {
                        queueBadge.style.display = 'inline-flex';
                        queueBadge.innerHTML = `⏳ ${count} Antrean`;
                        queueBadge.title = `${count} transaksi menunggu sinkronisasi`;
                    } else {
                        queueBadge.style.display = 'none';
                    }
                }
                if (syncBtn) {
                    syncBtn.style.display = (count > 0 && isOnline) ? 'inline-flex' : 'none';
                }
            } catch (e) {
                console.warn('Error counting queue:', e);
            }
        }
    }

    // Cache pre-warming: fetch latest server data and store into IndexedDB
    async function refreshOfflineCache() {
        if (!navigator.onLine || !window.AppDB) return;

        try {
            const res = await fetch('/utang/data-offline');
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    if (Array.isArray(data.pelanggan)) {
                        await window.AppDB.savePelangganList(data.pelanggan);
                    }
                    if (Array.isArray(data.utang)) {
                        await window.AppDB.saveUtangList(data.utang);
                    }
                    await window.AppDB.setMeta('last_cache_time', new Date().toISOString());
                }
            }
        } catch (e) {
            // Silently ignore network fetch errors during background pre-warming
        }
    }

    // Synchronize Pending Offline Transactions to Server
    async function syncPendingTransactions(manual = false) {
        if (isSyncing || !navigator.onLine || !window.AppDB) {
            if (manual && !navigator.onLine) {
                showToast('Tidak dapat menyinkronkan: perangkat masih dalam mode offline.', 'warning');
            }
            return;
        }

        try {
            const queue = await window.AppDB.getAntreanUtang();
            if (!queue || queue.length === 0) {
                if (manual) {
                    showToast('Semua transaksi sudah tersinkronkan dengan server.', 'info');
                }
                updateStatusUI();
                return;
            }

            isSyncing = true;
            showToast(`Mengirim ${queue.length} transaksi offline ke server...`, 'info', 2500);

            const response = await fetch('/utang/sinkronisasi-offline', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ items: queue })
            });

            if (!response.ok) {
                throw new Error(`Server returned status ${response.status}`);
            }

            const result = await response.json();

            if (result.success && Array.isArray(result.synced)) {
                // Remove synced items from queue
                for (const item of result.synced) {
                    await window.AppDB.removeAntreanUtang(item.local_id);
                }

                showToast(`✅ Berhasil menyinkronkan ${result.synced_count} transaksi ke server!`, 'success', 4500);

                // Refresh cache from server
                await refreshOfflineCache();
                await updateStatusUI();

                // If currently on utang page, refresh page to show real server IDs
                if (window.location.pathname.includes('/utang')) {
                    setTimeout(() => window.location.reload(), 1200);
                }
            } else {
                showToast('Sebagian transaksi offline gagal disinkronkan. Akan dicoba kembali nanti.', 'warning');
            }
        } catch (err) {
            console.error('[Sync] Synchronization failed:', err);
            if (manual) {
                showToast('Gagal menyinkronkan transaksi. Periksa koneksi internet Anda.', 'error');
            }
        } finally {
            isSyncing = false;
            updateStatusUI();
        }
    }

    // Intercept Add Debt Form (Tahap 3 - Input Offline)
    function setupOfflineFormHandling() {
        const form = document.querySelector('form[action*="/utang/simpan"]');
        if (!form) return;

        const pelangganSelect = document.getElementById('pelanggan_id');

        // If offline or dropdown is empty, populate from IndexedDB
        async function checkAndPopulatePelanggan() {
            if (!pelangganSelect || !window.AppDB) return;

            if (pelangganSelect.options.length <= 1 || !navigator.onLine) {
                try {
                    const cachedPelanggan = await window.AppDB.getPelangganList('aktif');
                    if (cachedPelanggan && cachedPelanggan.length > 0) {
                        // Preserve selected value if any
                        const selectedVal = pelangganSelect.value;
                        pelangganSelect.innerHTML = '<option value="">-- Pilih pelanggan (Tersimpan Offline) --</option>';
                        cachedPelanggan.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = p.nama + (p.no_hp ? ` (${p.no_hp})` : '');
                            if (String(p.id) === String(selectedVal)) {
                                opt.selected = true;
                            }
                            pelangganSelect.appendChild(opt);
                        });
                    }
                } catch (e) {
                    console.warn('Failed to load cached pelanggan into select:', e);
                }
            }
        }

        checkAndPopulatePelanggan();

        // Form submit interceptor
        form.addEventListener('submit', async function (e) {
            const isOffline = !navigator.onLine;

            // If offline, prevent standard HTTP POST and save to IndexedDB queue directly
            if (isOffline) {
                e.preventDefault();
                await saveTransactionOffline(form);
            }
        });
    }

    // Helper: Save Form Data into IndexedDB Antrean
    async function saveTransactionOffline(form) {
        if (!window.AppDB) {
            showToast('Penyimpanan lokal tidak tersedia.', 'error');
            return;
        }

        const pelangganSelect = form.querySelector('#pelanggan_id');
        const pelangganId = pelangganSelect ? pelangganSelect.value : '';
        const pelangganNama = pelangganSelect && pelangganSelect.selectedOptions[0]
            ? pelangganSelect.selectedOptions[0].textContent.split(' (')[0].trim()
            : 'Pelanggan';

        if (!pelangganId) {
            showToast('Pilih nama pelanggan terlebih dahulu.', 'warning');
            return;
        }

        const tanggalUtang = form.querySelector('#tanggal_utang').value || new Date().toISOString();
        const tanggalJatuhTempo = form.querySelector('#tanggal_jatuh_tempo')?.value || null;
        const catatan = form.querySelector('#catatan')?.value || '';

        const namaBarangInputs = form.querySelectorAll('input[name="nama_barang[]"]');
        const jumlahInputs = form.querySelectorAll('input[name="jumlah[]"]');
        const satuanInputs = form.querySelectorAll('input[name="satuan[]"]');
        const hargaInputs = form.querySelectorAll('input[name="harga_satuan[]"]');

        const rincian = [];
        let totalHutang = 0;

        for (let i = 0; i < namaBarangInputs.length; i++) {
            const nama = (namaBarangInputs[i].value || '').trim();
            const qty = parseFloat((jumlahInputs[i].value || '0').replace(',', '.')) || 0;
            const satuan = (satuanInputs[i].value || 'pcs').trim();
            const harga = parseInt(hargaInputs[i].value || '0', 10) || 0;

            if (nama !== '' && qty > 0 && harga >= 0) {
                const subtotal = Math.round(qty * harga);
                totalHutang += subtotal;
                rincian.push({
                    nama_barang: nama,
                    jumlah: qty,
                    satuan: satuan,
                    harga_satuan: harga,
                    total_harga: subtotal
                });
            }
        }

        if (rincian.length === 0 || totalHutang <= 0) {
            showToast('Isi minimal 1 rincian barang dengan jumlah dan harga yang valid.', 'warning');
            return;
        }

        const offlineTx = {
            local_id: 'OFFLINE-' + Date.now() + '-' + Math.random().toString(36).substr(2, 4).toUpperCase(),
            pelanggan_id: Number(pelangganId),
            nama_pelanggan: pelangganNama,
            tanggal_utang: tanggalUtang,
            tanggal_jatuh_tempo: tanggalJatuhTempo,
            catatan: catatan,
            rincian: rincian,
            total_hutang: totalHutang,
            created_at: new Date().toISOString()
        };

        try {
            await window.AppDB.addToAntreanUtang(offlineTx);
            await updateStatusUI();

            // Request background sync tag if supported
            if ('serviceWorker' in navigator && 'SyncManager' in window && window.swRegistration) {
                try {
                    await window.swRegistration.sync.register('sync-utang-offline');
                } catch (e) {
                    // Ignore background sync register error
                }
            }

            // Show friendly notification modal/alert
            const message = `Transaksi hutang sebesar Rp${totalHutang.toLocaleString('id-ID')} untuk ${pelangganNama} berhasil disimpan ke Antrean Offline! Data akan otomatis dikirim ke server saat internet kembali terhubung.`;
            showToast(message, 'success', 6000);

            // Redirect to Utang page after a short moment
            setTimeout(() => {
                window.location.href = '/utang';
            }, 1800);
        } catch (err) {
            console.error('Failed to save offline transaction:', err);
            showToast('Gagal menyimpan ke penyimpanan lokal: ' + err.message, 'error');
        }
    }

    // Render Offline Data on Utang Index if Offline
    async function setupOfflineUtangList() {
        const table = document.querySelector('main.wadah table tbody');
        if (!table || !window.location.pathname.match(/\/utang(\/index)?$/)) return;

        // Check if there are queued offline items and display them at the top
        if (window.AppDB) {
            try {
                const queue = await window.AppDB.getAntreanUtang();
                if (queue && queue.length > 0) {
                    queue.forEach(item => {
                        // Check if row already rendered
                        if (document.getElementById(`row-${item.local_id}`)) return;

                        const tr = document.createElement('tr');
                        tr.id = `row-${item.local_id}`;
                        tr.className = 'row-offline-pending';

                        const dateStr = item.tanggal_utang ? new Date(item.tanggal_utang).toLocaleDateString('id-ID') : '-';
                        const totalStr = Number(item.total_hutang || 0).toLocaleString('id-ID');

                        tr.innerHTML = `
                            <td>${dateStr}</td>
                            <td><strong style="color: #b45309;">${item.local_id}</strong></td>
                            <td>${item.nama_pelanggan}</td>
                            <td style="text-align: right;">Rp${totalStr}</td>
                            <td style="text-align: right;">Rp0</td>
                            <td style="text-align: right;"><strong class="text-danger">Rp${totalStr}</strong></td>
                            <td style="text-align: center;">
                                <span class="status-badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                                    Antrean Offline ⏳
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span style="font-size:12px; color:#92400e; font-weight:600;">Menunggu Online</span>
                            </td>
                        `;

                        // Insert at top of table
                        if (table.firstChild) {
                            table.insertBefore(tr, table.firstChild);
                        } else {
                            table.appendChild(tr);
                        }
                    });
                }
            } catch (e) {
                console.warn('Error rendering offline utang queue:', e);
            }
        }

        // If offline and table has no server records, load from IndexedDB
        if (!navigator.onLine && window.AppDB) {
            try {
                const cachedUtang = await window.AppDB.getUtangList();
                if (cachedUtang && cachedUtang.length > 0 && table.rows.length <= 1) {
                    table.innerHTML = '';
                    cachedUtang.forEach(u => {
                        const tr = document.createElement('tr');
                        const dateStr = u.tanggal_utang ? new Date(u.tanggal_utang).toLocaleDateString('id-ID') : '-';
                        const totalStr = Number(u.total_hutang || 0).toLocaleString('id-ID');
                        const bayarStr = Number(u.total_bayar || 0).toLocaleString('id-ID');
                        const sisaStr = Number(u.sisa_hutang || 0).toLocaleString('id-ID');
                        const isLunas = Number(u.sisa_hutang) === 0;

                        tr.innerHTML = `
                            <td>${dateStr}</td>
                            <td><strong>${u.nomor_transaksi}</strong></td>
                            <td>${u.nama_pelanggan}</td>
                            <td style="text-align: right;">Rp${totalStr}</td>
                            <td style="text-align: right;">Rp${bayarStr}</td>
                            <td style="text-align: right;"><strong class="${isLunas ? 'text-success' : 'text-danger'}">Rp${sisaStr}</strong></td>
                            <td style="text-align: center;">
                                <span class="status-badge ${isLunas ? 'badge-lunas' : 'badge-belum'}">
                                    ${isLunas ? 'Lunas' : 'Belum Bayar'} (Offline)
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span style="font-size:12px; color:#6d7d72;">Tersimpan</span>
                            </td>
                        `;
                        table.appendChild(tr);
                    });
                }
            } catch (e) {
                console.warn('Error rendering cached utang:', e);
            }
        }
    }

    // Render Offline Data on Pelanggan Index if Offline
    async function setupOfflinePelangganList() {
        const table = document.querySelector('main.wadah table tbody');
        if (!table || !window.location.pathname.match(/\/pelanggan(\/index)?$/)) return;

        // If offline, populate from IndexedDB
        if (!navigator.onLine && window.AppDB) {
            try {
                const cachedPelanggan = await window.AppDB.getPelangganList('aktif');
                if (cachedPelanggan && cachedPelanggan.length > 0 && table.rows.length <= 1) {
                    table.innerHTML = '';
                    cachedPelanggan.forEach(p => {
                        const tr = document.createElement('tr');
                        const sisaStr = Number(p.total_sisa_hutang || 0).toLocaleString('id-ID');

                        tr.innerHTML = `
                            <td><strong>${p.nama}</strong></td>
                            <td>${p.no_hp || '-'}</td>
                            <td>${p.alamat || '-'}<br><small style="color:#63776b;">${p.catatan || ''}</small></td>
                            <td style="text-align: right;"><strong class="text-danger">Rp${sisaStr}</strong></td>
                            <td style="text-align: right;">
                                <span style="font-size:12px; color:#6d7d72;">Tersimpan Offline</span>
                            </td>
                        `;
                        table.appendChild(tr);
                    });
                }
            } catch (e) {
                console.warn('Error rendering cached pelanggan:', e);
            }
        }
    }

    // Initialize all PWA & Offline listeners
    function init() {
        registerServiceWorker();
        initInstallPrompt();
        updateStatusUI();

        // Listen for online / offline events
        window.addEventListener('online', () => {
            updateStatusUI();
            showToast('Koneksi internet terhubung kembali. Memulai sinkronisasi...', 'success');
            syncPendingTransactions();
            refreshOfflineCache();
        });

        window.addEventListener('offline', () => {
            updateStatusUI();
            showToast('Koneksi internet terputus. Mode offline aktif (Data disimpan lokal).', 'warning');
        });

        // Setup page-specific offline handling
        setupOfflineFormHandling();
        setupOfflineUtangList();
        setupOfflinePelangganList();

        // Initial background cache pre-warming
        if (navigator.onLine) {
            setTimeout(refreshOfflineCache, 1500);
            setTimeout(syncPendingTransactions, 2500);
        }
    }

    return {
        init,
        showToast,
        syncPendingTransactions,
        refreshOfflineCache,
        updateStatusUI
    };
})();

// Auto-boot SyncManager on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', SyncManager.init);
} else {
    SyncManager.init();
}

window.SyncManager = SyncManager;
