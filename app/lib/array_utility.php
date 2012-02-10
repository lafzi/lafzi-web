<?php

// fungsi untuk menghitung "skor keterurutan" dari array
// param  : $array of integer yang dicari skornya
// return : skor
function array_order_score(array $array) {
    
    // for single and double element array
    $c = count($array);
    if ($c == 1) return 1;
    if ($c == 2) return ($array[0] < $array[1]) ? 2 : 1;
    
    // return LIS length / count
    return LIS_length($array) / count($array);
    
}

// mencari panjang Longest Increasing Subsequences
// algoritme Fredman (1975) dalam Liben-Nowell et al. (2006)
// param  : $array of integer
// return : panjang subsekuen monoton naik terpanjang
function LIS_length($array) {
    
    // initialize
    $k = 0;
    $A = array();
    $A[0] = -1000000;
    $A[1] =  1000000;
    
    // loop
    $len = count($array);
    for ($i = 0; $i < $len; $i++) {
        $x = $array[$i];
        $l = array_insertion_index($A, $x);
        
        if (isset($A[$l + 1])) $A[$l + 1] = $x; // if exists replace, else append
        else $A[] = $x;
        
        if ($l + 1 > $k) {
            $k++;
            if (isset($A[$k + 1])) $A[$k + 1] = 1000000;
            else $A[] = 1000000;
        }
    }
    
    return $k;
    
}

// mencari indeks untuk insertion dalam array dengan binary search
// param  : $array of integer, strictly increasing
//          $n integer yang dicari
// return : indeks L dimana $array[L] < $n < $array[L+1]
function array_insertion_index($array, $n) {
    
    $lo = 0;
    $hi = count($array) - 1;
    
    while ($lo <= $hi) {
        $mid = floor(($lo + $hi) / 2);
        // if fits
        if ($array[$mid] < $n && $n < $array[$mid+1]) {
            return $mid;
        // if seharusnya ke kiri
        } else if ($n < $array[$mid] && $n < $array[$mid+1]) {
            $hi = $mid;
        // if seharusnya ke kanan
        } else if ($n > $array[$mid] && $n > $array[$mid+1]) {
            $lo = $mid;
        } else return false;
    }
    
}

// echo array_order_score(array(33,34,35,36,37,38,39,4,41,25,43)) . "\n";