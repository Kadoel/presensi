<?php

namespace App\Services\Admin;

use App\Models\JadwalKerjaModel;
use App\Models\PegawaiModel;
use App\Models\PengajuanIzinModel;
use App\Models\PengaturanGajiModel;
use App\Models\PenggajianModel;
use App\Models\PresensiModel;
use App\Services\BaseService;
use CodeIgniter\Database\BaseBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;
use ZipArchive;

class PenggajianService extends BaseService
{
    protected PenggajianModel $penggajianModel;
    protected PengaturanGajiModel $pengaturanGajiModel;
    protected PegawaiModel $pegawaiModel;
    protected PresensiModel $presensiModel;
    protected JadwalKerjaModel $jadwalKerjaModel;
    protected PengajuanIzinModel $pengajuanIzinModel;

    public function __construct()
    {
        parent::__construct();
        $this->penggajianModel     = new PenggajianModel();
        $this->pengaturanGajiModel = new PengaturanGajiModel();
        $this->pegawaiModel        = new PegawaiModel();
        $this->presensiModel       = new PresensiModel();
        $this->jadwalKerjaModel    = new JadwalKerjaModel();
        $this->pengajuanIzinModel  = new PengajuanIzinModel();
    }

    public function dataTabel(?string $bulan = null): BaseBuilder
    {
        return $this->penggajianModel->selectData($bulan ?: date('Y-m'));
    }

    public function ringkasan(string $bulan): array
    {
        return $this->eksekusi(function () use ($bulan) {
            $validasiBulan = $this->validasiBulan($bulan);
            if (! $validasiBulan['sukses']) {
                return $validasiBulan;
            }

            return $this->hasilData([
                'ringkasan' => $this->penggajianModel->getRingkasanByBulan($bulan),
                'ada_draft' => $this->penggajianModel->adaDraftByBulan($bulan),
                'ada_final' => $this->penggajianModel->adaFinalByBulan($bulan),
            ]);
        });
    }

    public function detail(int $id): array
    {
        return $this->eksekusi(function () use ($id) {
            $data = $this->penggajianModel->getDetailById($id);
            if ($data === null) {
                return $this->hasilTidakDitemukan('Data penggajian tidak ditemukan');
            }

            return $this->hasilData(['penggajian' => $data]);
        });
    }

