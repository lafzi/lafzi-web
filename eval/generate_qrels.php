<?php

include 'CSVDataSource.php';

// membuat file qrels yang kompatibel dengan trec_eval
/* param :
 * 
 *  folder  
 *  
 * 
 * 
 */

$folder = $argv[1];
if ($folder=="") exit("Specify a folder \n\n");

$csvq = new File_CSV_DataSource($folder . '/queries.csv');
$csvr = new File_CSV_DataSource($folder . '/rel_judgement.csv');

$qs = $csvq->getHeaders();

// buat folder, kalo udah ada hapus
foreach ($qs as $qid) {
    $qfolder = $folder . '/' . $qid;
    if (is_dir($qfolder)) {
        rrmdir($qfolder);
        mkdir($qfolder);
    } else {
        mkdir($qfolder);
    }
}

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

foreach ($qs as $q_id) {
    
    $target_file = $folder . '/' . $q_id . '/qrel.txt';
    $f = fopen($target_file, 'w');
    
    $variasi_query = $csvq->getColumn($q_id);
    
    $i = 1;
    foreach ($variasi_query as $query) {
        if ($query != '') {
            $docno_relevan = $csvr->getColumn($q_id);
            foreach ($docno_relevan as $doc_no) {
                if ($doc_no != '') {
                    $qid   = $q_id . '-' . $i;
                    $iter  = '0';
                    $docno = $doc_no;
                    $rel   = '1';

                    $qrel_line = "$qid \t $iter \t $docno \t $rel \n";
                    fwrite($f, $qrel_line);
                }
            }  
            $i++;
        }
    }
    
    fclose($f);
}


// recursive dir delete

 function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
 }

