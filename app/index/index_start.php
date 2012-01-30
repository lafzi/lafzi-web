<?php

// mengindeks dokumen

// profiling
$time_start = microtime(true);

include '../lib/db.php';
include 'fonetik.php';
include 'trigram.php';

$db = new mysqlDB();
$db->connect();
 

// fase I : mengekstrak seluruh term dari seluruh dokumen
echo "Fase I \n\n"; 
 
// baca seluruh dokumen
$docs = $db->get_result("SELECT `id`, `fonetik` AS `fonetik` FROM `doc`", "assoc");
$docs_count = count($docs);

// buat tabel temporer untuk indeks sementara
$db->query("CREATE TEMPORARY TABLE `temp_index` (
                `trigram` char(3) NOT NULL,
                `doc_id` int(11) NOT NULL,
                 KEY `trigram` (`trigram`)
            ) ENGINE=MyISAM;");

// untuk setiap dokumen
foreach ($docs as $doc) {
    
    $id = $doc['id'];
    $text = $doc['fonetik'];

    echo "Dokumen $id : ";
        
    // ekstrak trigram
    $trigrams = ekstrak_trigram($text);
    
    foreach ($trigrams as $trigram) {
        
        // masukkan entri ke indeks sementara
        $db->query("INSERT INTO `temp_index` (`trigram`, `doc_id`) VALUES ('$trigram', '$id')");
        
    }
    
    echo "OK\t";
    echo "(". round($id/$docs_count*100) ."%)";
    echo "\n";
    
}

// fase II : membangun inverted index
echo "\nFase II \n\n";

// kosongkan dulu tabel indeks
$db->query("TRUNCATE `index`");

// dapatkan seluruh trigram yang ada pada indeks sementara
$trigram_terms = $db->get_result("SELECT DISTINCT `trigram` FROM `temp_index` ORDER BY `trigram`");

foreach ($trigram_terms as $term) {
    
    // dapatkan ID dokumen yang memiliki term ini
    $posting_list = $db->get_result("SELECT `doc_id` FROM `temp_index` WHERE `trigram` = '$term' ORDER BY `doc_id`");
    
    // posting list disimpan dalam bentuk string dipisah koma
    $posting_list_string = implode(",", $posting_list);
    
    // hitung DF
    $df = count(array_unique($posting_list)); // dihitung unique karena satu dokumen bisa punya beberapa trigram berulang
    
    echo "$term : $df dokumen\n";
    
    // masukkan ke indeks yang sebenarnya
    $db->query("INSERT INTO `index` (`term`, `df`, `posting_list`) VALUES ('$term', '$df', '$posting_list_string')");
    
}

// selesai, hapus tabel temporer tadi
$db->query("DROP TABLE `temp_index`");

// hasil profiling waktu eksekusi
$time_end = microtime(true);
$time = $time_end - $time_start;
 
echo "\nTerindeks dalam $time detik\n";