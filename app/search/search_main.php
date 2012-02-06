<?php

include 'search_ff.php';
include '../lib/fonetik_id.php';

// profiling
$time_start = microtime(true);

$query = "bismillahirrahmaanirrahim";
$query_final = id_fonetik($query, false);

echo $query_final."\n";

$term_list_filename = "../data/index_termlist_vokal.txt";
$post_list_filename = "../data/index_postlist_vokal.txt";

$matched_docs =& search($query_final, $term_list_filename, $post_list_filename); // using ff
// $matched_docs =& search($query_final, 'index2'); // using db

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "\nPencarian dalam $time detik\n";
echo "Memory usage      : " . memory_get_usage() . "\n";
echo "Memory peak usage : " . memory_get_peak_usage() . "\n\n";

print_r($matched_docs);