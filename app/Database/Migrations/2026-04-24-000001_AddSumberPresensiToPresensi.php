<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSumberPresensiToPresensi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('presensi', [
            'sumber_presensi' => [
                'type'    => "ENUM('scan','sinkron','lupa_presensi')",
                'default' => 'scan',
                'null'    => false,
                'after'   => 'is_manual',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('presensi', 'sumber_presensi');
    }
}
