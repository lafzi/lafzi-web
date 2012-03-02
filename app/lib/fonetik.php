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
function ar_fonetik($ar_string, $tanpa_harakat = true) {
    
   $ar_string = ar_hilangkan_spasi($ar_string);
   $ar_string = ar_hilangkan_tasydid($ar_string);
   $ar_string = ar_gabung_huruf_mati($ar_string);
   $ar_string = ar_akhir_ayat($ar_string);
   $ar_string = ar_substitusi_tanwin($ar_string);
   $ar_string = ar_hilangkan_mad($ar_string);
   $ar_string = ar_hilangkan_huruf_tidak_dibaca($ar_string);
   $ar_string = ar_substitusi_iqlab($ar_string);
   $ar_string = ar_substitusi_idgham($ar_string);
   if ($tanpa_harakat) $ar_string = ar_hilangkan_harakat($ar_string);
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
    
    $arr = ar_string_to_array($ar_string);
    $len = count($arr);
    
    if ($arr[$len-1] == ALIF || $arr[$len-1] == ALIF_MAQSURA) {
        // jika diakhiri alif / alif maqsura (tanpa harakat)
        // hapus karakter tersebut
        array_pop($arr);
        
    } else if($arr[$len-1] == FATHAH || $arr[$len-1] == KASRAH || $arr[$len-1] == DHAMMAH ||
              $arr[$len-1] == KASRATAIN || $arr[$len-1] == DHAMMATAIN) {
        // jika diakhiri tanda vokal / tanwin (kecuali fathatain)
        // ganti dengan sukun
        $arr[$len-1] = SUKUN;
    }
    
    // hitung ulang seandainya di atas tadi ada yang dihapus
    $len = count($arr);
    
    if ($arr[$len-1] == FATHATAIN) {
        // jika harakat terakhir fathatain
        // ganti dengan fathah
        $arr[$len-1] = FATHAH;
    }    
    
    if ($arr[$len-2] == TA_MARBUTAH) {
        // jika huruf terakhir ta marbutah, ganti dengan ha
        $arr[$len-2] = HHA;
    }    
    
    // alif di awal
    if ($arr[0] == ALIF) {
        array_shift($arr);
        array_unshift($arr, FATHAH);
        array_unshift($arr, HAMZAH_ALIF_A);
    }
    
    return ar_array_to_string($arr);
    
}

// mensubstitusi tanwin
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan tanwin diganti
function ar_substitusi_tanwin($ar_string) {
    
    // menggunakan regex
    $ar_string = mb_ereg_replace(FATHATAIN, FATHAH.NUN.SUKUN, $ar_string);    
    $ar_string = mb_ereg_replace(KASRATAIN, KASRAH.NUN.SUKUN, $ar_string);    
    $ar_string = mb_ereg_replace(DHAMMATAIN, DHAMMAH.NUN.SUKUN, $ar_string);    

    return $ar_string;
    
}

// menghilangkan mad
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan mad dihilangkan
function ar_hilangkan_mad($ar_string) {

    $arr = ar_string_to_array($ar_string);
    $len = count($arr);    
    $str = "";
    
    for ($i = 0; $i < $len; $i++) {
        
        $curr = $arr[$i];
        $next1 = isset($arr[$i+1]) ? $arr[$i+1] : $arr[$i];
        $next2 = isset($arr[$i+2]) ? $arr[$i+2] : $arr[$i];
        
        if (
           ($curr == FATHAH && ($next1 == ALIF || $next1 == ALIF_MAQSURA) && ($next2 != FATHAH && $next2 != KASRAH && $next2 != DHAMMAH))
           || 
           ($curr == KASRAH && ($next1 == YA) && ($next2 != FATHAH && $next2 != KASRAH && $next2 != DHAMMAH))
           || 
           ($curr == DHAMMAH && ($next1 == WAU) && ($next2 != FATHAH && $next2 != KASRAH && $next2 != DHAMMAH))
           ) 
           {
            // jika syarat terpenuhi
            // skip saja
            $str .= $arr[$i];
            $i += 2;
            $str .= $arr[$i];
        } else {
            $str .= $arr[$i];
        }

    }
    
    // ganti alif madd
    $str = mb_ereg_replace(ALIF_MAD, HAMZAH_ALIF_A.FATHAH, $str);
    
    return $str;
    
}

