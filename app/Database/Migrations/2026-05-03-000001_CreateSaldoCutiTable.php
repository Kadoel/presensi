<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaldoCutiTable extends Migration
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
            'pegawai_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'tahun' => [
                'type' => 'YEAR',
            ],
            'jatah' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 12,
            ],
            'terpakai' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'sisa' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 12,
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
        $this->forge->addKey('pegawai_id');
        $this->forge->addKey('tahun');
        $this->forge->addUniqueKey(['pegawai_id', 'tahun']);
        $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('saldo_cuti', true);
    }

    public function down()
    {
        $this->forge->dropTable('saldo_cuti', true);
    }
}
