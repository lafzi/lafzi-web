<?php

error_reporting(E_ALL & ~E_NOTICE);

include '../lib/db.php';
$db = new mysqlDB();
$db->connect();

// sesuaikan encoding
$db->query("set character_set_server='utf8'");
$db->query("set names 'utf8'");

// baca file, satu baris disimpan dalam satu array
$docs = file('quran-simple-wnum-edit.txt');

$count = 0;

foreach ($docs as $doc) {
    
    // split pada karakter "|"
    // [0] = nomor surat
    // [1] = nomor ayat
    // [2] = teks ayat
    $data = mb_split("\|", $doc);
    
    $query = "INSERT INTO doc (id, surat, ayat, teks) VALUES (NULL, '{$data[0]}', '{$data[1]}', '{$data[2]}')";

    $db->query($query);

    echo "Inserted surah {$data[0]} ayat {$data[1]}\n";
    $count++;
    
}

echo 'Total : ' . $count;
echo "\n\n";