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

// mencari Longest Increasing Subsequences
// dalam Liben-Nowell et al. (2006)
// param  : $array of integer
// return : array of integer, subsekuen increasing terpanjang
function LIS_sequence($array) {
    
    // initialize
    $A = array();
    $seq  = array();

    $k = 0;
    $A[0] = -1000000;
    $A[1] =  1000000;
    $seq[0] = array();
    
    // loop
    $len = count($array);
    for ($i = 0; $i < $len; $i++) {
        $x = $array[$i];
        $l = array_insertion_index($A, $x);
        
        if (isset($A[$l + 1])) $A[$l + 1] = $x; // if exists replace, else append
        else $A[] = $x;
        
        // update the sequence
        if (!isset($seq[$l])) $seq[$l] = array();
        $t = $seq[$l];
        array_push($t, $x);
        
        if (isset($seq[$l+1])) {
            $seq[$l+1] = $t;
        } else {
            $seq[] = $t;
        }
        
        if ($l + 1 > $k) {
            $k++;
            if (isset($A[$k + 1])) $A[$k + 1] = 1000000;
            else $A[] = 1000000;
        }
    }
    
    return $seq[$k];
    
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

// menghitung jumlah interval antar elemen array yang berdampingan
// param  : $array of integer
// return : jumlah interval
function array_diff_sum($array) {
    
    $diff = array();
    $len = count($array);
    
    for ($i = 1; $i < $len; $i++) {
        $diff[] = $array[$i] - $array[$i-1];
    }
    
    return array_sum($diff);
}

// menghitung rata-rata resiprokal dari interval elemen array berdampingan
// param  : $array of integer
// return : rata-rata interval resiprokal
function reciprocal_diff_average($array) {
    
    $diff = array();
    $len = count($array);
    
    if ($len == 1) return 1;
    
    for ($i = 0; $i < $len-1; $i++) {
        $diff[] = 1 / ($array[$i+1] - $array[$i]);
    }
    
    return array_sum($diff) / ($len-1);
}
