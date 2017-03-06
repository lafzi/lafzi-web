<?php

// buat hitung panjang posting list

$plsv = file('index_postlist_nonvokal.txt');

foreach ($plsv as $pl) {
    
    echo count(explode(',', $pl)) . "\n";
    
}



