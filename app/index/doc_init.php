<?php

error_reporting(E_ALL & ~E_NOTICE);

include '../lib/db.php';
include '../lib/fonetik.php';

$db = new mysqlDB();
$db->connect();

// sesuaikan encoding
$db->query("set character_set_server='utf8'");
$db->query("set names 'utf8'");

// baca file, satu baris disimpan dalam satu array
$docs = file('../data/quran_teks.txt');

$count = 0;
$id = 1;

foreach ($docs as $doc) {
    
    // split pada karakter "|"
    // [0] = nomor surat
    // [1] = nomor ayat
    // [3] = teks ayat
    $data = mb_split("\|", $doc);
    
    $fonetik = ar_fonetik($data[3]);
    $fonetik_berharakat = ar_fonetik($data[3], false);
    
    $query = "INSERT INTO doc (id, surat, ayat, teks, fonetik, fonetik_vokal) VALUES ('{$id}', '{$data[0]}', '{$data[1]}', '{$data[2]}', '{$fonetik}', '{$fonetik_berharakat}')";

    $db->query($query);

    echo $id . ". Inserted surah {$data[0]} ayat {$data[1]}\n";
    $count++;
    $id++;
    
}

echo 'Total : ' . $count;
echo "\n\n";