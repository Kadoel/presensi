<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\JadwalKerjaService;
use Hermawan\DataTables\DataTable;
use CodeIgniter\Database\RawSql;

class JadwalKerja extends BaseController
{
    protected JadwalKerjaService $jadwalKerjaService;

    public function __construct()
    {
        $this->jadwalKerjaService = new JadwalKerjaService();
    }

    public function index()
    {
        if (strtoupper($this->request->getMethod()) === 'GET') {
            $pegawai = $this->jadwalKerjaService->getPegawaiDropdown();
            $shift   = $this->jadwalKerjaService->getShiftDropdown();

            return view('pages/admin/jadwal/index', [
                'judul'   => 'Jadwal Kerja',
                'pegawai' => $pegawai['pegawai'] ?? [],
                'shift'   => $shift['shift'] ?? [],
            ]);
        }

        $builder = $this->jadwalKerjaService->dataTabel();

        return DataTable::of($builder)
            ->filter(function ($builder, $request) {
                $search = '';

                if (isset($request->search) && is_object($request->search) && property_exists($request->search, 'value')) {
                    $search = (string) $request->search->value;
                } elseif (isset($request->search) && is_array($request->search) && isset($request->search['value'])) {
                    $search = (string) $request->search['value'];
                } elseif (isset($_POST['search']['value'])) {
                    $search = (string) $_POST['search']['value'];
                }

                $search = trim(strtolower($search));
                $search = preg_replace('/\s+/', ' ', $search);

                if ($search === '') {
                    return;
                }

                $namaBulan = [
                    'januari' => 1,
                    'februari' => 2,
                    'maret' => 3,
                    'april' => 4,
                    'mei' => 5,
                    'juni' => 6,
                    'juli' => 7,
                    'agustus' => 8,
                    'september' => 9,
                    'oktober' => 10,
                    'november' => 11,
                    'desember' => 12,
                ];

                $builder->groupStart()
                    ->like('pegawai.nama_pegawai', $search)
                    ->orLike('pegawai.kode_pegawai', $search)
                    ->orLike('shift.nama_shift', $search)
                    ->orLike('jadwal_kerja.status_hari', $search)
                    ->orLike('jadwal_kerja.sumber_data', $search)
                    ->orLike('jadwal_kerja.catatan', $search);

                // 1. Search angka murni: 23 / 4 / 2026
                if (ctype_digit($search)) {
                    $angka = (int) $search;

                    $builder->orWhere(new \CodeIgniter\Database\RawSql('DAY(jadwal_kerja.tanggal) = ' . $angka))
                        ->orWhere(new \CodeIgniter\Database\RawSql('MONTH(jadwal_kerja.tanggal) = ' . $angka))
                        ->orWhere(new \CodeIgniter\Database\RawSql('YEAR(jadwal_kerja.tanggal) = ' . $angka));
                }

                // 2. Search bulan / prefix bulan: april / apr
                foreach ($namaBulan as $nama => $nomor) {
                    if (str_starts_with($nama, $search)) {
                        $builder->orWhere(new \CodeIgniter\Database\RawSql('MONTH(jadwal_kerja.tanggal) = ' . $nomor));
                    }
                }

                // 3. Search format: "23 april" / "23 apr"
                if (preg_match('/^(\d{1,2})\s+([a-z]+)/', $search, $match)) {
                    $hari = (int) $match[1];
                    $bulanText = $match[2];

                    foreach ($namaBulan as $nama => $nomor) {
                        if (str_starts_with($nama, $bulanText)) {
                            $builder->orWhere(new \CodeIgniter\Database\RawSql(
                                'DAY(jadwal_kerja.tanggal) = ' . $hari . ' AND MONTH(jadwal_kerja.tanggal) = ' . $nomor
                            ));
                        }
                    }
                }

                // 4. Search format: "april 2026" / "apr 2026"
                if (preg_match('/^([a-z]+)\s+(\d{4})$/', $search, $match)) {
                    $bulanText = $match[1];
                    $tahun = (int) $match[2];

                    foreach ($namaBulan as $nama => $nomor) {
                        if (str_starts_with($nama, $bulanText)) {
                            $builder->orWhere(new \CodeIgniter\Database\RawSql(
                                'MONTH(jadwal_kerja.tanggal) = ' . $nomor . ' AND YEAR(jadwal_kerja.tanggal) = ' . $tahun
                            ));
                        }
                    }
                }

                // 5. Search format lengkap: "23 april 2026" / "23 apr 2026"
                if (preg_match('/^(\d{1,2})\s+([a-z]+)\s+(\d{4})$/', $search, $match)) {
                    $hari = (int) $match[1];
                    $bulanText = $match[2];
                    $tahun = (int) $match[3];

                    foreach ($namaBulan as $nama => $nomor) {
                        if (str_starts_with($nama, $bulanText)) {
                            $builder->orWhere(new \CodeIgniter\Database\RawSql(
                                'DAY(jadwal_kerja.tanggal) = ' . $hari .
                                    ' AND MONTH(jadwal_kerja.tanggal) = ' . $nomor .
                                    ' AND YEAR(jadwal_kerja.tanggal) = ' . $tahun
                            ));
                        }
                    }
                }

                $builder->groupEnd();
            }, false)
            ->postQuery(function ($builder) {
                $builder->orderBy('YEAR(jadwal_kerja.tanggal)', 'ASC', false);
                $builder->orderBy('MONTH(jadwal_kerja.tanggal)', 'ASC', false);
                $builder->orderBy('pegawai.nama_pegawai', 'ASC');
                $builder->orderBy('jadwal_kerja.tanggal', 'ASC');
            })
            ->edit('pegawai_label', function ($row) {
                return $row->pegawai_label ?? '-';
            })
            ->edit('catatan', function ($row) {
                return $row->catatan ?? '-';
            })
            ->edit('tanggal', function ($row) {
                return tanggal_indonesia($row->tanggal ?? '-');
            })
            ->edit('bulan_jadwal', function ($row) {
                return tanggal_indonesia(($row->bulan_jadwal ?? '-'), 'bulan-tahun');
            })
            ->edit('nama_shift', function ($row) {
                if (($row->status_hari ?? '') !== 'kerja') {
                    return '-';
                }

                return $row->nama_shift ?? '-';
            })
            ->edit('status_hari', function ($row) {
                return match ($row->status_hari) {
                    'kerja' => '<span class="badge bg-success">Kerja</span>',
                    'libur' => '<span class="badge bg-danger">Libur</span>',
                    'izin'  => '<span class="badge bg-warning text-dark">Izin</span>',
                    'sakit' => '<span class="badge bg-info text-dark">Sakit</span>',
                    default => '<span class="badge bg-secondary">-</span>',
                };
            })
            ->edit('sumber_data', function ($row) {
                return match ($row->sumber_data ?? 'manual') {
                    'manual' => '<span class="badge bg-primary"><i class="fa fa-user"></i> Manual</span>',
                    'pengajuan_izin' => '<span class="badge bg-dark"><i class="fa fa-file-circle-check"></i> Pengajuan Izin</span>',
                    'hari_libur' => '<span class="badge bg-danger"><i class="fa fa-calendar-xmark"></i> Libur Global</span>',
                    default => '<span class="badge bg-secondary">-</span>',
                };
            })
            ->add('action', function ($row) {
                if (($row->sumber_data ?? 'manual') !== 'manual') {
                    return '
                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                        <i class="fa fa-lock"></i>
                    </button>
                ';
                }

                return '
                <button type="button" class="btn btn-sm btn-warning" id="act-edit" data-id="' . $row->id . '">
                    <i class="fa fa-edit text-white"></i>
                </button>
            ';
            })
            ->addNumbering('#')
            ->toJson(true);
    }

    public function simpan()
    {
        $result = $this->jadwalKerjaService->simpan($this->request->getPost());

        return $this->response->setJSON($result);
    }

    public function edit()
    {
        $id = (int) $this->request->getVar('id');
        $result = $this->jadwalKerjaService->ambil($id);

        return $this->response->setJSON($result);
    }

    public function update($id)
    {
        $result = $this->jadwalKerjaService->ubah((int) $id, $this->request->getPost());

        return $this->response->setJSON($result);
    }

    public function copy()
    {
        $result = $this->jadwalKerjaService->copyJadwalPegawai($this->request->getPost());

        return $this->response->setJSON($result);
    }

    public function individu()
    {
        $result = $this->jadwalKerjaService->simpanIndividu($this->request->getPost());

        return $this->response->setJSON($result);
    }
}
