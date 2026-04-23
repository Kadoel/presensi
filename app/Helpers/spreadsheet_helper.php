<?php

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (!function_exists('spreadsheet_style')) {
    function spreadsheet_style($parameter)
    {
        if ($parameter == 'kolom') {
            return [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_DOUBLE],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCDCDC']
                ],
            ];
        }
        if ($parameter == 'baris') {
            return [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_DOTTED],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_DOTTED],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ]
            ];
        }

        if ($parameter == 'total') {
            return [
                'font' => [
                    'bold' => true,
                    'size' => 11
                ],
                'alignment' => [
                    'indent' => 1,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCDCDC']
                ],
            ];
        }

        if ($parameter == 'subJudul') {
            return [
                'font' => [
                    'bold' => true,
                    'size' => 11
                ],
                'alignment' => [
                    'indent' => 1,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCDCDC']
                ],
            ];
        }

        if ($parameter == 'keterangan') {
            return [
                'font' => [
                    'italic' => true,
                    'color' => ['argb' => Color::COLOR_RED],
                    'size'  => 8
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1
                ],
            ];
        }

        if ($parameter == 'judul_keterangan') {
            return [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_RED],
                    'size'  => 9
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
        }

        if ($parameter == 'baris_null') {
            return [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_DOTTED],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_DOTTED],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF6666']
                ],
            ];
        }
        if ($parameter == 'header_saldo_terakhir') {
            return [
                'font' => [
                    'bold' => true,
                    'size' => 18
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_DOUBLE],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCDCDC']
                ],
            ];
        }
        if ($parameter == 'value_saldo_terakhir') {
            return [
                'font' => [
                    'bold' => true,
                    'size' => 18
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_DOTTED],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ]
            ];
        }
    }
}

if (!function_exists('template_style')) {
    function template_style($parameter)
    {
        if ($parameter == 'judul') {
            return [
                'font' => [
                    'bold'  => true,
                    'size'  => 16
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ];
        }
        if ($parameter == 'header') {
            return [
                'font' => [
                    'bold'  => true,
                    'size'  => 11
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCDCDC']
                ],
            ];
        }
        if ($parameter == 'sub_header') {
            return [
                'font'  => [
                    'size'  => 9,
                    'color' => ['argb' => Color::COLOR_RED],
                    'italic' => true,
                ],
                'fill'  => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCDCDC']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'top' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'right' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'bottom' => ['borderStyle'  => Border::BORDER_DOUBLE],
                    'left' => ['borderStyle'  => Border::BORDER_MEDIUM],
                    'vertical' => ['borderStyle'  => Border::BORDER_THIN]
                ],
            ];
        }
    }
}
