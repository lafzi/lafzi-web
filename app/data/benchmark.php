<?php

// membandingkan, multi file single line vs single file multi line
// mengambil 4000 record

$path = "/home/abrari/Desktop/quran_teks_lines";

$random = array();

for ($i = 0; $i < 4000; $i++) {
    $random[] = round(rand(1, 6236));
}

$text = array();

$mode = intval($argv[1]);

$time_start = microtime(true);

if ($mode == 0) {

    // multi file, single line =====================================================

    echo "Multi file : ";

    foreach ($random as $doc_id) {

        $text[] = file_get_contents($path . "/" . str_pad(($doc_id-1), 4, '0', STR_PAD_LEFT));

    }

} else if ($mode == 1) {
    
    // single file, multi line, lazy loading =======================================

    echo "Single file, lazy loading : ";

    $fo = new SplFileObject($path . "/quran_teks.txt");

    foreach ($random as $doc_id) {

        $fo->seek($doc_id);
        $text[] = $fo->current();

    }

} else if ($mode == 2) {

    // single file, multi line, complete loading ===================================

    echo "Single file, complete loading : ";

    $big_data = file($path . "/quran_teks.txt");

    foreach ($random as $doc_id) {

        $text[] = $big_data[$doc_id-1];

    }

}

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "\nDone in $time seconds\n";
echo "Memory usage      : " . memory_get_usage() . "\n";
echo "Memory peak usage : " . memory_get_peak_usage() . "\n\n";

