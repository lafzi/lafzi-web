<?php

// membuat korpus, versi flat file

error_reporting(E_ALL & ~E_NOTICE);

include '../lib/fonetik.php';

$bervokal = true;

// baca file, satu baris disimpan dalam satu array
$docs = file('../data/quran_teks.txt');

$count = 0;
$id = 1;

if ($bervokal)
    $target_file = "../data/fonetik_vokal.txt";
else
    $target_file = "../data/fonetik.txt";

$f = fopen($target_file, "w");

foreach ($docs as $doc) {
    
    // split pada karakter "|"
    // [0] = nomor surat
    // [1] = nama surat
    // [2] = nomor ayat
    // [3] = teks ayat
    $data = mb_split("\|", $doc);
    
    $fonetik = ar_fonetik($data[3], !$bervokal);
    
    fwrite($f, $id."|".$fonetik."\n");

    echo $id . ". Diproses surah {$data[0]} ayat {$data[2]}\n";
    $count++;
    $id++;
    
}

fclose($f);

echo 'Total : ' . $count;
echo "\n\n";
