<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use App\Models\UtangModel;
use App\Models\RincianUtangModel;

class Utang extends BaseController
{
    protected $pelangganModel;
    protected $utangModel;
    protected $rincianModel;\n    protected $pembayaranModel;

    public function __construct()
    {
        $this->pelangganModel = new PelangganModel();
        $this->utangModel = new UtangModel();
        $this->rincianModel = new RincianUtangModel();\n        $this->pembayaranModel = new PembayaranModel();
    }

    public function index()
    {
        $cari = trim($this->request->getGet('cari') ?? '');
        $db = db_connect();

        $builder = $db->table('utang u')
            ->select("u.*, p.nama AS nama_pelanggan,
                (SELECT COALESCE(SUM(r.total_harga), 0) FROM rincian_utang r WHERE r.utang_id = u.id) AS total_hutang,
                (SELECT COALESCE(SUM(b.jumlah_bayar), 0) FROM pembayaran b WHERE b.utang_id = u.id) AS total_bayar")
            ->join('pelanggan p', 'p.id = u.pelanggan_id')
            ->where('u.dibatalkan', 0);

        if ($cari !== '') {
            $builder->groupStart()
                ->like('p.nama', $cari)
                ->orLike('u.nomor_transaksi', $cari)
                ->groupEnd();
        }

        $data = [
            'judul' => 'Catatan Hutang',
            'utang' => $builder->orderBy('u.tanggal_utang', 'DESC')->get()->getResultArray(),
            'cari' => $cari,
        ];

        return view('utang/index', $data);
    }

    public function tambah()
    {
        $pelanggan = $this->pelangganModel
            ->where('aktif', 1)
            ->orderBy('nama', 'ASC')
            ->findAll();

        return view('utang/form', [
            'judul' => 'Catat Hutang Baru',
            'pelanggan' => $pelanggan,
        ]);
    }

    public function simpan()
    {
        $pelangganId = (int) $this->request->getPost('pelanggan_id');
        $pelanggan = $this->pelangganModel->find($pelangganId);

        if (!$pelanggan || (int) $pelanggan['aktif'] !== 1) {
            return redirect()->back()->withInput()
                ->with('error', 'Pilih pelanggan aktif yang valid.');
        }

        $tanggal = trim($this->request->getPost('tanggal_utang') ?? '');
        $jatuhTempo = trim($this->request->getPost('tanggal_jatuh_tempo') ?? '');
        $catatan = trim($this->request->getPost('catatan') ?? '');

        if ($tanggal === '') {
            return redirect()->back()->withInput()
                ->with('error', 'Tanggal hutang wajib diisi.');
        }

        $barang = $this->request->getPost('nama_barang');
        $jumlah = $this->request->getPost('jumlah');
        $satuan = $this->request->getPost('satuan');
        $harga = $this->request->getPost('harga_satuan');

        if (!is_array($barang) || !is_array($jumlah) || !is_array($satuan) || !is_array($harga)) {
            return redirect()->back()->withInput()
                ->with('error', 'Rincian barang tidak valid.');
        }

        $rincian = [];
        $totalHutang = 0;
        $jumlahBaris = count($barang);

        for ($i = 0; $i < $jumlahBaris; $i++) {
            $nama = trim((string) ($barang[$i] ?? ''));
            $qtyRaw = trim((string) ($jumlah[$i] ?? ''));
            $unit = trim((string) ($satuan[$i] ?? ''));
            $hargaRaw = trim((string) ($harga[$i] ?? ''));

            // Baris kosong boleh diabaikan.
            if ($nama === '' && $qtyRaw === '' && $unit === '' && $hargaRaw === '') {
                continue;
            }

            $qtyRaw = str_replace(',', '.', $qtyRaw);

            if ($nama === '' || $unit === '' || !is_numeric($qtyRaw) || (float) $qtyRaw <= 0
                || !ctype_digit($hargaRaw) || (int) $hargaRaw < 0) {
                return redirect()->back()->withInput()
                    ->with('error', 'Periksa nama barang, jumlah, satuan, dan harga pada baris ' . ($i + 1) . '.');
            }

            $qty = (float) $qtyRaw;
            $hargaSatuan = (int) $hargaRaw;
            $subtotal = (int) round($qty * $hargaSatuan);

            if ($subtotal < 0 || $totalHutang > PHP_INT_MAX - $subtotal) {
                return redirect()->back()->withInput()
                    ->with('error', 'Nilai total hutang terlalu besar.');
            }

            $totalHutang += $subtotal;
            $rincian[] = [
                'nama_barang' => $nama,
                'jumlah' => $qty,
                'satuan' => $unit,
                'harga_satuan' => $hargaSatuan,
                'total_harga' => $subtotal,
            ];
        }

        if (empty($rincian) || $totalHutang <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'Tambahkan minimal satu barang dengan total harga lebih dari Rp0.');
        }

        $db = db_connect();
        $db->transBegin();

        $nomor = 'HTG-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $utangId = $this->utangModel->insert([
            'pelanggan_id' => $pelangganId,
            'nomor_transaksi' => $nomor,
            'tanggal_utang' => date('Y-m-d H:i:s', strtotime($tanggal)),
            'tanggal_jatuh_tempo' => $jatuhTempo !== '' ? $jatuhTempo : null,
            'catatan' => $catatan !== '' ? $catatan : null,
            'dibatalkan' => 0,
            'alasan_batal' => null,
        ], true);

        if (!$utangId) {
            $db->transRollback();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan transaksi hutang.');
        }

        foreach ($rincian as &$baris) {
            $baris['utang_id'] = $utangId;
        }
        unset($baris);

        $this->rincianModel->insertBatch($rincian);

        if ($db->transStatus() === false) {
            $db->transRollback();
            return redirect()->back()->withInput()
                ->with('error', 'Transaksi gagal disimpan. Silakan coba lagi.');
        }

        $db->transCommit();

        return redirect()->to('/utang/detail/' . $utangId)
            ->with('success', 'Hutang berhasil dicatat.');
    }

    public function detail($id)
    {
        $db = db_connect();

        $utang = $db->table('utang u')
            ->select("u.*, p.nama AS nama_pelanggan, p.no_hp, p.alamat,
                (SELECT COALESCE(SUM(r.total_harga), 0) FROM rincian_utang r WHERE r.utang_id = u.id) AS total_hutang,
                (SELECT COALESCE(SUM(b.jumlah_bayar), 0) FROM pembayaran b WHERE b.utang_id = u.id) AS total_bayar")
            ->join('pelanggan p', 'p.id = u.pelanggan_id')
            ->where('u.id', $id)
            ->get()->getRowArray();

        if (!$utang) {
            return redirect()->to('/utang')
                ->with('error', 'Transaksi hutang tidak ditemukan.');
        }

        $utang['sisa_hutang'] = max(0, (int) $utang['total_hutang'] - (int) $utang['total_bayar']);

        return view('utang/detail', [
            'judul' => 'Detail Hutang',
            'utang' => $utang,
            'rincian' => $this->rincianModel->where('utang_id', $id)->findAll(),
            'pembayaran' => db_connect()->table('pembayaran')
                ->where('utang_id', $id)
                ->orderBy('tanggal_bayar', 'DESC')
                ->get()->getResultArray(),
        ]);
    }
}
