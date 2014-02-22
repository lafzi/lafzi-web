<?php

// mengekstrak trigram dari sebuah string
// param  : $string yang akan diambil trigramnya
// return : array berisi semua trigram
function ekstrak_trigram($string) {
    
    $string = trim($string);
    
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

// menghitung frekuensi trigram
// param  : $string yang akan dihitung frekuensi dan posisi kemunculan pertama trigramnya
// return : array berisi trigram sebagai key dan (frekuensi, posisi) sebagai value
function trigram_frekuensi_posisi($string) {
    
    $array = ekstrak_trigram($string);
    $array_freq = array_count_values($array);
    
    $res = array();
    
    foreach ($array_freq as $trigram => $freq) {
        
        $pos = strpos($string, $trigram) + 1;
        $res[$trigram] = array($freq, $pos);
        
    }
    
    return $res;
    
}

function trigram_frekuensi_posisi_all($string) {
    
    $array = ekstrak_trigram($string);
    $array_freq = array_count_values($array);
    
    $res = array();
    
    foreach ($array_freq as $trigram => $freq) {
        
        $pos = strpos_all($string, $trigram);
        $res[$trigram] = array($freq, $pos);
        
    }
    
    return $res;
    
}


function strpos_all($haystack,$needle){
    $s=0;
    $i=0;
    while (is_integer($i)){        
        $i = strpos($haystack,$needle,$s);
        if (is_integer($i)) {
            $aStrPos[] = $i+1;
            $s = $i+strlen($needle);
        }
    }
    if (isset($aStrPos)) {
        return $aStrPos;
    }
    else {
        return false;
    }
}

// test suite

// print_r(trigram_frekuensi_posisi_all("ALAAALAMANALALA")); 
 

