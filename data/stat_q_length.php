<?php

// mencari panjang rata-rata setiap kueri set

include '../eval/CSVDataSource.php';
include '../lib/fonetik_id.php';

$folder = $argv[1];

$csvq = new File_CSV_DataSource('../eval/' . $folder . '/queries.csv');
$qs = $csvq->getHeaders();

// baca semua kueri
$daftar_query = array();
$daftar_kodef_query_nv = array();
$daftar_kodef_query_v = array();

foreach ($qs as $q_id) {
    $variasi_query = $csvq->getColumn($q_id);
    $i = 1;
    foreach ($variasi_query as $query) {
        if ($query != '') {
            $daftar_query[$q_id][$i] = $query;
            $daftar_kodef_query_nv[$q_id][$i] = id_fonetik($query);
            $daftar_kodef_query_v[$q_id][$i] = id_fonetik($query, false);
            $i++;
        }
    }
}

echo "Avg V : \n";
foreach ($daftar_kodef_query_v as $qid => $values) {
    $sum   = array_sum(array_map(function($el){return strlen($el);}, $values));
    $count = count($values);
    $avg = $sum / $count;
    echo round($avg, 2);
    echo "\n";
}

echo "\nAvg NV : \n";
foreach ($daftar_kodef_query_nv as $qid => $values) {
    $sum   = array_sum(array_map(function($el){return strlen($el);}, $values));
    $count = count($values);
    $avg = $sum / $count;
    echo round($avg, 2);
    echo "\n";
}