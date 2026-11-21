<?php

namespace App\Models;

use CodeIgniter\Model;

class UtangModel extends Model
{
    protected $table = 'utang';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'pelanggan_id',
        'nomor_transaksi',
        'tanggal_utang',
        'tanggal_jatuh_tempo',
        'catatan',
        'dibatalkan',
        'alasan_batal',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
