<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerubahanJadwalTable extends Migration
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
            'jadwal_kerja_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'pegawai_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'shift_lama_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'shift_baru_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'alasan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'diubah_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('jadwal_kerja_id');
        $this->forge->addKey('pegawai_id');
        $this->forge->addKey('shift_lama_id');
        $this->forge->addKey('shift_baru_id');
        $this->forge->addKey('diubah_oleh');

        $this->forge->addForeignKey('jadwal_kerja_id', 'jadwal_kerja', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('shift_lama_id', 'shift', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('shift_baru_id', 'shift', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('diubah_oleh', 'users', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('perubahan_jadwal', true);
    }

    public function down()
    {
        $this->forge->dropTable('perubahan_jadwal', true);
    }
}
