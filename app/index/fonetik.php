<?php

// karakter arabic dibuat alias
define("SYADDAH", "ّ");
define("SUKUN", "ْ");

define("FATHAH", "َ");
define("KASRAH", "ِ");
define("DHAMMAH", "ُ");

define("FATHATAIN", "ً");
define("KASRATAIN", "ٍ");
define("DHAMMATAIN", "ٌ");

define("ALIF", "ا");
define("ALIF_MAQSURA", "ى");
define("ALIF_MAD", "آ");
define("BA", "ب");
define("TA", "ت");
define("TA_MARBUTAH", "ة");
define("TSA", "ث");
define("JIM", "ج");
define("HA", "ح");
define("KHA", "خ");
define("DAL", "د");
define("DZAL", "ذ");
define("RA", "ر");
define("ZA", "ز");
define("SIN", "س");
define("SYIN", "ش");
define("SHAD", "ص");
define("DHAD", "ض");
define("THA", "ط");
define("ZHA", "ظ");
define("AIN", "ع");
define("GHAIN", "غ");
define("FA", "ف");
define("QAF", "ق");
define("KAF", "ك");
define("LAM", "ل");
define("MIM", "م");
define("NUN", "ن");
define("WAU", "و");
define("YA", "ي");
define("HHA", "ه");

define("HAMZAH", "ء");
define("HAMZAH_MAQSURA", "ئ");
define("HAMZAH_WAU", "ؤ");
define("HAMZAH_ALIF_A", "أ");
define("HAMZAH_ALIF_I", "إ");

// mengodekan teks arabic menjadi kode fonetik dengan beberapa langkah
// param  : $ar_string : string teks Al-Quran (arabic)
// return : kode fonetik 
function ar_fonetik($ar_string) {
    
   $ar_string = ar_hilangkan_spasi($ar_string);
   $ar_string = ar_hilangkan_tasydid($ar_string);
   $ar_string = ar_gabung_huruf_mati($ar_string);
   $ar_string = ar_akhir_ayat($ar_string);
   $ar_string = ar_substitusi_tanwin($ar_string);
   $ar_string = ar_hilangkan_mad($ar_string);
   $ar_string = ar_hilangkan_huruf_tidak_dibaca($ar_string);
   $ar_string = ar_substitusi_iqlab($ar_string);
   $ar_string = ar_substitusi_idgham($ar_string);
   $ar_string = ar_hilangkan_harakat($ar_string);
   $kode_fonetik = ar_fonetik_encode($ar_string);
   
   return $kode_fonetik;
   
}

// menghilangkan spasi dari string arabic
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic tanpa spasi
function ar_hilangkan_spasi($ar_string) {
    
    // menggunakan regex lebih mudah
    return mb_ereg_replace("\s*", "", $ar_string);
    
}

// menghilangkan tanda tasydid dari string arabic
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic tanpa tasydid/syaddah
function ar_hilangkan_tasydid($ar_string) {

    // menggunakan regex lebih mudah
    return mb_ereg_replace(SYADDAH, "", $ar_string);    
    
}

// menggabungkan huruf idgham mutamatsilain
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic
function ar_gabung_huruf_mati($ar_string) {

    $arr = ar_string_to_array($ar_string);
    $str = "";
    
    for ($i = 0; $i < count($arr); $i++) {
        
        $curr = $arr[$i];
        $next1 = isset($arr[$i+1]) ? $arr[$i+1] : $arr[$i];
        $next2 = isset($arr[$i+2]) ? $arr[$i+2] : $arr[$i];
        
        if ($next1 == SUKUN && $curr == $next2) {
            // jika terdeteksi huruf bersukun yang selanjutnya huruf yang sama
            // ambil salah satu saja
            // dan pointer array loncat
            $str .= $curr;
            $i += 2;
        } else {
            $str .= $curr;
        }
        
    }
    
    return $str;
    
}

// menangani akhir ayat
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan akhir ayat disesuaikan
function ar_akhir_ayat($ar_string) {
    
    
}

// mensubstitusi tanwin
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan tanwin diganti
function ar_substitusi_tanwin($ar_string) {
    
}

// menghilangkan mad
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan mad dihilangkan
function ar_hilangkan_mad($ar_string) {
    
}

// menghilangkan huruf tidak dibaca
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan huruf tidak dibaca dihilangkan
function ar_hilangkan_huruf_tidak_dibaca($ar_string) {
    
}

// mensubstitusi huruf iqlab
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan huruf iqlab disesuaikan
function ar_substitusi_iqlab($ar_string) {
    
}

// mensubstitusi huruf idgham
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan huruf idgham disesuaikan
function ar_substitusi_idgham($ar_string) {
    
}

// menghilangkan harakat
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic tanpa harakat
function ar_hilangkan_harakat($ar_string) {
    
}

// mensubstitusi kode fonetik
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string latin (kode fonetik)
function ar_fonetik_encode($ar_string) {
    
}

// fungsi bantuan, memecah string arabic menjadi array
// param  : $ar_string : string teks Al-Quran (arabic)
// return : array dari karakter-karakter arabic
function ar_string_to_array($ar_string) {
    $ar_array = array();
    $len = mb_strlen($ar_string, 'UTF-8');

    for($i = 0; $i < $len; $i++){
        $ar_array[] = mb_substr($ar_string, $i, 1, 'UTF-8');
    }    
    
    return $ar_array;
}

// fungsi bantuan, menggabung array menjadi string
// param  : $ar_array : array dari karakter arabic
// return : string arabic
function ar_array_to_string($ar_array) {
    $ar_string = "";
    for($i = 0; $i < count($ar_array); $i++){
        $ar_string .= $ar_array[$i];
    }    
    
    return $ar_string;
}



$ar_string = "تَنَزَّلُ الْمَلَائِكَةُ وَالرُّوحُ فِيهَا بِإِذْنِ رَبِّهِمْ مِنْ كُلِّ أَمْرٍ";

echo ar_gabung_huruf_mati(ar_hilangkan_tasydid(ar_hilangkan_spasi($ar_string)));

echo "\n\n";