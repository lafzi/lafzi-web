<?php

include '../eval/CSVDataSource.php';

$quran_teks = file('quran_teks.txt');
$c = count($quran_teks);

$surah_map = array();
for ($i = 0; $i < $c; $i++) {
    
    // split pada karakter "|"
    // [0] = nomor surat
    // [1] = nama surat
    // [2] = nomor ayat
    // [3] = teks ayat
    $data = mb_split("\|", $quran_teks[$i]);
    
    $surah_map[$data[0]][$data[2]] = $i+1;
    
}

$folder = $argv[1];
$csvr = new File_CSV_DataSource('../eval/' . $folder . '/rel_judgement.csv');

$fonetik_vokal = file("fonetik_vokal.txt", FILE_IGNORE_NEW_LINES);

echo "Panjang rata-rata dokumen relevan (V) : \n";

foreach ($csvr->getHeaders() as $qid) {
    
    //echo $qid . "\t";
    
    $docno_relevan = $csvr->getColumn($qid);
    $docno_relevan = array_filter($docno_relevan);
    
    $sum = 0;
    $count = count($docno_relevan);
    
    $sum = array_sum(
            array_map(function($el){
                global $surah_map, $fonetik_vokal;
                
                list($s, $a) = explode(':', $el);
                $docid = $surah_map[$s][$a];
                list(, $doc_text) = explode('|', $fonetik_vokal[$docid-1]);
                return strlen($doc_text);
            }, $docno_relevan)
    );
            
    echo round($sum/$count, 2);
    
    echo "\n";
}


