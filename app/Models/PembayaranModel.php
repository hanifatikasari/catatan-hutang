<?php

namespace App\Models;

use CodeIgniter\Model;

class PembayaranModel extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'utang_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'cara_bayar',
        'catatan',
        'created_at',
    ];

    // Tabel pembayaran hanya memiliki created_at, bukan updated_at.
    protected $useTimestamps = false;
}
