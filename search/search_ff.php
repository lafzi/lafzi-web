<?php

// pencari, flat file version

include_once '../lib/trigram.php';
include_once '../lib/array_utility.php';
include_once '../lib/doc_class.php';
include_once '../lib/predis/autoload.php';

// fungsi pencari
// param  : $query_final yang siap cari (sudah melalui pengodean fonetik)
//          $vocal aktif atau tidak
//          $score_order true jika ingin menghitung keterurutan kemunculan term
// return : array of found_doc object
function search($query_final, $vocal, $score_order = true, $filtered = true, $filter_threshold = 0.8, $use_redis = false) {

    // ekstrak trigram dari query
    $query_trigrams = trigram_frekuensi_posisi($query_final);
    $query_trigrams_count = count($query_trigrams);
    $query_trigrams_count_all = strlen($query_final) - 2;
    
    if ($query_trigrams_count <= 0 || $query_trigrams_count_all <= 0) {
        return array();
    };
    
    $matched_posting_lists = array();
    $matched_docs = array();

    // untuk setiap trigram dari query
    foreach ($query_trigrams as $query_trigram => $qtfp) {
        list($qt_freq, $qt_pos) = $qtfp;
        
        if ($use_redis) {

            Predis\Autoloader::register();
            $redis = new Predis\Client();

            $key_prefix = $vocal ? "vocal-" : "nonvocal-";
            $key = $key_prefix.$query_trigram;

            // index dari redis
            if ($redis->exists($key)){
                //ambil posting list yang sesuai untuk trigram ini
                $matched_posting_lists = json_decode($redis->get($key),true);

                // untuk setiap posting list untuk trigram ini
                foreach ($matched_posting_lists as $data) {
                    list ($doc_id, $term_freq, $term_pos) = $data;

                    // hitung jumlah kemunculan dll
                    if (isset($matched_docs[$doc_id])) {
                        $matched_docs[$doc_id]->matched_trigrams_count += ($qt_freq < $term_freq) ? $qt_freq : $term_freq;
                    } else {
                        $matched_docs[$doc_id] = new found_doc();
                        $matched_docs[$doc_id]->matched_trigrams_count = 1;
                        $matched_docs[$doc_id]->id = $doc_id;
                    }

                    $matched_docs[$doc_id]->matched_terms[$query_trigram] = $term_pos; // $term_pos is an array
                }
            }

        } else {

            if ($vocal) {
                $term_list_filename = "../data/index_termlist_vokal.txt";
                $post_list_filename = "../data/index_postlist_vokal.txt";
            } else {
                $term_list_filename = "../data/index_termlist_nonvokal.txt";
                $post_list_filename = "../data/index_postlist_nonvokal.txt";
            }

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

            // index dari file
            if (isset($term_hashmap[$query_trigram])) {
                // ambil posting list yang sesuai untuk trigram ini
                $post_list_file->fseek($term_hashmap[$query_trigram]);
                $matched_posting_lists = explode(';', trim($post_list_file->current()));

                // untuk setiap posting list untuk trigram ini
                foreach ($matched_posting_lists as $data) {
                    list ($doc_id, $term_freq, $term_pos) = explode(':', $data);
                    $term_pos = explode(',', $term_pos);
                    //$term_pos = reset(explode(',', $term_pos));

                    // hitung jumlah kemunculan dll
                    if (isset($matched_docs[$doc_id])) {
                        $matched_docs[$doc_id]->matched_trigrams_count += ($qt_freq < $term_freq) ? $qt_freq : $term_freq;
                    } else {
                        $matched_docs[$doc_id] = new found_doc();
                        $matched_docs[$doc_id]->matched_trigrams_count = 1;
                        $matched_docs[$doc_id]->id = $doc_id;
                    }

                    $matched_docs[$doc_id]->matched_terms[$query_trigram] = $term_pos; // $term_pos is an array
                }
            }
        }
    }
    
    // bila difilter, diambil cuma yang sekian % trigramnya cocok
    $filtered_docs = array();
    $min_score = $filter_threshold * (strlen($query_final) - 2);
    
    // pemberian skor berdasarkan jumlah trigram yang sama dan keterurutan
    if ($score_order) {
        foreach ($matched_docs as $doc_found) {
            $doc_found->matched_terms_count_score = $doc_found->matched_trigrams_count;
            
            $LIS = longest_contiguous_subsequence(array_values_flatten($doc_found->matched_terms)); // LIS_sequence(array_values_flatten($doc_found->matched_terms));
            
            $doc_found->matched_terms_order_score = count($LIS);
            $doc_found->LIS = $LIS;
            $doc_found->matched_terms_contiguity_score = reciprocal_diff_average($LIS);
            
            $doc_found->score = $doc_found->matched_terms_order_score * $doc_found->matched_terms_contiguity_score;            

            if ($filtered) if ($doc_found->matched_trigrams_count >= $min_score) $filtered_docs[] = $doc_found;
        }
    } else {
        foreach ($matched_docs as $doc_found) {
            $doc_found->matched_terms_count_score = $doc_found->matched_trigrams_count;
            $doc_found->score = $doc_found->matched_terms_count_score;
            
            if ($filtered) if ($doc_found->matched_trigrams_count >= $min_score) $filtered_docs[] = $doc_found;
        }
    }

    if ($filtered) {
        return $filtered_docs;        
    } else {
        return $matched_docs;
    }
    
}
