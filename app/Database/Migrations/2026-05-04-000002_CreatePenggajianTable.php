<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenggajianTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pegawai_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'jabatan_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'bulan' => [
                'type' => 'CHAR',
                'constraint' => 7,
            ],
            'gaji_pokok' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'tunjangan' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'gaji_kotor' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'total_hadir' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_izin' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_sakit' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_libur' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_cuti' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_alpa' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_menit_telat' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'total_menit_pulang_cepat' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'potongan_telat' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'potongan_pulang_cepat' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'potongan_alpa' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'total_potongan' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'gaji_bersih' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'final'],
                'default' => 'draft',
            ],
            'slip_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status',
            ],
            'created_by' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'generated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'finalized_by' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'finalized_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey(['pegawai_id', 'bulan']);
        $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('jabatan_id', 'jabatan', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('finalized_by', 'users', 'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('penggajian', true);
    }

    public function down()
    {
        $this->forge->dropTable('penggajian', true);
    }
}
