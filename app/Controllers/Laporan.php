<?php

namespace App\Controllers;

class Laporan extends BaseController
{
    public function index()
    {
        $mulaiRaw = $this->request->getGet('mulai');
        $sampaiRaw = $this->request->getGet('sampai');
        $mulai = is_string($mulaiRaw) && $mulaiRaw !== '' ? $mulaiRaw : date('Y-m-01');
        $sampai = is_string($sampaiRaw) && $sampaiRaw !== '' ? $sampaiRaw : date('Y-m-d');

        if (!$this->validTanggal($mulai) || !$this->validTanggal($sampai) || $mulai > $sampai) {
            return redirect()->to('/laporan')->with('error', 'Rentang tanggal laporan tidak valid.');
        }

        $db = db_connect();
        $utang = $db->query("SELECT u.id, u.nomor_transaksi, u.tanggal_utang, u.tanggal_jatuh_tempo,
            p.nama AS nama_pelanggan,
            COALESCE((SELECT SUM(r.total_harga) FROM rincian_utang r WHERE r.utang_id = u.id), 0) AS total_hutang,
            COALESCE((SELECT SUM(b.jumlah_bayar) FROM pembayaran b WHERE b.utang_id = u.id), 0) AS total_bayar
            FROM utang u JOIN pelanggan p ON p.id = u.pelanggan_id
            WHERE u.dibatalkan = 0 AND DATE(u.tanggal_utang) BETWEEN ? AND ?
            ORDER BY u.tanggal_utang DESC", [$mulai, $sampai])->getResultArray();

        $totalHutang = 0;
        $totalPembayaranUntukUtang = 0;
        $sisaPiutang = 0;
        foreach ($utang as &$row) {
            $row['sisa_hutang'] = max(0, (int) $row['total_hutang'] - (int) $row['total_bayar']);
            $totalHutang += (int) $row['total_hutang'];
            $totalPembayaranUntukUtang += (int) $row['total_bayar'];
            $sisaPiutang += (int) $row['sisa_hutang'];
        }
        unset($row);

        $pembayaran = $db->query("SELECT COALESCE(SUM(jumlah_bayar), 0) AS total
            FROM pembayaran WHERE DATE(tanggal_bayar) BETWEEN ? AND ?", [$mulai, $sampai])->getRowArray();

        return view('laporan/index', [
            'judul' => 'Laporan Hutang Toko Ibu Zulaikhah',
            'mulai' => $mulai,
            'sampai' => $sampai,
            'utang' => $utang,
            'totalHutang' => $totalHutang,
            'totalPembayaranUntukUtang' => $totalPembayaranUntukUtang,
            'sisaPiutang' => $sisaPiutang,
            'pembayaranPeriode' => (int) ($pembayaran['total'] ?? 0),
        ]);
    }

    private function validTanggal(string $tanggal): bool
    {
        $date = \DateTime::createFromFormat('!Y-m-d', $tanggal);
        return $date && $date->format('Y-m-d') === $tanggal;
    }
}
