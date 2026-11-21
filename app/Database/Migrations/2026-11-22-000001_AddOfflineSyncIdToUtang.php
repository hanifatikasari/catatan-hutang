<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOfflineSyncIdToUtang extends Migration
{
    public function up()
    {
        $this->forge->addColumn('utang', [
            'offline_id' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
                'default' => null,
                'after' => 'nomor_transaksi',
            ],
        ]);

        $this->forge->addKey('offline_id', false, true);
        $this->forge->processIndexes('utang');
    }

    public function down()
    {
        $this->forge->dropColumn('utang', 'offline_id');
    }
}
