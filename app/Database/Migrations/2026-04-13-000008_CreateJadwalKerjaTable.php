<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJadwalKerjaTable extends Migration
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'shift_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status_hari' => [
                'type'       => 'ENUM',
                'constraint' => ['kerja', 'libur', 'izin', 'sakit', 'cuti'],
                'default'    => 'kerja',
            ],
            'sumber_data' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'pengajuan_izin', 'hari_libur'],
                'default'    => 'manual',
            ],
            'pengajuan_izin_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'hari_libur_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            // snapshot sebelum dioverride approval izin/sakit
            'shift_id_sebelumnya' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status_hari_sebelumnya' => [
                'type'       => 'ENUM',
                'constraint' => ['kerja', 'libur', 'izin', 'sakit', 'cuti'],
                'null'       => true,
            ],
            'catatan_sebelumnya' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sumber_data_sebelumnya' => [
                'type'       => 'ENUM',
                'constraint' => ['manual', 'pengajuan_izin', 'hari_libur'],
                'null'       => true,
            ],

            'catatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_by' => [
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
        $this->forge->addUniqueKey(['pegawai_id', 'tanggal']);
        $this->forge->addKey('shift_id');
        $this->forge->addKey('shift_id_sebelumnya');
        $this->forge->addKey('created_by');
        $this->forge->addKey('pengajuan_izin_id');
        $this->forge->addKey('hari_libur_id');

        $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('shift_id', 'shift', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('shift_id_sebelumnya', 'shift', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('pengajuan_izin_id', 'pengajuan_izin', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('hari_libur_id', 'hari_libur', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('jadwal_kerja', true);
    }

    public function down()
    {
        $this->forge->dropTable('jadwal_kerja', true);
    }
}
