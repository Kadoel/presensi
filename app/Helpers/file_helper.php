<?php
if (!function_exists('hapus_file')) {
    function hapus_file($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

if (!function_exists('hapus_files')) {
    function hapus_files($folder, $file)
    {
        for ($index = 0; $index < count($file); $index++) {
            if (file_exists($folder . $file[$index]->nama . '.png')) {
                unlink($folder . $file[$index]->nama . '.png');
            }
        }
    }
}

if (!function_exists('buat_folder')) {
    function buat_folder($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
