<?php

// mengekstrak trigram dari sebuah string
// param  : $string yang akan diambil trigramnya
// return : array berisi semua trigram
function ekstrak_trigram($string) {
    
    $len = strlen($string);
    
    if ($len <= 3) return array($string);
    
    $trigrams = array();
    for ($i = 0; $i <= $len - 3; $i++) {
        
        $trigrams[] = substr($string, $i, 3);
        
    }
    
    return $trigrams;
    
}

// menghitung frekuensi trigram
// param  : $string yang akan dihitung frekuensi trigramnya
// return : array berisi trigram sebagai key dan frekuensi sebagai value
function frekuensi_trigram_string($string) {
    
    $array = ekstrak_trigram($string);
    
    // fungsi bawaan PHP
    return array_count_values($array);
    
}

// test suite

// print_r(frekuensi_trigram_string("TO BE OR NOT TO BE IS THE QUESTION")); 
 

