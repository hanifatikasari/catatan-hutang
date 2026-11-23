<?php

namespace App\Controllers;

use App\Models\PelangganModel;

class Pelanggan extends BaseController
{
    protected $pelangganModel;

    public function __construct()
    {
        $this->pelangganModel = new PelangganModel();
    }

    /**
     * AJAX autocomplete pelanggan aktif untuk form transaksi hutang.
     */
    public function cariAjax()
    {
        $q = trim((string) $this->request->getGet('q'));

        if (mb_strlen($q) < 2) {
            return $this->response
                ->setHeader('Cache-Control', 'no-store')
                ->setJSON(['results' => []]);
        }

        $results = $this->pelangganModel
            ->select('id, nama, no_hp')
            ->where('aktif', 1)
            ->groupStart()
                ->like('nama', $q)
                ->orLike('no_hp', $q)
            ->groupEnd()
            ->orderBy('nama', 'ASC')
            ->findAll(10);

        return $this->response
            ->setHeader('Cache-Control', 'no-store')
            ->setJSON(['results' => $results]);
    }

    // Menampilkan daftar pelanggan
    public function index()
    {
        $status = $this->request->getGet('status') ?? 'aktif';
        $cari = trim($this->request->getGet('cari') ?? '');
        $aktif = ($status === 'tidak_aktif') ? 0 : 1;
        $db = db_connect();

        $builder = $db->table('pelanggan p')
            ->select("p.*,
                (SELECT COALESCE(SUM(GREATEST(0,
                    (SELECT COALESCE(SUM(r.total_harga), 0) FROM rincian_utang r WHERE r.utang_id = u.id) -
                    (SELECT COALESCE(SUM(b.jumlah_bayar), 0) FROM pembayaran b WHERE b.utang_id = u.id)
                )), 0) FROM utang u WHERE u.pelanggan_id = p.id AND u.dibatalkan = 0) AS total_sisa_hutang")
            ->where('p.aktif', $aktif);

        if ($cari !== '') {
            $builder->groupStart()
                ->like('p.nama', $cari)
                ->orLike('p.no_hp', $cari)
                ->orLike('p.alamat', $cari)
                ->orLike('p.catatan', $cari)
                ->groupEnd();
        }

        return view('pelanggan/index', [
            'judul' => 'Daftar Pelanggan',
            'pelanggan' => $builder->orderBy('p.nama', 'ASC')->get()->getResultArray(),
            'status' => $status,
            'cari' => $cari,
        ]);
    }

    // Menampilkan formulir tambah pelanggan
    public function tambah()
    {
        $data = [
            'judul' => 'Tambah Pelanggan',
            'pelanggan' => [
                'id'      => '',
                'nama'    => '',
                'no_hp'   => '',
                'alamat'  => '',
                'catatan' => ''
            ]
        ];

        return view('pelanggan/form', $data);
    }

    // Menyimpan pelanggan baru
    public function simpan()
    {
        $data = [
            'nama'   => trim($this->request->getPost('nama') ?? ''),
            'no_hp'  => trim($this->request->getPost('no_hp') ?? ''),
            'alamat' => trim($this->request->getPost('alamat') ?? ''),
            'catatan' => trim($this->request->getPost('catatan') ?? ''),
        ];

        $rules = [
            'nama' => 'required|max_length[100]',
            'no_hp' => 'permit_empty|max_length[15]',
            'alamat' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Cek apakah nama sudah pernah tercatat
        $duplikat = $this->cariNamaDuplikat($data['nama']);

        if (!empty($duplikat)) {
            return view('pelanggan/duplikat', [
                'pelangganBaru' => $data,
                'duplikat' => $duplikat,
            ]);
        }

        // Jika tidak ada duplikat, simpan pelanggan
        $this->pelangganModel->insert([
            'nama' => $data['nama'],
            'no_hp' => $this->nilaiKosongJadiNull($data['no_hp']),
            'alamat' => $this->nilaiKosongJadiNull($data['alamat']),
            'catatan' => $this->nilaiKosongJadiNull($data['catatan']),
            'aktif' => 1,
        ]);

        return redirect()->to('/pelanggan')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }


    // Menampilkan formulir edit
    public function edit($id)
    {
        $pelanggan = $this->pelangganModel->find($id);

        if (!$pelanggan) {
            return redirect()->to('/pelanggan')
                ->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $data = [
            'judul' => 'Ubah Data Pelanggan',
            'pelanggan' => $pelanggan
        ];

        return view('pelanggan/form', $data);
    }

    // Memperbarui pelanggan
    public function perbarui($id)
    {
        $pelanggan = $this->pelangganModel->find($id);

        if (!$pelanggan) {
            return redirect()->to('/pelanggan')
                ->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $aturan = [
            'nama' => 'required|max_length[100]',
            'no_hp' => 'permit_empty|max_length[15]',
            'alamat' => 'permit_empty|max_length[255]'
        ];

        if (!$this->validate($aturan)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->pelangganModel->update($id, [
            'nama'    => trim($this->request->getPost('nama')),
            'no_hp'   => $this->nilaiKosongJadiNull('no_hp'),
            'alamat'  => $this->nilaiKosongJadiNull('alamat'),
            'catatan' => $this->nilaiKosongJadiNull('catatan')
        ]);

        return redirect()->to('/pelanggan')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    // Menonaktifkan pelanggan
    public function nonaktifkan($id)
    {
        $pelanggan = $this->pelangganModel->find($id);

        if (!$pelanggan) {
            return redirect()->to('/pelanggan')
                ->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $this->pelangganModel->update($id, [
            'aktif' => 0
        ]);

        return redirect()->to('/pelanggan')
            ->with('success', 'Pelanggan berhasil dinonaktifkan.');
    }

    // Mengubah input kosong menjadi NULL
    private function nilaiKosongJadiNull($kolom)
    {
        $nilai = trim((string) $this->request->getPost($kolom));

        return $nilai === '' ? null : $nilai;
    }

    private function normalisasiNama(string $nama): string
    {
        // Hapus spasi di awal/akhir dan rapikan spasi berlebih
        $nama = trim($nama);
        $nama = preg_replace('/\s+/u', ' ', $nama);

        // Samakan huruf besar dan kecil
        return mb_strtolower($nama, 'UTF-8');
    }

    private function cariNamaDuplikat(string $nama): array
    {
        $namaNormal = $this->normalisasiNama($nama);

        // Cari dari semua pelanggan, termasuk yang tidak aktif
        $semuaPelanggan = $this->pelangganModel
            ->findAll();

        $hasil = [];

        foreach ($semuaPelanggan as $pelanggan) {
            $namaTersimpan = $this->normalisasiNama(
                $pelanggan['nama']
            );

            if ($namaNormal === $namaTersimpan) {
                $hasil[] = $pelanggan;
            }
        }

        return $hasil;
    }


    public function aktifkan($id)
    {
        $pelanggan = $this->pelangganModel->find($id);

        if (!$pelanggan) {
            return redirect()->to('/pelanggan')
                ->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $this->pelangganModel->update($id, [
            'aktif' => 1,
        ]);

        return redirect()->to('/pelanggan')
            ->with('success', 'Pelanggan berhasil diaktifkan kembali.');
    }

    public function simpanTetap()
    {
        $data = [
            'nama' => trim($this->request->getPost('nama') ?? ''),
            'no_hp' => trim($this->request->getPost('no_hp') ?? ''),
            'alamat' => trim($this->request->getPost('alamat') ?? ''),
            'catatan' => trim($this->request->getPost('catatan') ?? ''),
        ];

        $rules = [
            'nama' => 'required|max_length[100]',
            'no_hp' => 'permit_empty|max_length[15]',
            'alamat' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Simpan sebagai pelanggan baru karena ibu sudah
        // memilih bahwa ini orang yang berbeda.
        $this->pelangganModel->insert([
            'nama' => $data['nama'],
            'no_hp' => $this->nilaiKosongJadiNull($data['no_hp']),
            'alamat' => $this->nilaiKosongJadiNull($data['alamat']),
            'catatan' => $this->nilaiKosongJadiNull($data['catatan']),
            'aktif' => 1,
        ]);

        return redirect()->to('/pelanggan')
            ->with('success', 'Pelanggan baru berhasil ditambahkan.');
    }

    // Menampilkan ringkasan seluruh transaksi hutang milik pelanggan.
    public function hutang($id)
    {
        $pelanggan = $this->pelangganModel->find($id);
        if (!$pelanggan) {
            return redirect()->to('/pelanggan')->with('error', 'Data pelanggan tidak ditemukan.');
        }

        $utang = db_connect()->table('utang u')
            ->select("u.id, u.nomor_transaksi, u.tanggal_utang, u.tanggal_jatuh_tempo, u.dibatalkan,
                (SELECT COALESCE(SUM(r.total_harga), 0) FROM rincian_utang r WHERE r.utang_id = u.id) AS total_hutang,
                (SELECT COALESCE(SUM(b.jumlah_bayar), 0) FROM pembayaran b WHERE b.utang_id = u.id) AS total_bayar")
            ->where('u.pelanggan_id', $id)
            ->orderBy('u.tanggal_utang', 'DESC')
            ->get()->getResultArray();

        $totalSisa = 0;
        foreach ($utang as &$transaksi) {
            $transaksi['sisa_hutang'] = (int) $transaksi['dibatalkan'] === 1
                ? 0
                : max(0, (int) $transaksi['total_hutang'] - (int) $transaksi['total_bayar']);
            $totalSisa += $transaksi['sisa_hutang'];
        }
        unset($transaksi);

        return view('pelanggan/hutang', [
            'judul' => 'Ringkasan Hutang Pelanggan',
            'pelanggan' => $pelanggan,
            'utang' => $utang,
            'totalSisa' => $totalSisa,
        ]);
    }

}
