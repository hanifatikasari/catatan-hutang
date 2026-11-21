<?php

namespace App\Models;

use CodeIgniter\Model;

class RincianUtangModel extends Model
{
    protected $table = 'rincian_utang';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'utang_id',
        'nama_barang',
        'jumlah',
        'satuan',
        'harga_satuan',
        'total_harga',
    ];

    protected $useTimestamps = false;
}
