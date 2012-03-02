<?php

// pencari, flat file version

include_once '../lib/trigram.php';
include_once '../lib/array_utility.php';
include_once '../lib/doc_class.php';

// fungsi pencari
// param  : $query_final yang siap cari (sudah melalui pengodean fonetik)
//          $term_list_filename nama file term list
//          $post_list_filename nama file posting list
//          $score_order true jika ingin menghitung keterurutan kemunculan term
// return : array of found_doc object
function search($query_final, $term_list_filename, $post_list_filename, $score_order = true, $filtered = true, $filter_threshold = 0.8) {

    // baca seluruh term list simpan dalam hashmap
    $term_hashmap = array();
    $term_list = fopen($term_list_filename, 'r');

    while (($line = fgets($term_list, 32)) !== false) {
        list($term, $offset) = explode('|', $line);
        $term_hashmap[$term] = intval($offset);
    }

    fclose($term_list);    
    
    // akses posting list
    $post_list_file = new SplFileObject($post_list_filename);

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
            $matched_posting_lists = explode(',', trim($post_list_file->current()));

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
    }

    
    // diambil cuma yang sekian % trigramnya cocok
    $filtered_docs = array();
    $min_score = $filter_threshold * (strlen($query_final) - 2);
    
    // pemberian skor berdasarkan jumlah trigram yang sama dan keterurutan
    if ($score_order)
        foreach ($matched_docs as $doc_found) {
            $doc_found->matched_terms_count_score = $doc_found->matched_trigrams_count / $query_trigrams_count_all;
            $doc_found->matched_terms_order_score = LIS_length(array_values($doc_found->matched_terms));
            
            $LIS = LIS_sequence(array_values($doc_found->matched_terms));
            $doc_found->LIS = $LIS;
            $doc_found->matched_terms_contiguity_score = reciprocal_diff_average($LIS);
            
            $doc_found->score = $doc_found->matched_terms_order_score * $doc_found->matched_terms_contiguity_score;            

            if ($filtered) if ($doc_found->matched_trigrams_count >= $min_score) $filtered_docs[] = $doc_found;
        }
    else
        foreach ($matched_docs as $doc_found) {
            $doc_found->matched_terms_count_score = $doc_found->matched_trigrams_count / $query_trigrams_count_all;
            $doc_found->score = $doc_found->matched_terms_count_score;
            
            if ($filtered) if ($doc_found->matched_trigrams_count >= $min_score) $filtered_docs[] = $doc_found;
        }

    if ($filtered) {
        // urutkan berdasarkan doc->score
        usort($filtered_docs, 'matched_docs_cmp');
        return $filtered_docs;        
    } else {
        // urutkan berdasarkan doc->score
        usort($matched_docs, 'matched_docs_cmp');
        return $matched_docs;
    }
    
}
