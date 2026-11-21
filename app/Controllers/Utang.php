<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use App\Models\UtangModel;
use App\Models\RincianUtangModel;
use App\Models\PembayaranModel;

class Utang extends BaseController
{
    protected $pelangganModel;
    protected $utangModel;
    protected $rincianModel;
    protected $pembayaranModel;

    public function __construct()
    {
        $this->pelangganModel = new PelangganModel();
        $this->utangModel = new UtangModel();
        $this->rincianModel = new RincianUtangModel();
        $this->pembayaranModel = new PembayaranModel();
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

        $timestampUtang = $tanggal !== '' ? strtotime($tanggal) : false;
        if ($timestampUtang === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Tanggal hutang tidak valid.');
        }

        if ($jatuhTempo !== '') {
            $dateTempo = \DateTime::createFromFormat('!Y-m-d', $jatuhTempo);
            if (!$dateTempo || $dateTempo->format('Y-m-d') !== $jatuhTempo) {
                return redirect()->back()->withInput()
                    ->with('error', 'Tanggal jatuh tempo tidak valid.');
            }
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
            'tanggal_utang' => date('Y-m-d H:i:s', $timestampUtang),
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


    public function edit($id)
    {
        $db = db_connect();
        $utang = $db->table('utang u')
            ->select("u.*, p.nama AS nama_pelanggan,
                (SELECT COALESCE(SUM(b.jumlah_bayar),0) FROM pembayaran b WHERE b.utang_id=u.id) AS total_bayar")
            ->join('pelanggan p','p.id=u.pelanggan_id')
            ->where('u.id',$id)->get()->getRowArray();

        if (!$utang || (int) $utang['dibatalkan'] === 1) {
            return redirect()->to('/utang')->with('error','Transaksi tidak ditemukan atau sudah dibatalkan.');
        }

        $utang['rincian'] = $this->rincianModel->where('utang_id',$id)->findAll();
        return view('utang/edit', [
            'judul' => 'Koreksi Catatan Hutang',
            'utang' => $utang,
        ]);
    }

    public function perbarui($id)
    {
        $tanggal = trim((string) $this->request->getPost('tanggal_utang'));
        $tempo = trim((string) ($this->request->getPost('tanggal_jatuh_tempo') ?? ''));
        $catatan = trim((string) ($this->request->getPost('catatan') ?? ''));
        $timestamp = $tanggal !== '' ? strtotime($tanggal) : false;
        if ($timestamp === false) {
            return redirect()->back()->withInput()->with('error','Tanggal hutang tidak valid.');
        }
        if ($tempo !== '') {
            $parsed = \DateTime::createFromFormat('!Y-m-d',$tempo);
            if (!$parsed || $parsed->format('Y-m-d') !== $tempo) {
                return redirect()->back()->withInput()->with('error','Tanggal jatuh tempo tidak valid.');
            }
        }

        $names = $this->request->getPost('nama_barang');
        $qtys = $this->request->getPost('jumlah');
        $units = $this->request->getPost('satuan');
        $prices = $this->request->getPost('harga_satuan');
        if (!is_array($names) || !is_array($qtys) || !is_array($units) || !is_array($prices)
            || count($names) !== count($qtys) || count($names) !== count($units) || count($names) !== count($prices)) {
            return redirect()->back()->withInput()->with('error','Rincian barang tidak valid.');
        }

        $items=[]; $total=0;
        foreach ($names as $i=>$rawName) {
            $name=trim((string)$rawName); $qtyRaw=str_replace(',', '.', trim((string)($qtys[$i]??'')));
            $unit=trim((string)($units[$i]??'')); $priceRaw=trim((string)($prices[$i]??''));
            if ($name==='' && $qtyRaw==='' && $unit==='' && $priceRaw==='') continue;
            if ($name==='' || $unit==='' || !is_numeric($qtyRaw) || (float)$qtyRaw<=0 || !ctype_digit($priceRaw)) {
                return redirect()->back()->withInput()->with('error','Periksa nama barang, jumlah, satuan, dan harga pada baris '.($i+1).'.');
            }
            $qty=(float)$qtyRaw; $price=(int)$priceRaw; $subtotal=(int)round($qty*$price);
            if ($subtotal<0 || $total>PHP_INT_MAX-$subtotal) {
                return redirect()->back()->withInput()->with('error','Total hutang terlalu besar.');
            }
            $total+=$subtotal;
            $items[]=['utang_id'=>(int)$id,'nama_barang'=>$name,'jumlah'=>$qty,'satuan'=>$unit,'harga_satuan'=>$price,'total_harga'=>$subtotal];
        }
        if (!$items || $total<=0) {
            return redirect()->back()->withInput()->with('error','Masukkan minimal satu barang dengan total lebih dari Rp0.');
        }

        $db=db_connect(); $db->transBegin();
        $utang=$db->query('SELECT * FROM utang WHERE id=? FOR UPDATE',[$id])->getRowArray();
        if (!$utang || (int)$utang['dibatalkan']===1) {
            $db->transRollback();
            return redirect()->to('/utang')->with('error','Transaksi tidak ditemukan atau sudah dibatalkan.');
        }
        $paidRow=$db->table('pembayaran')->selectSum('jumlah_bayar')->where('utang_id',$id)->get()->getRowArray();
        $paid=(int)($paidRow['jumlah_bayar']??0);
        if ($total<$paid) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error','Total hutang baru tidak boleh lebih kecil dari pembayaran yang sudah diterima (Rp'.number_format($paid,0,',','.').').');
        }

        $ok=$this->utangModel->update($id,[
            'tanggal_utang'=>date('Y-m-d H:i:s',$timestamp),
            'tanggal_jatuh_tempo'=>$tempo!==''?$tempo:null,
            'catatan'=>$catatan!==''?$catatan:null,
        ]);
        if (!$ok) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error','Gagal memperbarui data transaksi.');
        }
        $this->rincianModel->where('utang_id',$id)->delete();
        $this->rincianModel->insertBatch($items);
        if ($db->transStatus()===false) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error','Koreksi gagal disimpan.');
        }
        $db->transCommit();
        return redirect()->to('/utang/detail/'.$id)->with('success','Catatan hutang berhasil dikoreksi.');
    }

