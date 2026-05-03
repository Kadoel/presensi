<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePresensiTable extends Migration
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
            'jadwal_kerja_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'shift_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'jam_datang' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'jam_pulang' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status_datang' => [
                'type'       => 'ENUM',
                'constraint' => ['tepat_waktu', 'telat'],
                'null'       => true,
            ],
            'status_pulang' => [
                'type'       => 'ENUM',
                'constraint' => ['tepat_waktu', 'pulang_cepat'],
                'null'       => true,
            ],
            'menit_telat' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'menit_pulang_cepat' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'hasil_presensi' => [
                'type'       => 'ENUM',
                'constraint' => ['hadir', 'alpa', 'izin', 'sakit', 'libur', 'cuti'],
                'null'       => true,
            ],
            'selfie_datang' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'selfie_pulang' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'barcode_datang' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'barcode_pulang' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'catatan_admin' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_manual' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'sumber_presensi' => [
                'type'    => "ENUM('scan','sinkron','lupa_presensi')",
                'default' => 'scan',
                'null'    => false,
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
        $this->forge->addKey('jadwal_kerja_id');
        $this->forge->addKey('shift_id');

        $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('jadwal_kerja_id', 'jadwal_kerja', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('shift_id', 'shift', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('presensi', true);
    }

    public function down()
    {
        $this->forge->dropTable('presensi', true);
    }
}
