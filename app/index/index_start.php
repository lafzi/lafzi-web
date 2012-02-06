<?php

// mengindeks dokumen

// profiling
$time_start = microtime(true);

include '../lib/db.php';
include '../lib/fonetik.php';
include '../lib/trigram.php';

$db = new mysqlDB();
$db->connect();

// parameter
$doc_field = "fonetik";
$index_table = "index";

// fase I : mengekstrak seluruh term dari seluruh dokumen
echo "Fase I \n\n"; 
 
// baca seluruh dokumen
$docs = $db->get_result("SELECT `id`, `{$doc_field}` AS `fonetik` FROM `doc`", "assoc");
$docs_count = count($docs);

// array besar penyimpan indeks
$index = array();

$limit = 8000;
$i = 1;

// untuk setiap dokumen
foreach ($docs as $doc) {
    
    $id = $doc['id'];
    $text = $doc['fonetik'];

    echo "Memproses dokumen $id : ";
        
    // ekstrak trigram
    $trigrams = trigram_frekuensi_posisi($text);
    
    foreach ($trigrams as $trigram => $fp) {
        
        // $fp[0] = frekuensi, $fp[1] = posisi trigram
        list($freq, $pos) = $fp;
        
        // masukkan entri ke array indeks
        $index[$trigram][] = array($id, $freq, $pos);
        
    }
    
    echo "OK\t";
    echo "(". round($id/$docs_count*100) ."%)";
    echo "\n";
    
    if ($i >= $limit) break;
    $i++;
    
}

// fase II : membangun inverted index
echo "\nFase II \n\n";

// kosongkan dulu tabel indeks
$db->query("TRUNCATE `{$index_table}`");

// urutkan key pada array indeks
ksort($index);

foreach ($index as $term => $postings) {
    
    $posting_list = array();
    $posting_list_string = "";
    
    // setiap value indeks adalah beberapa posting
    foreach ($postings as $posting) {
        
        // format id:frekuensi:posisi
        list($id, $freq, $pos) = $posting;
        $posting_string = "$id:$freq:$pos";
        $posting_list[] = $posting_string;
        
    }
    
    $df = count($postings);
    
    $posting_list_string = implode(",", $posting_list);
 
    echo "Menulis term $term ($df dokumen)\n";
    
    // masukkan ke indeks yang sebenarnya
    $db->query("INSERT INTO `{$index_table}` (`term`, `posting_list`) VALUES ('$term', '$posting_list_string')");
    
}

// selesai, hapus index di memory
unset($index);

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;
 
echo "\nTerindeks dalam $time detik\n";
