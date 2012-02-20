<?php

// membuat file qrels yang kompatibel dengan trec_eval

$rel_judgment_filename = "rel_judgment_lafadz.txt";
$qrels_target_filename = "trec_qrels.txt";

$rel_judgment = file($rel_judgment_filename, FILE_IGNORE_NEW_LINES);

/*  Rel_info_file format: Standard 'qrels'
    Relevance for each docno to qid is determined from rel_info_file, which 
    consists of text tuples of the form 
       qid  iter  docno  rel 
    giving TREC document numbers (docno, a string) and their relevance (rel,  
    a non-negative integer less than 128, or -1 (unjudged)) 
    to query qid (a string).  iter string field is ignored.   
    Fields are separated by whitespace, string fields can contain no whitespace. 
    File may contain no NULL characters. 
*/

$rj = fopen($qrels_target_filename, "w");

foreach ($rel_judgment as $line) {
    
    list($q, $rel_list) = explode("|", $line);
    $q = str_replace(" ", "_", $q);
    $rel_list = explode(",", $rel_list);
    
    echo $q . " : \n";
    
    foreach ($rel_list as $doc) {
        
        echo " - $doc \n";
        
        $qrels_line = "{$q}\t0\t{$doc}\t1\n";
        fwrite($rj, $qrels_line);
        
    }
    
}

fclose($rj);