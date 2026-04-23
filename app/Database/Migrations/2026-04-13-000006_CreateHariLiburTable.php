<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHariLiburTable extends Migration
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'nama_libur' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey('tanggal');
        $this->forge->createTable('hari_libur', true);
    }

    public function down()
    {
        $this->forge->dropTable('hari_libur', true);
    }
}
