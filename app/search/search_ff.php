<?php

// prototipe pencari, flat file version

// profiling
$time_start = microtime(true);

include '../lib/trigram.php';
include '../lib/array_utility.php';

// file term list dan posting list
$term_list_filename = "../data/index_termlist_vokal.txt";
$post_list_filename = "../data/index_postlist_vokal.txt";

// baca seluruh term list
$term_list = file($term_list_filename);
$term_hashmap = array();

// simpan dalam hashmap
foreach ($term_list as $line) {
    list($term, $offset) = explode("|", $line);
    $term_hashmap[$term] = intval($offset);
}

unset($term_list);

// akses posting list tanpa dibaca
$post_list_file = new SplFileObject($post_list_filename);

$query_final = "LATAKUMFIHIABADA"; // seharusnya melalui algoritma fonetik juga

// ekstrak trigram dari query ==================================================
$query_trigrams = ekstrak_trigram($query_final);
$query_trigrams_count = count($query_trigrams);

$matched_posting_list_string = "";
$matched_posting_lists = array();

foreach ($query_trigrams as $query_trigram) {
    if (isset($term_hashmap[$query_trigram])) {
        
        $post_list_file->fseek($term_hashmap[$query_trigram]);
    
        $matched_posting_list_string .= trim($post_list_file->current()) . ",";
        
    }
}

$matched_posting_lists = explode(",", $matched_posting_list_string);

$matched_docs = array();

foreach ($matched_posting_lists as $data) {
    if ($data != "") {
        
        list($doc_id, $term_freq, $pos) = explode(":", $data);
        if (isset($matched_docs[$doc_id])) 
            $matched_docs[$doc_id]++;
        else
            $matched_docs[$doc_id] = 0;        
    }
}

arsort($matched_docs);

//print_r($matched_docs);


// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "\nPencarian dalam $time detik\n";
echo "Memory usage      : " . memory_get_usage() . "\n";
echo "Memory peak usage : " . memory_get_peak_usage() . "\n";

