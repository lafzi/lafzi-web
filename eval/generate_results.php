<?php

// membuat file hasil pencarian yang kompatibel dengan trec_eval

$vokal = false;
$order = false;

if ($vokal && $order)
    $schema = "vokal-order";
else if ($vokal && !$order)
    $schema = "vokal-nonorder";
else if (!$vokal && $order)
    $schema = "nonvokal-order";
else if (!$vokal && !$order)
    $schema = "nonvokal-nonorder";

$rel_judgment_filename = "rel_judgment_lafadz.txt";
$result_target_filename = "trec_result_$schema.txt";

$rel_judgment = file($rel_judgment_filename, FILE_IGNORE_NEW_LINES);

// here we search

include_once '../search/search_ff.php';
include_once '../lib/fonetik_id.php';

if ($vokal) {
    $term_list_filename = "../data/index_termlist_vokal.txt";
    $post_list_filename = "../data/index_postlist_vokal.txt";
} else {
    $term_list_filename = "../data/index_termlist_nonvokal.txt";
    $post_list_filename = "../data/index_postlist_nonvokal.txt";
}

$quran_text = file("../data/quran_teks.txt", FILE_IGNORE_NEW_LINES);

/*  Results_file format: Standard 'trec_results'
    Lines of results_file are of the form 
         030  Q0  ZF08-175-870  0   4238   prise1 
         qid iter   docno      rank  sim   run_id 
    giving TREC document numbers (a string) retrieved by query qid  
    (a string) with similarity sim (a float).  The other fields are ignored, 
    with the exception that the run_id field of the last line is kept and 
    output.  In particular, note that the rank field is ignored here; 
    internally ranks are assigned by sorting by the sim field with ties  
    broken deterministicly (using docno). 
    Sim is assumed to be higher for the docs to be retrieved first. 
    File may contain no NULL characters. 
    Lines may contain fields after the run_id; they are ignored. 
 */

$res = fopen($result_target_filename, "w");

foreach ($rel_judgment as $line) {
    
    list($q, ) = explode("|", $line);
    $qe = str_replace(" ", "_", $q);
    $query_final = id_fonetik($q, !$vokal);
    
    echo $qe . " : ";
    
    $matched_docs = & search($query_final, $term_list_filename, $post_list_filename, $order); // using ff
    
    $match_count = count($matched_docs);
    
    echo $match_count . "\n";
    
    for ($i = 0; $i < $match_count; $i++) {
        
        $doc = $matched_docs[$i];
        $doc_data = explode('|', $quran_text[$doc->id - 1]);
        
        $result_lines = "{$qe}\t0   \t{$doc_data[0]}:{$doc_data[2]}\t 0   \t".round($doc->score, 2)." \t {$schema} \n";
        fwrite($res, $result_lines);
        
        echo " - {$doc_data[0]}:{$doc_data[2]}\n";
        
    }
    
}

fclose($res);