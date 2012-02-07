<?php

include 'search_ff.php';
include '../lib/fonetik_id.php';

// profiling
$time_start = microtime(true);

$query = "al-yauma akmaltu lakum diinakum";
$query_final = id_fonetik($query, false);
$query_trigrams_count = strlen($query_final) - 2;

$term_list_filename = "../data/index_termlist_vokal.txt";
$post_list_filename = "../data/index_postlist_vokal.txt";

$matched_docs =& search($query_final, $term_list_filename, $post_list_filename); // using ff

$num_doc_found = count($matched_docs);
$quran_text = file("../data/quran_teks.txt", FILE_IGNORE_NEW_LINES);

// output

echo "Hasil pencarian\n";
echo "===============\n\n";

echo "Query                : $query\n";
echo "Kode fonetik         : $query_final\n";
echo "Jumlah trigram query : $query_trigrams_count\n";
echo "Ditemukan            : $num_doc_found dokumen\n";
echo "Hasil cari (top 10)  : \n\n";

for ($i = 0; $i < 10; $i++) {
    
    $doc = $matched_docs[$i];
    echo "Dokumen #{$doc->id} (jumlah trigram cocok : {$doc->matched_trigrams_count}; skor : ".round($doc->score, 2).")\n";

    $doc_data = explode('|', $quran_text[$doc->id - 1]);

    echo "Surat {$doc_data[1]} ({$doc_data[0]}) ayat {$doc_data[2]}\n";
    echo "Posisi kemunculan : ".  implode(',', array_values($doc->matched_terms))."\n\n";
    echo $doc_data[3] . "\n\n";
    echo "I-------------------------------------------\n\n";
      
}

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "\nPencarian dalam $time detik\n";
echo "Memory usage      : " . memory_get_usage() . "\n";
echo "Memory peak usage : " . memory_get_peak_usage() . "\n\n";
