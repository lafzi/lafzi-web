<?php

function array_values_flatten(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
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

// merge posisi trigram untuk higlight
/* misalnya begini
 *      
 *      0, 0, 1, 3, 4, 5, 6, 7, 20, 22, 24
 * 
 * di-lookforward sepanjang mungkin selama masuk 3 sekuens untuk higlight, jadi
 * 
 *       0 :  7
 *      20 : 24
 * 
 */
function longest_highlight_lookforward($hl_sequence, $min_length = 3) {

    $len = count($hl_sequence);
    if ($len == 1) return array(array((int)$hl_sequence[0], (int)$hl_sequence[0] + $min_length));
    
    sort($hl_sequence);
    
    $res = array();
    $j = 1;
    
    for ($i=0; $i<$len; $i++) {        
        while (isset($hl_sequence[$j]) && $hl_sequence[$j] - $hl_sequence[$j-1] <= $min_length+1 && $j < $len) {
            $j++;
        }
        $res[] = array((int)$hl_sequence[$i], (int)$hl_sequence[$j-1]);
        $i = $j-1;
        $j++;
    }
    
    return $res;
}

// print_r(longest_highlight_lookforward(array(1, 2, 36, 4, 5, 6, 7)));


// New scoring function

function longest_contiguous_subsequence($seq, $maxgap = 5) {
    
    sort($seq);
    $size = count($seq);
    $start = 0;
    $length = 0;
    $maxstart = 0;
    $maxlength = 0;
    
    for ($i = 0; $i < $size - 1; $i++) {
        if (($seq[$i+1] - $seq[$i]) > $maxgap) {
            $length = 0;
            $start = $i+1;
        } else {
            $length++;
            if ($length > $maxlength) {
                $maxlength = $length;
                $maxstart = $start;
            }
        }
    }
    
    $maxlength++;
    //echo "START: $maxstart \nFINISH: $maxfinish\n\n";
    
    return array_slice($seq, $maxstart, $maxlength);
}

// $aarr = array(7,33,209,218,233,8,210,207,211,212,213,291,1,214,292,2,158,190,215,265,275,216,217);
// sort($aarr);

// print_r($aarr);
// print_r(longest_contiguous_subsequence($aarr));










