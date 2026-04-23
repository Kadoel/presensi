<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTukarJadwalTable extends Migration
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

            // slot A
            'jadwal_kerja_a_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'pegawai_a_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'tanggal_a' => [
                'type' => 'DATE',
            ],
            'shift_a_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'status_hari_a' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'sumber_data_a' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            // slot B
            'jadwal_kerja_b_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'pegawai_b_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'tanggal_b' => [
                'type' => 'DATE',
            ],
            'shift_b_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'status_hari_b' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'sumber_data_b' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            // tipe proses validasi swap
            'tipe_swap' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'simple / paired',
            ],

            // pengajuan
            'alasan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
                'comment'    => 'pending / approved / rejected / cancelled',
            ],
            'tipe_pengajuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'pegawai / admin',
            ],

            // approval
            'catatan_approval' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'diajukan_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'disetujui_oleh' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'disetujui_at' => [
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

        $this->forge->addKey('jadwal_kerja_a_id');
        $this->forge->addKey('pegawai_a_id');
        $this->forge->addKey('shift_a_id');

        $this->forge->addKey('jadwal_kerja_b_id');
        $this->forge->addKey('pegawai_b_id');
        $this->forge->addKey('shift_b_id');

        $this->forge->addKey('status');
        $this->forge->addKey('tipe_pengajuan');
        $this->forge->addKey('diajukan_oleh');
        $this->forge->addKey('disetujui_oleh');

        $this->forge->addForeignKey('jadwal_kerja_a_id', 'jadwal_kerja', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('pegawai_a_id', 'pegawai', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('shift_a_id', 'shift', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->addForeignKey('jadwal_kerja_b_id', 'jadwal_kerja', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('pegawai_b_id', 'pegawai', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('shift_b_id', 'shift', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->addForeignKey('diajukan_oleh', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('disetujui_oleh', 'users', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('tukar_jadwal', true);
    }

    public function down()
    {
        $this->forge->dropTable('tukar_jadwal', true);
    }
}
