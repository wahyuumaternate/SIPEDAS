/**
 * Draft otomatis untuk formulir pendataan.
 *
 * - Setiap perubahan disimpan ke peramban (localStorage) ±1 detik setelah mengetik — tetap jalan saat offline.
 * - Draft juga dikirim ke server setiap beberapa detik bila online (dicoba ulang saat koneksi kembali).
 * - Saat formulir dibuka lagi, tersedia pilihan "Lanjutkan Draft" atau "Buang Draft".
 * - Draft dihapus server setelah data berhasil disimpan; layout membersihkan salinan di peramban.
 *
 * Pemakaian: DraftFormulir.pasang(formElement, { kunci, url, userId, status, repeaters, abaikan, adaInputLama, diperbaruiPada })
 */
(function () {
    'use strict';

    const JEDA_LOKAL = 800;
    const JEDA_SERVER = 4000;
    const JEDA_ULANG = 30000;
    const TIPE_DIABAIKAN = ['file', 'hidden', 'button', 'submit', 'reset', 'image'];

    function waktu(iso) {
        return new Date(iso).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    }

    function pasang(form, opsi) {
        if (!form || !opsi || !opsi.kunci) {
            return;
        }

        const kunciLokal = `draft:${opsi.userId}:${opsi.kunci}`;
        const token = (form.querySelector('input[name="_token"]') || {}).value;
        const status = opsi.status || null;
        const repeaters = opsi.repeaters || {};
        let jedaLokal = null;
        let jedaServer = null;
        let perluKirim = false;
        let sedangPulih = false;
        let sedangKirimForm = false;

        // ===== Serialisasi isian (tanpa file, token, & input tersembunyi) =====
        function diabaikan(el) {
            const tipe = (el.type || '').toLowerCase();
            return !el.name || el.name === '_token' || el.name === '_method' || TIPE_DIABAIKAN.includes(tipe)
                || (opsi.abaikan instanceof RegExp && opsi.abaikan.test(el.name));
        }

        function serialisasi() {
            const hasil = [];
            Array.from(form.elements).forEach(function (el) {
                if (diabaikan(el) || el.disabled) {
                    return;
                }
                if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) {
                    return;
                }
                hasil.push([el.name, String(el.value ?? '')]);
            });
            return hasil;
        }

        const isianAwal = JSON.stringify(serialisasi());

        // ===== Penyimpanan lokal =====
        function bacaLokal() {
            try {
                return JSON.parse(localStorage.getItem(kunciLokal));
            } catch (e) {
                return null;
            }
        }

        function simpanLokal(draft) {
            try {
                localStorage.setItem(kunciLokal, JSON.stringify(draft));
                return true;
            } catch (e) {
                return false;
            }
        }

        function hapusLokal() {
            try {
                localStorage.removeItem(kunciLokal);
            } catch (e) {
                // abaikan
            }
        }

        function tulisStatus(teks, kelas) {
            if (status) {
                status.textContent = teks;
                status.className = 'small ' + (kelas || 'text-muted');
            }
        }

        // ===== Server =====
        function permintaan(metode, body, keepalive) {
            return fetch(opsi.url, {
                method: metode,
                keepalive: !!keepalive,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body ? JSON.stringify(body) : undefined,
            });
        }

        async function kirimServer(keepalive) {
            clearTimeout(jedaServer);
            const draft = bacaLokal();
            if (!perluKirim || !draft) {
                return;
            }
            try {
                const respons = await permintaan('PUT', { isian: draft.isian, disimpan_pada: draft.disimpanPada }, keepalive);
                if (!respons.ok) {
                    throw new Error(String(respons.status));
                }
                perluKirim = false;
                tulisStatus(`Draft tersimpan otomatis · ${waktu(draft.disimpanPada)}`, 'text-success');
            } catch (e) {
                tulisStatus(`Draft tersimpan di perangkat · ${waktu(draft.disimpanPada)} (belum terkirim, menunggu koneksi)`, 'text-warning-emphasis');
                jedaServer = setTimeout(kirimServer, JEDA_ULANG);
            }
        }

        function hapusServer() {
            return permintaan('DELETE').catch(function () {});
        }

        // ===== Simpan saat mengetik =====
        function simpan() {
            if (sedangPulih || sedangKirimForm) {
                return;
            }
            const isian = serialisasi();
            if (JSON.stringify(isian) === isianAwal) {
                return;
            }
            const draft = { isian: isian, disimpanPada: new Date().toISOString() };
            if (!simpanLokal(draft)) {
                tulisStatus('Draft tidak dapat disimpan di perangkat (penyimpanan penuh/diblokir).', 'text-danger');
            } else {
                tulisStatus(`Draft tersimpan di perangkat · ${waktu(draft.disimpanPada)}`);
            }
            perluKirim = true;
            clearTimeout(jedaServer);
            jedaServer = setTimeout(kirimServer, JEDA_SERVER);
        }

        function jadwalkanSimpan() {
            clearTimeout(jedaLokal);
            jedaLokal = setTimeout(simpan, JEDA_LOKAL);
        }

        // ===== Pulihkan draft =====
        function pulihkan(isian) {
            sedangPulih = true;

            // Baris dinamis (mis. anggota keluarga) dibangun ulang, lalu indeks lama dipetakan ke indeks baru.
            const peta = {};
            Object.keys(repeaters).forEach(function (prefix) {
                const repeater = repeaters[prefix];
                const pola = new RegExp('^' + prefix + '\\[(\\d+)\\]');
                const indeksLama = [];
                isian.forEach(function (pasangan) {
                    const cocok = pasangan[0].match(pola);
                    if (cocok && !indeksLama.includes(cocok[1])) {
                        indeksLama.push(cocok[1]);
                    }
                });
                if (!indeksLama.length || !repeater.container) {
                    return;
                }
                repeater.container.innerHTML = '';
                indeksLama.forEach(function (lama) {
                    repeater.add();
                    const contoh = repeater.container.lastElementChild
                        && repeater.container.lastElementChild.querySelector('[name^="' + prefix + '["]');
                    const baru = contoh && contoh.name.match(pola);
                    if (baru) {
                        peta[prefix + '[' + lama + ']'] = prefix + '[' + baru[1] + ']';
                    }
                });
            });

            const nilai = new Map();
            isian.forEach(function (pasangan) {
                const cocok = pasangan[0].match(/^([a-z_]+\[\d+\])(.*)$/);
                const nama = cocok && peta[cocok[1]] ? peta[cocok[1]] + cocok[2] : pasangan[0];
                if (!nilai.has(nama)) {
                    nilai.set(nama, []);
                }
                nilai.get(nama).push(pasangan[1]);
            });

            Array.from(form.elements).forEach(function (el) {
                if (diabaikan(el)) {
                    return;
                }
                const isi = nilai.get(el.name);
                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = !!isi && isi.includes(el.value);
                    return;
                }
                if (isi === undefined) {
                    return;
                }
                el.value = isi[isi.length - 1];
                // Select bertingkat (mis. kecamatan → desa) perlu memicu pembaruan pilihan turunannya.
                if (el.tagName === 'SELECT') {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            sedangPulih = false;
        }

        function tampilkanTawaran(draft) {
            const banner = document.createElement('div');
            banner.className = 'alert alert-warning d-flex flex-wrap align-items-center gap-2';
            banner.setAttribute('role', 'status');
            banner.innerHTML = '<i class="bi bi-clock-history" aria-hidden="true"></i>'
                + '<span class="me-auto">Ada draft isian yang belum disimpan (terakhir <strong></strong>). Lanjutkan pengisian?</span>'
                + '<button type="button" class="btn btn-warning btn-sm" data-aksi="pulihkan">Lanjutkan Draft</button>'
                + '<button type="button" class="btn btn-outline-secondary btn-sm" data-aksi="buang">Buang Draft</button>';
            banner.querySelector('strong').textContent = waktu(draft.disimpanPada);
            form.parentNode.insertBefore(banner, form);

            banner.querySelector('[data-aksi="pulihkan"]').addEventListener('click', function () {
                pulihkan(draft.isian);
                banner.remove();
                tulisStatus(`Draft dipulihkan · ${waktu(draft.disimpanPada)}. Periksa kembali sebelum menyimpan.`, 'text-success');
            });
            banner.querySelector('[data-aksi="buang"]').addEventListener('click', function () {
                if (!confirm('Buang draft ini? Isian draft tidak dapat dikembalikan.')) {
                    return;
                }
                hapusLokal();
                hapusServer();
                banner.remove();
                tulisStatus('Draft dibuang.');
            });
        }

        async function mulai() {
            const lokal = bacaLokal();
            let server = null;
            try {
                const respons = await fetch(opsi.url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (respons.ok) {
                    const json = await respons.json();
                    if (json.draft) {
                        server = { isian: json.draft.isian, disimpanPada: json.draft.disimpan_pada };
                    }
                }
            } catch (e) {
                // offline: pakai draft lokal saja
            }

            const kandidat = [lokal, server]
                .filter(function (d) { return d && Array.isArray(d.isian); })
                .sort(function (a, b) { return new Date(b.disimpanPada) - new Date(a.disimpanPada); })[0];

            if (!kandidat) {
                return;
            }

            // Data sudah diubah & disimpan setelah draft dibuat: draft usang.
            if (opsi.diperbaruiPada && new Date(kandidat.disimpanPada) < new Date(opsi.diperbaruiPada)) {
                hapusLokal();
                hapusServer();
                return;
            }

            if (JSON.stringify(kandidat.isian) === isianAwal) {
                return;
            }

            if (kandidat !== lokal) {
                simpanLokal(kandidat);
            } else if (!server || new Date(server.disimpanPada) < new Date(lokal.disimpanPada)) {
                // Draft lokal belum sempat terkirim (mis. saat offline): kirim sekarang.
                perluKirim = true;
                kirimServer();
            }

            // Formulir yang dikembalikan karena gagal validasi sudah berisi isian terakhir.
            if (opsi.adaInputLama) {
                return;
            }

            tampilkanTawaran(kandidat);
        }

        // ===== Pemicu =====
        form.addEventListener('input', jadwalkanSimpan);
        form.addEventListener('change', jadwalkanSimpan);
        new MutationObserver(jadwalkanSimpan).observe(form, { childList: true, subtree: true });

        form.addEventListener('submit', function () {
            // Simpan isian terakhir; draft baru dihapus setelah server menyatakan data tersimpan.
            clearTimeout(jedaLokal);
            simpan();
            sedangKirimForm = true;
        });

        // Kembali ke halaman (tombol back) setelah pengiriman gagal: lanjutkan menyimpan draft.
        window.addEventListener('pageshow', function () {
            sedangKirimForm = false;
        });

        window.addEventListener('online', function () {
            kirimServer();
        });
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                clearTimeout(jedaLokal);
                simpan();
                kirimServer(true);
            }
        });

        tulisStatus('Isian tersimpan otomatis sebagai draft saat Anda mengetik.');
        mulai();
    }

    window.DraftFormulir = { pasang: pasang };
})();
