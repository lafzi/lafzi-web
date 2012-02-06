<?php

// membuat korpus, versi flat file

error_reporting(E_ALL & ~E_NOTICE);

include '../lib/fonetik.php';

// baca file, satu baris disimpan dalam satu array
$docs = file('../data/quran_teks.txt');

$count = 0;
$id = 1;

$target_file = "../data/fonetik_vokal.txt";
$bervokal = true;

$f = fopen($target_file, "w");

foreach ($docs as $doc) {
    
    // split pada karakter "|"
    // [0] = nomor surat
    // [1] = nomor ayat
    // [3] = teks ayat
    $data = mb_split("\|", $doc);
    
    $fonetik = ar_fonetik($data[3], !$bervokal);
    
    fwrite($f, $id."|".$fonetik."\n");

    echo $id . ". Diproses surah {$data[0]} ayat {$data[1]}\n";
    $count++;
    $id++;
    
}

fclose($f);

echo 'Total : ' . $count;
echo "\n\n";