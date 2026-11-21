<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $pelangganAktif = $db->table('pelanggan')->where('aktif', 1)->countAllResults();

        $transaksi = $db->query("SELECT u.id, u.tanggal_jatuh_tempo,
            COALESCE((SELECT SUM(r.total_harga) FROM rincian_utang r WHERE r.utang_id = u.id), 0) AS total_hutang,
            COALESCE((SELECT SUM(p.jumlah_bayar) FROM pembayaran p WHERE p.utang_id = u.id), 0) AS total_bayar
            FROM utang u WHERE u.dibatalkan = 0")->getResultArray();

        $totalPiutang = 0;
        $transaksiBelumLunas = 0;
        $jatuhTempo = 0;
        $hariIni = date('Y-m-d');

        foreach ($transaksi as $t) {
            $sisa = max(0, (int) $t['total_hutang'] - (int) $t['total_bayar']);
            if ($sisa > 0) {
                $totalPiutang += $sisa;
                $transaksiBelumLunas++;
                if (!empty($t['tanggal_jatuh_tempo']) && $t['tanggal_jatuh_tempo'] < $hariIni) {
                    $jatuhTempo++;
                }
            }
        }

        return view('home', [
            'judul' => 'Catatan Hutang',
            'pelangganAktif' => $pelangganAktif,
            'totalPiutang' => $totalPiutang,
            'transaksiBelumLunas' => $transaksiBelumLunas,
            'jatuhTempo' => $jatuhTempo,
        ]);
    }
}
