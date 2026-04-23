<?php
if (!function_exists('register')) {
    function register($noKendaraan)
    {
        $register = "";
        if (!preg_match("/^[A-Z]{1,2}[1-9]{3,4}[A-Z]{1,2}$/", $noKendaraan ?? '')) {
            $register = "";
        } else {
            $a = array("01" => "A", "02" => "B", "03" => "C", "04" => "D", "05" => "E", "06" => "F", "07" => "G", "08" => "H", "09" => "I", "10" => "J", "11" => "K", "12" => "L", "13" => "M", "14" => "N", "15" => "O", "16" => "P", "17" => "Q", "18" => "R", "19" => "S", "20" => "T", "21" => "U", "22" => "V", "23" => "W", "24" => "X", "25" => "Y", "26" => "Z");
            $patern = "/[0-9]{1,4}/"; //Patern Memecah Huruf Berdasarkan Angka
            $hasil = preg_split($patern, $noKendaraan); // Mendapatkan Huruf Pada Nomor Kendaraan
            $angka = substr($noKendaraan, strlen($hasil[0]), (strlen($noKendaraan) - (strlen($hasil[0]) + strlen($hasil[1])))); // Mendapatkan Angka pada nomor kendaraan
            $char1 = str_split($hasil[0]); //Memecah Huruf yang Ada Di Depan Angka
            $char2 = str_split($hasil[1]); //Memecah Huruf yang Ada Di Belakang Angka
            for ($index = 0; $index < strlen($hasil[0]); $index++) {
                $register .= array_search($char1[$index], $a); //Mengganti Setiap Huruf ke Angka dan meenyimpan di variabel data
            }

            $register .= strlen($hasil[0]) == 1 ? "00" . $angka : $angka; // Jika Huruf Depan Angka Hanya Ada 1, Huruf Ke 2 Diganti Dengan 00

            for ($index = 0; $index < strlen($hasil[1]); $index++) {
                $register .= array_search($char2[$index], $a); //Mengganti Setiap Huruf ke Angka dan meenyimpan di variabel data
            }

            $register .= strlen($hasil[1]) == 1 ? "00" : ''; // Jika Huruf Dibelakang Angka Hanya Ada 1, Huruf Ke 2 Diganti Dengan 00
        }
        return $register;
    }
}
