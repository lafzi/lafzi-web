<?php

// fungsi untuk mencari nilai minimum elemen array dengan suatu bilangan
// param  : $array yang elemennya dibandingkan
//          $n     bilangan pembanding
// return : array yang sudah dicari minimum dengan bilangan
function array_min_value(array $array, $n) {
    
    for ($i=0; $i<count($array); $i++) {
        if ($n < $array[$i]) $array[$i] = $n;
    }
    
    return $array;
    
}

// fungsi untuk mengulang elemen array sesuai frekuensinya
// param  : $array      of integer yang elemennya akan diulang
//          $freq_array of integer jumlah pengulangan sesuai urutan elemen di $array
// return : array yang sudah diulang elemennya
function array_repeat_freq(array $array, array $freq_array) {
    
    $res = array();
    
    for ($i=0; $i<count($array); $i++) {
        for ($j=0; $j<$freq_array[$i]; $j++) {
            $res[] = $array[$i];
        }
    }
    
    return $res;
    
}

