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

    // Menampilkan daftar pelanggan
    public function index()
    {
        $status = $this->request->getGet('status') ?? 'aktif';
        $cari = trim($this->request->getGet('cari') ?? '');
        $aktif = ($status === 'tidak_aktif') ? 0 : 1;

        $builder = $this->pelangganModel
            ->where('aktif', $aktif);

        if ($cari !== '') {
            $builder->groupStart()
                ->like('nama', $cari)
                ->orLike('no_hp', $cari)
                ->orLike('alamat', $cari)
                ->orLike('catatan', $cari)
                ->groupEnd();
        }

        $data = [
            'judul'       => 'Daftar Pelanggan',
            'pelanggan' => $builder->orderBy('nama', 'ASC')->findAll(),
            'status' => $status,
            'cari' => $cari,
        ];

        return view('pelanggan/index', $data);
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

}

