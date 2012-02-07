<?php

// prototipe pencari, db version

include_once '../lib/trigram.php';
include_once '../lib/array_utility.php';
include_once '../lib/doc_class.php';
include_once '../lib/db.php';

if (!isset($db)) {
    $db = new mysqlDB();
    $db->connect();
}

// fungsi pencari
// param  : $query_final yang siap cari (sudah melalui pengodean fonetik)
//          $term_list_filename nama file term list
//          $post_list_filename nama file posting list
// return : array of found_doc object
function search($query_final, $index_table) {

    global $db;
    
    // ekstrak trigram dari query
    $query_trigrams = trigram_frekuensi_posisi($query_final);
    $query_trigrams_count = count($query_trigrams);
    $query_trigrams_count_all = strlen($query_final) - 2;

    $matched_posting_lists = array();
    $matched_docs = array();

    // untuk setiap trigram dari query
    foreach ($query_trigrams as $query_trigram => $qtfp) {
        list($qt_freq, $qt_pos) = $qtfp;

        // ambil posting list yang sesuai untuk trigram ini
        // $post_list_file->fseek($term_hashmap[$query_trigram]);

        $term = $db->get_result("SELECT `posting_list` FROM `$index_table` WHERE `term` = '$query_trigram'");

        $matched_posting_lists = explode(',', $term[0]);

        // untuk setiap posting list untuk trigram ini
        foreach ($matched_posting_lists as $data) {
            list ($doc_id, $term_freq, $term_pos) = explode(':', $data);

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

    // pemberian skor berdasarkan jumlah trigram yang sama
    foreach ($matched_docs as $doc_found) {
        $doc_found->matched_terms_count_score = $doc_found->matched_trigrams_count / $query_trigrams_count_all;
        $doc_found->score = $doc_found->matched_terms_count_score;
    }

    // urutkan berdasarkan doc->score
    usort($matched_docs, 'matched_docs_cmp');

    return $matched_docs;
    
}
