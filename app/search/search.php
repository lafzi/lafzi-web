<?php

// prototipe pencari

// profiling
$time_start = microtime(true);

include '../lib/db.php';
include '../lib/trigram.php';
include '../lib/array_utility.php';

$db = new mysqlDB;
$db->connect();

// sesuaikan encoding
$db->query("set character_set_server='utf8'");
$db->query("set names 'utf8'");

// input
$index_table = 'index2';
$query_final = "LATAKUMFIHIABADA"; // seharusnya melalui algoritma fonetik juga
$threshold = 0.6;

// ekstrak trigram dari query ==================================================
$query_trigrams = ekstrak_trigram($query_final);
$query_trigrams_count = count($query_trigrams);

// frekuensi trigram query
$query_trigrams_freq = array_count_values($query_trigrams);

// pisahkan trigram yang unik (tidak berulang) dan nonunik
$query_trigrams_u = array();
$query_trigrams_nu = array();
foreach ($query_trigrams_freq as $qt => $qtf) {
    if ($qtf == 1) {
        $query_trigrams_u[] = $qt; // tidak perlu disimpan frekuensi karena pasti 1
    } else {
        $query_trigrams_nu[] = array('trigram' => $qt, 'freq' => $qtf);
    }
}

// cari dalam indeks semua entri yang mengandung trigram unik dari query =======
$db_search_query = "SELECT * FROM `$index_table` WHERE `term` IN ('{$query_trigrams_u[0]}'";

for ($i = 1; $i < count($query_trigrams_u); $i++) {
    $db_search_query .= ", '{$query_trigrams_u[$i]}'";
}

$db_search_query .= ")";

$db_search_result = $db->get_result($db_search_query, "assoc");

$posting_lists = array();
$posting_lists_string = "";

// cari dalam indeks semua entri yang mengandung trigram nonunik dari query ====

foreach ($query_trigrams_nu as $el) {
    
    $res = $db->get_result("SELECT * FROM `$index_table` WHERE `term` = '{$el['trigram']}'", "assoc");
   
    $pl = explode(",", $res[0]['posting_list']);
    $fl = explode(",", $res[0]['freq_list']);
    
    $pl = array_repeat_freq($pl, array_min_value($fl, $el['freq']));
    
    $posting_lists_string .= implode(",", $pl) . ",";
    
}

// gabungkan seluruh posting_list yang didapat untuk trigram unik ==============
for ($i = 0; $i < count($db_search_result); $i++) {
    // via string untuk optimalisasi performa
    $posting_lists_string .= $db_search_result[$i]['posting_list'] . ",";    
}

// hitung frekuensi (= jumlah common trigram) ==================================
$posting_lists = explode(",", $posting_lists_string);
$posting_lists_freq = array_count_values($posting_lists);

// diurutkan berdasarkan yang paling banyak cocoknya ===========================
arsort($posting_lists_freq);

// akan menampung daftar dokumen yang "dianggap relevan" =======================
$retrieved = array();
$n_threshold = $threshold * $query_trigrams_count;

foreach ($posting_lists_freq as $doc_id => $common_trigrams_count) {
    
    // difilter berdasarkan threshold
    if ($common_trigrams_count >= $n_threshold) {
        
        $retrieved[] = array(
            "doc_id" => $doc_id,
            "relevance" => $common_trigrams_count / $query_trigrams_count
        );
        
    }
    
}

// output sederhana ============================================================
/*
echo "Hasil pencarian\n";
echo "===============\n\n";

echo "Query                : $query_final\n";
echo "Jumlah trigram query : $query_trigrams_count (".count($query_trigrams_u)." unik)\n";
echo "Threshold            : $threshold\n";
echo "Ditemukan            : ".count($retrieved)." dokumen\n";
echo "Hasil cari           : \n\n";

foreach ($retrieved as $doc) {
    
    echo "- Dokumen #{$doc['doc_id']} (relevansi : ".round($doc['relevance'], 2).")\n";
    
    $doc_data = $db->get_result("SELECT * FROM `doc` WHERE `id` = {$doc['doc_id']}", "assoc");
    
    echo "  Surat {$doc_data[0]['surat']} ayat {$doc_data[0]['ayat']}\n";
    echo "  Teks : {$doc_data[0]['teks']}\n\n";
    
}*/

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;
 
echo "\nPencarian dalam $time detik\n";
echo "Memory usage      : " . memory_get_usage() . "\n";
echo "Memory peak usage : " . memory_get_peak_usage() . "\n";
