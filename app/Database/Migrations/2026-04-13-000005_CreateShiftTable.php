<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShiftTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_shift' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nama_shift' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'jam_masuk' => [
                'type' => 'TIME',
            ],
            'batas_mulai_datang' => [
                'type' => 'TIME',
            ],
            'batas_akhir_datang' => [
                'type' => 'TIME',
            ],
            'jam_pulang' => [
                'type' => 'TIME',
            ],
            'batas_mulai_pulang' => [
                'type' => 'TIME',
            ],
            'batas_akhir_pulang' => [
                'type' => 'TIME',
            ],
            'toleransi_telat_menit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_shift');
        $this->forge->createTable('shift', true);
    }

    public function down()
    {
        $this->forge->dropTable('shift', true);
    }
}
