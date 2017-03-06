<?php

// buat hitung panjang posting list

$tls = file('index_termlist_nonvokal.txt', FILE_IGNORE_NEW_LINES);
$pls = file('index_postlist_nonvokal.txt', FILE_IGNORE_NEW_LINES);

$c = count($tls);
$a = array();

for ($i = 0; $i < $c; $i++) {
    
    list($term, ) = explode('|', $tls[$i]);
    
    $a[$term] = count(explode(',', $pls[$i]));
    
}

arsort($a);

foreach ($a as $t => $df) {

    echo $t . "\t" . $df . "\n";

}