    public function generate(string $bulan): array
    {
        return $this->transaksi(function () use ($bulan) {
            $validasiBulan = $this->validasiBulan($bulan);
            if (! $validasiBulan['sukses']) {
                return $validasiBulan;
            }

            $validasiGenerate = $this->validasiBolehGenerate($bulan);
            if (! $validasiGenerate['sukses']) {
                return $validasiGenerate;
            }

            $pegawaiAktif = $this->pegawaiModel->getPegawaiAktifUntukPenggajian();
            if (empty($pegawaiAktif)) {
                return $this->hasilGagal([], 'Tidak ada pegawai aktif untuk digenerate');
            }

            if (! $this->penggajianModel->deleteDraftByBulan($bulan)) {
                return $this->hasilGagal([], 'Draft penggajian bulan ini gagal dibersihkan');
            }

            $rows = [];
            $pegawaiGagal = [];

            foreach ($pegawaiAktif as $pegawai) {
                $pegawaiId = (int) ($pegawai->id ?? 0);
                $jabatanId = (int) ($pegawai->jabatan_id ?? 0);

                if ($pegawaiId <= 0) {
                    continue;
                }

                if ($jabatanId <= 0) {
                    $pegawaiGagal[] = ($pegawai->nama_pegawai ?? 'Pegawai') . ' belum memiliki jabatan';
                    continue;
                }

                $pengaturanGaji = $this->pengaturanGajiModel->getAktifByJabatan($jabatanId);
                if ($pengaturanGaji === null) {
                    $pegawaiGagal[] = ($pegawai->nama_pegawai ?? 'Pegawai');
                    continue;
                }

                $rekap = $this->presensiModel->getRekapPenggajianPegawai($pegawaiId, $bulan);

                $gajiPokok = (float) ($pengaturanGaji->gaji_pokok ?? 0);
                $tunjangan = (float) ($pengaturanGaji->tunjangan ?? 0);
                $totalMenitTelat = (int) ($rekap->total_menit_telat ?? 0);
                $totalMenitPulangCepat = (int) ($rekap->total_menit_pulang_cepat ?? 0);
                $totalAlpa = (int) ($rekap->total_alpa ?? 0);

                $potonganTelat = $totalMenitTelat * (float) ($pengaturanGaji->potongan_telat_per_menit ?? 0);
                $potonganPulangCepat = $totalMenitPulangCepat * (float) ($pengaturanGaji->potongan_pulang_cepat_per_menit ?? 0);
                $potonganAlpa = $totalAlpa * (float) ($pengaturanGaji->potongan_alpa_per_hari ?? 0);
                $gajiKotor = $gajiPokok + $tunjangan;
                $totalPotongan = $potonganTelat + $potonganPulangCepat + $potonganAlpa;

                $rows[] = [
                    'pegawai_id'               => $pegawaiId,
                    'jabatan_id'               => $jabatanId,
                    'bulan'                    => $bulan,
                    'gaji_pokok'               => $gajiPokok,
                    'tunjangan'                => $tunjangan,
                    'gaji_kotor'               => $gajiKotor,
                    'total_hadir'              => (int) ($rekap->total_hadir ?? 0),
                    'total_izin'               => (int) ($rekap->total_izin ?? 0),
                    'total_sakit'              => (int) ($rekap->total_sakit ?? 0),
                    'total_libur'              => (int) ($rekap->total_libur ?? 0),
                    'total_cuti'               => (int) ($rekap->total_cuti ?? 0),
                    'total_alpa'               => $totalAlpa,
                    'total_menit_telat'        => $totalMenitTelat,
                    'total_menit_pulang_cepat' => $totalMenitPulangCepat,
                    'potongan_telat'           => $potonganTelat,
                    'potongan_pulang_cepat'    => $potonganPulangCepat,
                    'potongan_alpa'            => $potonganAlpa,
                    'total_potongan'           => $totalPotongan,
                    'gaji_bersih'              => max(0, $gajiKotor - $totalPotongan),
                    'status'                   => 'draft',
                    'created_by'               => $this->intAtauNull(session()->get('user_id')),
                    'generated_at'             => date('Y-m-d H:i:s'),
                ];
            }

            if (! empty($pegawaiGagal)) {
                return $this->hasilGagal([], 'Generate ditolak: ' . implode(', ', $pegawaiGagal) . ' belum memiliki pengaturan gaji aktif');
            }

            if (empty($rows)) {
                return $this->hasilGagal([], 'Tidak ada data penggajian yang dapat digenerate');
            }

            if (! $this->penggajianModel->insertBatchPenggajian($rows)) {
                return $this->hasilGagal([], 'Data penggajian gagal digenerate');
            }

            $this->catatAudit('generate', 'penggajian', null, 'Generate penggajian bulan ' . $bulan . ' sebanyak ' . count($rows) . ' pegawai');

            return $this->hasilSukses('Penggajian berhasil digenerate', ['bulan' => $bulan, 'jumlah' => count($rows)]);
        });
    }

    public function finalkan(string $bulan): array
    {
        return $this->transaksi(function () use ($bulan) {
            $validasiBulan = $this->validasiBulan($bulan);
            if (! $validasiBulan['sukses']) {
                return $validasiBulan;
            }

            if ($this->penggajianModel->adaFinalByBulan($bulan)) {
                return $this->hasilGagal([], 'Penggajian bulan ini sudah final');
            }

            $jumlahDraft = $this->penggajianModel->countDraftByBulan($bulan);
            if ($jumlahDraft < 1) {
                return $this->hasilGagal([], 'Tidak ada draft penggajian untuk difinalkan');
            }

            if (! $this->penggajianModel->finalkanByBulan($bulan, $this->intAtauNull(session()->get('user_id')))) {
                return $this->hasilGagal([], 'Penggajian gagal difinalkan');
            }

            $this->catatAudit('final', 'penggajian', null, 'Finalisasi penggajian bulan ' . $bulan . ' sebanyak ' . $jumlahDraft . ' pegawai');

            return $this->hasilSukses('Penggajian berhasil difinalkan');
        });
    }