// menghilangkan huruf tidak dibaca
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan huruf tidak dibaca dihilangkan
function ar_hilangkan_huruf_tidak_dibaca($ar_string) {

    $arr = ar_string_to_array($ar_string);
    $str = "";
    
    for ($i = 0; $i < count($arr); $i++) {
        
        $curr = $arr[$i];
        $next = isset($arr[$i+1]) ? $arr[$i+1] : $arr[$i];
        
        if (ar_huruf($curr) && ar_huruf($next)) {
            // jika yang sekarang adalah huruf dan selanjutnya adalah huruf juga
            // maka yang sekarang tidak bertanda
            // maka buang saja
            $str .= $next;
            $i++;            
        } else {
            $str .= $curr;
        }
        
    }

    $arr = ar_string_to_array($str);
    $str = "";
    
    // 2 kali untuk antisipasi huruf tidak dibaca dobel
    
    for ($i = 0; $i < count($arr); $i++) {
        
        $curr = $arr[$i];
        $next = isset($arr[$i+1]) ? $arr[$i+1] : $arr[$i];
        
        if (ar_huruf($curr) && ar_huruf($next)) {
            // jika yang sekarang adalah huruf dan selanjutnya adalah huruf juga
            // maka yang sekarang tidak bertanda
            // maka buang saja
            $str .= $next;
            $i++;            
        } else {
            $str .= $curr;
        }
        
    }    
    
    return $str;    
    
}

// mensubstitusi huruf iqlab
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan huruf iqlab disesuaikan
function ar_substitusi_iqlab($ar_string) {
    
    // dengan regex
    return mb_ereg_replace(NUN.SUKUN.BA, MIM.SUKUN.BA, $ar_string);        
    
}

// mensubstitusi huruf idgham
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic dengan huruf idgham disesuaikan
function ar_substitusi_idgham($ar_string) {
    
    $ar_string = mb_ereg_replace(NUN.SUKUN.NUN, NUN, $ar_string);    
    $ar_string = mb_ereg_replace(NUN.SUKUN.MIM, MIM, $ar_string);    
    $ar_string = mb_ereg_replace(NUN.SUKUN.LAM, LAM, $ar_string);    
    $ar_string = mb_ereg_replace(NUN.SUKUN.RA, RA, $ar_string);    

    // pengecualian
    $ar_string = mb_ereg_replace("دُنْي", "DUNYA", $ar_string);
    $ar_string = mb_ereg_replace("بُنْيَن", "BUNYAN", $ar_string);
    $ar_string = mb_ereg_replace("صِنْوَن", "SINWAN", $ar_string);
    $ar_string = mb_ereg_replace("قِنْوَن", "QINWAN", $ar_string);
    
    $ar_string = mb_ereg_replace(NUN.SUKUN.YA, YA, $ar_string);    
    $ar_string = mb_ereg_replace(NUN.SUKUN.WAU, WAU, $ar_string);

    // dikembalikan lagi
    $ar_string = mb_ereg_replace("DUNYA", "دُنْي", $ar_string);    
    $ar_string = mb_ereg_replace("BUNYAN", "بُنْيَن", $ar_string);
    $ar_string = mb_ereg_replace("SINWAN", "صِنْوَن", $ar_string);
    $ar_string = mb_ereg_replace("QINWAN", "قِنْوَن", $ar_string);
    
    return $ar_string;
    
}

// menghilangkan harakat
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string arabic tanpa harakat
function ar_hilangkan_harakat($ar_string) {

    $ar_string = mb_ereg_replace(FATHAH, "", $ar_string);    
    $ar_string = mb_ereg_replace(KASRAH, "", $ar_string);    
    $ar_string = mb_ereg_replace(DHAMMAH, "", $ar_string);    
    $ar_string = mb_ereg_replace(SUKUN, "", $ar_string); 
    
    return $ar_string;
    
}

