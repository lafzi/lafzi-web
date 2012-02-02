<?php

// mengindeks dokumen, versi flat file

// profiling
$time_start = microtime(true);

include '../lib/fonetik.php';
include '../lib/trigram.php';

// parameter
$doc_file = "../data/fonetik_vokal.txt";

$term_list_file    = "../data/index_termlist_vokal.txt";
$posting_list_file = "../data/index_postlist_vokal.txt";

// fase I : mengekstrak seluruh term dari seluruh dokumen dan membangun indeks
echo "Fase I \n\n"; 
 
// baca seluruh dokumen
$docs = file($doc_file);
$docs_count = count($docs);

// array besar penyimpan indeks
$index = array();

// untuk setiap dokumen
foreach ($docs as $doc) {
    
    // dipeca pada karakter |
    list($id, $text) = explode("|", $doc);
    
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
    
}

unset($docs);

// fase II : menulis inverted index
echo "\nFase II \n\n";

// siapkan file untuk ditulisi
$fh_index    = fopen($term_list_file, "w");
$fh_postlist = fopen($posting_list_file, "w");

$offset = 0;

// urutkan key pada array indeks
ksort($index);

// untuk setiap term pada indeks
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
    
    // tulis ke file
    fwrite($fh_index, $term."|".$offset."\n");
    fwrite($fh_postlist, $posting_list_string."\n");
    
    // offset indeks term untuk dicari di file posting list
    $offset += strlen($posting_list_string) + 1;
}

// selesai, hapus index di memory
unset($index);
fclose($fh_index);
fclose($fh_postlist);

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;
 
echo "\nTerindeks dalam $time detik\n";