    public function exportFinal(string $bulan)
    {
        $bulan = preg_match('/^\d{4}-\d{2}$/', $bulan) ? $bulan : date('Y-m');

        $rows = $this->penggajianModel->getExportFinalByBulan($bulan);

        if (empty($rows)) {
            return redirect()->back()->with('error', 'Data penggajian final belum tersedia untuk bulan ini');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penggajian ' . $bulan);

        $headers = [
            'No',
            'Kode Pegawai',
            'Nama Pegawai',
            'Jabatan',
            'Bulan',
            'Hadir',
            'Izin',
            'Sakit',
            'Libur',
            'Cuti',
            'Alpa',
            'Menit Telat',
            'Menit Pulang Cepat',
            'Gaji Pokok',
            'Tunjangan',
            'Gaji Kotor',
            'Potongan Telat',
            'Potongan Pulang Cepat',
            'Potongan Alpa',
            'Total Potongan',
            'Gaji Bersih',
            'Status',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $styleHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAF7'],
            ],
        ];

        $styleBorder = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        $styleIndent = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'indent'     => 1,
            ],
        ];

        $sheet->getStyle('A1:V1')->applyFromArray($styleHeader);

        $rowExcel = 2;
        $no = 1;

        $totalGajiKotor = 0;
        $totalPotongan = 0;
        $totalGajiBersih = 0;

        foreach ($rows as $row) {
            $totalGajiKotor += (float) $row->gaji_kotor;
            $totalPotongan += (float) $row->total_potongan;
            $totalGajiBersih += (float) $row->gaji_bersih;

            $sheet->fromArray([
                $no++,
                $row->kode_pegawai ?? '-',
                $row->nama_pegawai ?? '-',
                $row->nama_jabatan ?? '-',
                $this->formatBulanIndonesia($row->bulan ?? $bulan),
                (int) $row->total_hadir,
                (int) $row->total_izin,
                (int) $row->total_sakit,
                (int) $row->total_libur,
                (int) $row->total_cuti,
                (int) $row->total_alpa,
                (int) $row->total_menit_telat,
                (int) $row->total_menit_pulang_cepat,
                (float) $row->gaji_pokok,
                (float) $row->tunjangan,
                (float) $row->gaji_kotor,
                (float) $row->potongan_telat,
                (float) $row->potongan_pulang_cepat,
                (float) $row->potongan_alpa,
                (float) $row->total_potongan,
                (float) $row->gaji_bersih,
                strtoupper($row->status ?? 'final'),
            ], null, 'A' . $rowExcel);

            $sheet->getStyle('A' . $rowExcel . ':V' . $rowExcel)->applyFromArray($styleBorder);
            $sheet->getStyle('A' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowExcel . ':E' . $rowExcel)->applyFromArray($styleIndent);
            $sheet->getStyle('F' . $rowExcel . ':M' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('N' . $rowExcel . ':U' . $rowExcel)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('V' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowExcel++;
        }

        $sheet->setCellValue('A' . $rowExcel, 'TOTAL');
        $sheet->mergeCells('A' . $rowExcel . ':M' . $rowExcel);
        $sheet->setCellValue('P' . $rowExcel, $totalGajiKotor);
        $sheet->setCellValue('T' . $rowExcel, $totalPotongan);
        $sheet->setCellValue('U' . $rowExcel, $totalGajiBersih);

        $sheet->getStyle('A' . $rowExcel . ':V' . $rowExcel)->applyFromArray($styleHeader);
        $sheet->getStyle('N' . $rowExcel . ':U' . $rowExcel)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        $filename = 'export-penggajian-' . $bulan . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function previewSlip(int $id)
    {
        $slip = $this->penggajianModel->getSlipById($id);

        if ($slip === null || ($slip->status ?? '') !== 'final') {
            return redirect()->back()->with('error', 'Slip gaji hanya tersedia untuk penggajian final');
        }

        return view('pages/admin/penggajian/slip', [
            'slip' => $slip,
        ]);
    }

    public function exportSlipPdf(int $id)
    {
        $slip = $this->penggajianModel->getSlipById($id);

        if ($slip === null || ($slip->status ?? '') !== 'final') {
            return redirect()->back()->with('error', 'Slip gaji hanya tersedia untuk penggajian final');
        }

        $html = view('pages/admin/penggajian/slip', [
            'slip' => $slip,
            'isPdf' => true,
        ]);

        $filename = 'slip-gaji-' . $slip->kode_pegawai . '-' . $slip->bulan . '.pdf';

        return $this->renderPdf($html, $filename);
    }

    public function exportSlipBulk(string $bulan)
    {
        $bulan = preg_match('/^\d{4}-\d{2}$/', $bulan) ? $bulan : date('Y-m');

        $rows = $this->penggajianModel->getSlipFinalByBulan($bulan);

        if (empty($rows)) {
            return redirect()->back()->with('error', 'Data slip final belum tersedia');
        }

        $dir = WRITEPATH . 'uploads/slip-gaji-' . $bulan . '-' . date('YmdHis') . DIRECTORY_SEPARATOR;

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach ($rows as $slip) {
            $html = view('pages/admin/penggajian/slip', [
                'slip' => $slip,
                'isPdf' => true,
            ]);

            $pdfContent = $this->renderPdfContent($html);
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $slip->kode_pegawai . '-' . $slip->nama_pegawai);

            file_put_contents($dir . 'slip-gaji-' . $safeName . '-' . $bulan . '.pdf', $pdfContent);
        }

        $zipPath = WRITEPATH . 'uploads/slip-gaji-' . $bulan . '.zip';

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP');
        }

        foreach (glob($dir . '*.pdf') as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        return service('response')
            ->download($zipPath, null)
            ->setFileName('slip-gaji-' . $bulan . '.zip');
    }

    protected function renderPdf(string $html, string $filename)
    {
        $dompdf = $this->createDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    protected function renderPdfContent(string $html): string
    {
        $dompdf = $this->createDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function createDompdf(): Dompdf
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        return new Dompdf($options);
    }

    protected function formatBulanIndonesia(string $bulan): string
    {
        $bulanIndo = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        if (! preg_match('/^(\d{4})-(\d{2})$/', $bulan, $match)) {
            return $bulan;
        }

        return $bulanIndo[(int) $match[2]] . ' ' . $match[1];
    }

    protected function validasiBulan(string $bulan): array
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return $this->hasilGagal(['bulan' => 'Format bulan tidak valid'], 'Format bulan tidak valid');
        }

        if ($bulan > date('Y-m')) {
            return $this->hasilGagal(['bulan' => 'Bulan penggajian tidak boleh melebihi bulan berjalan'], 'Bulan penggajian tidak boleh melebihi bulan berjalan');
        }

        return $this->hasilSukses();
    }

    protected function validasiBolehGenerate(string $bulan): array
    {
        if ($this->penggajianModel->adaFinalByBulan($bulan)) {
            return $this->hasilGagal([], 'Penggajian bulan ini sudah final dan tidak dapat digenerate ulang');
        }

        [$tanggalMulai, $tanggalSelesai] = $this->periodeBulan($bulan);
        $tanggalBelumSinkron = $this->jadwalKerjaModel->getTanggalBelumSinkronByBulan($bulan);

        if (! empty($tanggalBelumSinkron)) {
            $listTanggal = array_map(fn($row) => tanggal_indonesia((string) $row->tanggal), $tanggalBelumSinkron);
            return $this->hasilGagal([], 'Generate ditolak. Masih ada tanggal kerja yang belum sinkron: ' . implode(', ', $listTanggal));
        }

        if ($this->pengajuanIzinModel->countPendingByPeriode($tanggalMulai, $tanggalSelesai) > 0) {
            return $this->hasilGagal([], 'Generate ditolak. Masih ada pengajuan izin/sakit/cuti pending pada bulan ini');
        }

        return $this->hasilSukses();
    }

    protected function periodeBulan(string $bulan): array
    {
        $tanggalMulai = $bulan . '-01';
        return [$tanggalMulai, date('Y-m-t', strtotime($tanggalMulai))];
    }
}