    public function bayar($id)
    {
        $jumlahRaw = trim((string) $this->request->getPost('jumlah_bayar'));
        $caraBayar = trim((string) $this->request->getPost('cara_bayar'));
        $catatan = trim((string) ($this->request->getPost('catatan') ?? ''));
        $tanggalRaw = trim((string) ($this->request->getPost('tanggal_bayar') ?? ''));

        if (!ctype_digit($jumlahRaw) || (int) $jumlahRaw <= 0) {
            return redirect()->back()->withInput()->with('error', 'Jumlah pembayaran harus lebih dari Rp0.');
        }
        if (!in_array($caraBayar, ['Tunai', 'Transfer', 'QRIS', 'Lainnya'], true)) {
            return redirect()->back()->withInput()->with('error', 'Pilih cara pembayaran yang valid.');
        }

        $timestampBayar = $tanggalRaw !== '' ? strtotime($tanggalRaw) : time();
        if ($timestampBayar === false) {
            return redirect()->back()->withInput()->with('error', 'Tanggal pembayaran tidak valid.');
        }
        $tanggalBayar = date('Y-m-d H:i:s', $timestampBayar);
        $db = db_connect();
        $db->transBegin();

        $utang = $db->query('SELECT * FROM utang WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        if (!$utang || (int) $utang['dibatalkan'] === 1) {
            $db->transRollback();
            return redirect()->to('/utang')->with('error', 'Transaksi tidak ditemukan atau sudah dibatalkan.');
        }

        $totalHutangRow = $db->table('rincian_utang')->selectSum('total_harga')->where('utang_id', $id)->get()->getRowArray();
        $totalBayarRow = $db->table('pembayaran')->selectSum('jumlah_bayar')->where('utang_id', $id)->get()->getRowArray();
        $totalHutang = (int) ($totalHutangRow['total_harga'] ?? 0);
        $totalBayar = (int) ($totalBayarRow['jumlah_bayar'] ?? 0);
        $sisa = max(0, $totalHutang - $totalBayar);
        $jumlahBayar = (int) $jumlahRaw;

        if ($jumlahBayar > $sisa) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Pembayaran melebihi sisa hutang. Sisa saat ini Rp' . number_format($sisa, 0, ',', '.') . '.');
        }

        $berhasil = $this->pembayaranModel->insert([
            'utang_id' => $id,
            'tanggal_bayar' => $tanggalBayar,
            'jumlah_bayar' => $jumlahBayar,
            'cara_bayar' => $caraBayar,
            'catatan' => $catatan !== '' ? $catatan : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$berhasil || $db->transStatus() === false) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Pembayaran gagal disimpan.');
        }

        $db->transCommit();
        return redirect()->to('/utang/detail/' . $id)->with('success', 'Pembayaran berhasil dicatat.');
    }


    public function batalkan($id)
    {
        $alasan = trim((string) $this->request->getPost('alasan_batal'));
        if ($alasan === '' || mb_strlen($alasan) > 255) {
            return redirect()->back()->withInput()
                ->with('error', 'Alasan pembatalan wajib diisi, maksimal 255 karakter.');
        }

        $db = db_connect();
        $db->transBegin();
        $utang = $db->query('SELECT * FROM utang WHERE id = ? FOR UPDATE', [$id])->getRowArray();

        if (!$utang || (int) $utang['dibatalkan'] === 1) {
            $db->transRollback();
            return redirect()->to('/utang')->with('error', 'Transaksi tidak ditemukan atau sudah dibatalkan.');
        }

        $pembayaran = $db->table('pembayaran')->where('utang_id', $id)->countAllResults();
        if ($pembayaran > 0) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Transaksi yang sudah memiliki pembayaran tidak dapat dibatalkan. Hubungi pengelola untuk koreksi.');
        }

        $ok = $this->utangModel->update($id, [
            'dibatalkan' => 1,
            'alasan_batal' => $alasan,
        ]);

        if (!$ok || $db->transStatus() === false) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Pembatalan gagal disimpan.');
        }

        $db->transCommit();
        return redirect()->to('/utang')->with('success', 'Transaksi hutang berhasil dibatalkan.');
    }

}
