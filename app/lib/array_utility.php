<?php

// fungsi untuk menghitung "skor keterurutan" dari array
// param  : $array of integer yang dicari skornya
// return : skor
function array_order_score(array $array) {
    
    if (count($array) == 1) return 1;
    
    $array = array_values($array);
    
    $diff = array();
    for ($i = 1; $i < count($array); $i++) {
        $diff[] = abs($array[$i] - $array[$i-1]);
    }
    
    // return count per sum of diff
    return count($diff) / array_sum($diff);
    
}
