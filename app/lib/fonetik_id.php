<?php

// mengodekan teks latin (lafadz) menjadi kode fonetik
// param  : $string lafadz dalam teks latin
// return : string kode fonetik
function id_fonetik($string, $tanpa_vokal = true) {
    
    // preproses : uppercase, jadikan spasi tunggal, ubah - jadi spasi, hilangkan semua karakter selain alphabet & ` & '
    $string = strtoupper($string);
    $string = preg_replace("/\s+/", " ", $string);
    $string = preg_replace("/\-/", " ", $string);
    $string = preg_replace("/[^A-Z`'\-\s]/", "", $string);
    
    // transformasi
    $string = id_substitusi_vokal($string);
    $string = id_gabung_konsonan($string);
    $string = id_gabung_vokal($string);
    $string = id_substitusi_diftong($string);
    $string = id_tandai_hamzah($string);
    $string = id_substitusi_ikhfa($string);
    $string = id_substitusi_iqlab($string);
    $string = id_substitusi_idgham($string);
    $string = id_fonetik_2_konsonan($string);
    $string = id_fonetik_1_konsonan($string);
    $string = id_hilangkan_spasi($string);
    if ($tanpa_vokal) $string = id_hilangkan_vokal ($string);
    
    return $string;
    
}

// substitusi vokal yang tidak ada pada arabic
// param  : $string lafadz dalam teks latin
// return : string vokal tersubstitusi
function id_substitusi_vokal($string) {
    
    $string = str_replace("O", "A", $string);
    $string = str_replace("E", "I", $string);
    
    return $string;
    
}

// peleburan konsonan yang sama yang berdampingan
// param  : $string lafadz dalam teks latin
// return : string tanpa konsonan sama berdampingan
function id_gabung_konsonan($string) {
    
    // gabung yang bergandengan
    $string = preg_replace("/(B|C|D|F|G|H|J|K|L|M|N|P|Q|R|S|T|V|W|X|Y|Z)\s?\\1+/", "$1", $string);

    // untuk yang 2 konsonan (KH, SH, dst)
    $string = preg_replace("/(KH|CH|SH|TS|SY|DH|TH|ZH|DZ|GH)\s?\\1+/", "$1", $string);
    
    return $string;
    
}

// peleburan vokal yang sama yang berdampingan
// param  : $string lafadz dalam teks latin
// return : string tanpa vokal sama berdampingan
function id_gabung_vokal($string) {
    
    // gabung yang bergandengan langsung
    $string = preg_replace("/(A|I|U|E|O)\\1+/", "$1", $string);
    
    return $string;
    
}

// substitusi diftong bahasa Arab
// param  : $string lafadz dalam teks latin
// return : string dengan diftong disesuaikan
function id_substitusi_diftong($string) {
    
    $string = str_replace("AI", "AY", $string);
    $string = str_replace("AU", "AW", $string);
    
    return $string;
    
}

// penandaan hamzah
// param  : $string lafadz dalam teks latin
// return : string dengan hamzah ditandai
function id_tandai_hamzah($string) {
    
    // setelah spasi atau di awal string
    $string = preg_replace("/^(A|I|U)/", " X$1", $string);
    $string = preg_replace("/\s(A|I|U)/", " X$1", $string);

    // IA, IU => IXA, IXU
    $string = preg_replace("/I(A|U)/", "IX$1", $string);

    // UA, UI => UXA, UXI
    $string = preg_replace("/U(A|I)/", "UX$1", $string);    
    
    return $string;
    
}

// substitusi huruf ikhfa (NG)
// param  : $string lafadz dalam teks latin
// return : string dengan huruf ikhfa disesuaikan
function id_substitusi_ikhfa($string) {
    
    // [vokal][NG][konsonan] => [vokal][N][konsonan]
    $string = preg_replace("/(A|I|U)NG\s?(D|F|J|K|P|Q|S|T|V|Z)/", "$1N$2", $string);

    return $string;
    
}

// substitusi huruf iqlab
// param  : $string lafadz dalam teks latin
// return : string dengan huruf iqlab disesuaikan
function id_substitusi_iqlab($string) {
    
    // NB => MB
    $string = preg_replace("/N\s?B/", "MB", $string);

    return $string;
    
}

// substitusi huruf idgham
// param  : $string lafadz dalam teks latin
// return : string dengan huruf idgham disesuaikan
function id_substitusi_idgham($string) {
    
    // pengecualian
    $string = str_replace("DUNYA", "DUN_YA", $string);
    $string = str_replace("BUNYAN", "BUN_YAN", $string);
    $string = str_replace("QINWAN", "KIN_WAN", $string);
    $string = str_replace("KINWAN", "KIN_WAN", $string);
    $string = str_replace("SINWAN", "SIN_WAN", $string);
    $string = str_replace("SHINWAN", "SIN_WAN", $string);
    
    // N,M,L,R,Y,W
    $string = preg_replace("/N\s?(N|M|L|R|Y|W)/", "$1", $string);

    // dikembalikan
    $string = str_replace("DUN_YA", "DUNYA", $string);
    $string = str_replace("BUN_YAN", "BUNYAN", $string);
    $string = str_replace("KIN_WAN", "KINWAN", $string);
    $string = str_replace("SIN_WAN", "SINWAN", $string);
    
    return $string;
    
}

// substitusi fonetik 2 konsonan
// param  : $string lafadz dalam teks latin
// return : kode fonetik string
function id_fonetik_2_konsonan($string) {
    
    $string = preg_replace("/KH|CH/", "H", $string);
    $string = preg_replace("/SH|TS|SY/", "S", $string);
    $string = preg_replace("/DH/", "D", $string);
    $string = preg_replace("/ZH|DZ/", "Z", $string);
    $string = preg_replace("/TH/", "T", $string);
    $string = preg_replace("/NG(A|I|U)/", "X$1", $string);  // mengatasi "ngalamin"
    $string = preg_replace("/GH/", "G", $string);
    
    return $string;
    
}

// substitusi fonetik 1 konsonan
// param  : $string lafadz dalam teks latin
// return : kode fonetik string
function id_fonetik_1_konsonan($string) {
    
    $string = preg_replace("/'|`/", "X", $string);
    $string = preg_replace("/Q|K/", "K", $string);
    $string = preg_replace("/F|V|P/", "F", $string);
    $string = preg_replace("/J|Z/", "Z", $string);
    
    return $string;
    
}

// menghilangkan spasi
// param  : $string lafadz dalam teks latin
// return : string tanpa spasi
function id_hilangkan_spasi($string) {
    
    return preg_replace("/\s/", "", $string);
    
}

// menghilangkan vokal
// param  : $string lafadz dalam teks latin
// return : string tanpa vokal
function id_hilangkan_vokal($string) {
    
    return preg_replace("/A|I|U/", "", $string);
    
}

/*
echo id_fonetik(
"INNAA LILLAAHI WAINNA ILAIHI ROOJI'UN MINAL JINNI WAL-ZAANIYATI",
false)
. "\n";
*/