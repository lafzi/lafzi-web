<?php

// prototipe pencari, flat file version

// profiling
$time_start = microtime(true);

include '../lib/trigram.php';
include '../lib/array_utility.php';
include '../lib/doc_class.php';

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

// akses posting list
$post_list_file = new SplFileObject($post_list_filename);

$query_final = "KULHUWALAHUAHAD"; // seharusnya melalui algoritma fonetik juga

// ekstrak trigram dari query
$query_trigrams = trigram_frekuensi_posisi($query_final);
$query_trigrams_count = count($query_trigrams);
$query_trigrams_count_all = strlen($query_final) - 2;

$matched_posting_lists = array();
$matched_docs = array();

// untuk setiap trigram dari query
foreach ($query_trigrams as $query_trigram => $qtfp) {
    list($qt_freq, $qt_pos) = $qtfp;
    
    if (isset($term_hashmap[$query_trigram])) {    
        // ambil posting list yang sesuai untuk trigram ini
        $post_list_file->fseek($term_hashmap[$query_trigram]);
        $matched_posting_lists = explode(",", trim($post_list_file->current()));
        
        // untuk setiap posting list untuk trigram ini
        foreach ($matched_posting_lists as $data) {
            list ($doc_id, $term_freq, $term_pos) = explode(":", $data);

            // hitung jumlah kemunculan dll
            if (isset($matched_docs[$doc_id])) {
                $matched_docs[$doc_id]->matched_trigrams_count += ($qt_freq < $term_freq) ? $qt_freq : $term_freq;
            } else {
                $matched_docs[$doc_id] = new found_doc();
                $matched_docs[$doc_id]->matched_trigrams_count = 1;
                $matched_docs[$doc_id]->id = $doc_id;
            }
            
            $matched_docs[$doc_id]->matched_terms[$query_trigram] = $term_pos;
        }
        
    }
}

// pemberian skor berdasarkan jumlah trigram yang sama + keterurutan term
foreach ($matched_docs as $doc_found) {
    $doc_found->matched_terms_count_score = $doc_found->matched_trigrams_count / $query_trigrams_count_all;
    $doc_found->score = $doc_found->matched_terms_count_score;
}

// urutkan berdasarkan doc->score
usort($matched_docs, "matched_docs_cmp");

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "\nPencarian dalam $time detik\n";
echo "Memory usage      : " . memory_get_usage() . "\n";
echo "Memory peak usage : " . memory_get_peak_usage() . "\n\n";

print_r($matched_docs);