// mensubstitusi kode fonetik
// param  : $ar_string : string teks Al-Quran (arabic)
// return : string latin (kode fonetik)
function ar_fonetik_encode($ar_string) {
    
    $arr = ar_string_to_array($ar_string);
    $str = "";
    
    $map = array(
        JIM => "Z",
        ZA  => "Z",
        HHA => "H",
        KHA => "H",
        HA  => "H",
        HAMZAH         => "X",
        HAMZAH_ALIF_A  => "X",
        HAMZAH_ALIF_I  => "X",
        HAMZAH_MAQSURA => "X",
        HAMZAH_WAU     => "X",
        ALIF           => "X",
        AIN            => "X",
        SHAD => "S",
        TSA  => "S",
        SYIN => "S",
        SIN  => "S",
        ZHA  => "D",
        DHAD => "D",
        DZAL => "D",
        DAL  => "D",
        TA_MARBUTAH  => "T",
        TA           => "T",
        THA          => "T",
        QAF  => "K",
        KAF  => "K",
        GHAIN => "G",
        FA  => "F",
        MIM => "M",
        NUN => "N",
        LAM => "L",
        BA  => "B",
        YA  => "Y",
        WAU => "W",
        RA  => "R",
        
        FATHAH  => "A",
        KASRAH  => "I",
        DHAMMAH => "U",
        SUKUN   => ""
    );
    
    for ($i = 0; $i < count($arr); $i++) {
        
        $char = $arr[$i];
        $str .= $map[$char];
        
    }
    
    return $str;
    
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

// mengecek suatu karakter huruf atau bukan
// param  : $ar_char karakter arabic
// output : boolean
function ar_huruf($ar_char) {
    if ($ar_char == FATHAH || $ar_char == KASRAH || $ar_char == DHAMMAH || $ar_char == FATHATAIN || $ar_char == KASRATAIN || $ar_char == DHAMMATAIN || $ar_char == SUKUN || $ar_char == SYADDAH)
        return false;
    else
        return true;
}

/* test suite 

$ar_string = "اعْلَمُوا أَنَّمَا الْحَيَاةُ الدُّنْيَا لَعِبٌ وَلَهْوٌ وَزِينَةٌ وَتَفَاخُرٌ بَيْنَكُمْ وَتَكَاثُرٌ فِي الْأَمْوَالِ وَالْأَوْلَادِ كَمَثَلِ غَيْثٍ أَعْجَبَ الْكُفَّارَ نَبَاتُهُ ثُمَّ يَهِيجُ فَتَرَاهُ مُصْفَرًّا ثُمَّ يَكُونُ حُطَامًا وَفِي الْآخِرَةِ عَذَابٌ شَدِيدٌ وَمَغْفِرَةٌ مِنَ اللَّهِ وَرِضْوَانٌ وَمَا الْحَيَاةُ الدُّنْيَا إِلَّا مَتَاعُ الْغُرُورِ";

echo "\n ";
echo ar_fonetik($ar_string, false);

echo "\n\n";

 */

// tambahan untuk highlighting hasil pencarian

function ar_reduksi($ar_string, $hilangkan_vokal = false) {
    
   $ar_string = ar_hilangkan_spasi($ar_string);
   $ar_string = ar_hilangkan_tasydid($ar_string);
   $ar_string = ar_gabung_huruf_mati($ar_string);
   $ar_string = ar_akhir_ayat($ar_string);
   $ar_string = ar_substitusi_tanwin($ar_string);
   $ar_string = ar_hilangkan_mad($ar_string);
   $ar_string = ar_hilangkan_huruf_tidak_dibaca($ar_string);
   $ar_string = ar_substitusi_iqlab($ar_string);
   $ar_string = ar_substitusi_idgham($ar_string);

   $ar_string = mb_ereg_replace(SUKUN, "", $ar_string);
   if ($hilangkan_vokal) $ar_string = ar_hilangkan_harakat($ar_string);
   
   return $ar_string;
   
}

/* kita cobain...
 
$ar = 
"قَالُوا تَاللَّهِ تَفْتَأُ تَذْكُرُ يُوسُفَ حَتَّى تَكُونَ حَرَضًا أَوْ تَكُونَ مِنَ الْهَالِكِينَ"
;

//echo $ar . "\n";
echo ar_reduksi($ar) . "\n";
echo ar_fonetik($ar, false) . "\n\n";

$k = ar_string_to_array(ar_reduksi($ar));

echo count($k) . "\n";
echo strlen(ar_fonetik($ar, false)) . "\n";
*/

// comparer
function cmp_ph($r, $a) {
    
    return
        ($r == $a)
        ||
        ($r == DHAMMAH && $a == DHAMMATAIN)     // para tanwin
        ||
        ($r == KASRAH && $a == KASRATAIN)
        ||
        ($r == FATHAH && $a == FATHATAIN)
        ||
        ($r == HAMZAH_ALIF_A && $a == ALIF_MAD)    // buat alif madda
        ||
        ($r == MIM && $a == NUN)    // buat iqlab
    ;
    
}

// ok, sudah sama, mari kita lanjut
// memetakan posisi di string reduksi ke posisi di string asli
function map_reduksi_ke_asli($str_asli, $hilangkan_vokal = false) {
    
    // catatan : tanwin jadi nun harus di-invers
    
    $str_reduksi = ar_reduksi($str_asli, $hilangkan_vokal);
    
    $reduksi = ar_string_to_array($str_reduksi);
    $asli = ar_string_to_array($str_asli);
    
    $pos = array();
    $len_red = count($reduksi);
    $len_asli = count($asli);
    
    $j = 0;
    
    // untuk semua elemen reduksi
    // i = pointer array reduksi
    // j = pointer array asli
    for ($i = 0; $i < $len_red; $i++) {
        if ($asli[$j] == ALIF) { // kalau alif di depan
            $pos[$i] = $j;
            $pos[$i+1] = $j;
            $i+=2;
        }
        while (!cmp_ph($reduksi[$i], $asli[$j]) && $j < $len_asli) {
            if ($asli[$j] == DHAMMATAIN || $asli[$j] == KASRATAIN || $asli[$j] == FATHATAIN || $asli[$j] == ALIF_MAD) { // skip pointer buat tanwin dan alif madda
                $pos[$i] = $j;
                $i++;
            }
            $j++;
        }
        $pos[$i] = $j;
    }
    
    return $pos;
    
}

// merge posisi trigram untuk higlight
/* misalnya begini
 *      
 *      0, 0, 1, 3, 4, 5, 6, 7, 20, 22, 24
 * 
 * di-lookforward sepanjang mungkin selama masuk 3 sekuens untuk higlight, jadi
 * 
 *       0 :  7+2
 *      20 : 24+2
 * 
 */
function longest_highlight_lookforward($hl_sequence, $min_length = 3) {

    $len = count($hl_sequence);
    if ($len == 1) return array(array($hl_sequence[0], $hl_sequence[0] + $min_length));
    
    sort($hl_sequence);
    
    $res = array();
    $j = 1;
    
    for ($i=0; $i<$len; $i++) {        
        while (isset($hl_sequence[$j]) && $hl_sequence[$j] - $hl_sequence[$j-1] <= $min_length+1 && $j < $len) {
            $j++;
        }
        $res[] = array($hl_sequence[$i], $hl_sequence[$j-1]);
        $i = $j-1;
        $j++;
    }
    
    return $res;
}

// print_r(longest_highlight_lookforward(array(1, 2, 36, 4, 5, 6, 7)));

/*

$ar = 

"الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ
"

;

header("Content-Type: text/html;charset=UTF-8");

echo "<table style='font-size: 20px'>";
echo "<tr>";
echo "<td valign='top' width='10%'><pre>";
print_r(map_reduksi_ke_asli($ar, true));
echo "</pre></td>";
echo "<td valign='top' width='10%'><pre>";
print_r(ar_string_to_array(ar_reduksi($ar, true)));
echo "</pre></td>";
echo "<td valign='top' width='10%'><pre>";
print_r(ar_string_to_array($ar));
echo "</pre></td>";
echo "</tr>";
echo "</table>";

*/