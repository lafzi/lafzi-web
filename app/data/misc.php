<?php

// skrip ini melengkapi data teks quran yang tadinya belum ada nama suratnya

$raw_data = file("quran-simple-wnum-edit.txt", FILE_IGNORE_NEW_LINES);
$surah_data = file("quran-surah.txt", FILE_IGNORE_NEW_LINES);

$surah = array();
foreach ($surah_data as $data) {
    
    list($id, $name, ) = explode("|", $data);
    $surah[$id] = $name;
    
}

$target_file = "quran_teks.txt";

$f = fopen($target_file, "w");

$id = 1;

foreach ($raw_data as $doc) {
    
    // split pada karakter "|"
    // [0] = nomor surat
    // [1] = nomor ayat
    // [2] = teks ayat
    $data = mb_split("\|", $doc);
    
    fwrite($f, $data[0]."|".$surah[$data[0]]."|".$data[1]."|".$data[2]."\n");

    echo $id . ". Diproses dokumen #$id\n";
    $id++;
    
}

fclose($f);