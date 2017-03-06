<?php

error_reporting(0);

// keycheck

$magic_number = 577;
$key = (int) $_GET['key'];

if ($key % $magic_number != 0)
    exit;
if (!(isset($_GET['q']) && $_GET['q'] != ""))
    exit;

// start =======================================================================

$order = true;
$vowel = true;

$filtered = true;

include '../search/search_ff.php';
include '../lib/fonetik_id.php';
include '../lib/fonetik.php';

$query = $_GET['q'];

$query_final = id_fonetik($query, !$vowel);

$term_list_filename = "../data/index_termlist_vokal.txt";
$post_list_filename = "../data/index_postlist_vokal.txt";

$quran_text = file("../data/quran_teks.txt", FILE_IGNORE_NEW_LINES);

$quran_text_muqathaat = file("../data/quran_muqathaat.txt", FILE_IGNORE_NEW_LINES);
$quran_text_muqathaat_map = array();

foreach ($quran_text_muqathaat as $line) {
    list ($no_surah, $nama_surah, $no_ayat, $teks) = explode('|', $line);
    $quran_text_muqathaat_map[$no_surah][$no_ayat] = $teks;
}

// sistem cache

$cache_file = "../cache/" . $query_final;
if ($order)
    $cache_file .= "_o";
if ($filtered)
    $cache_file .= "_f";

if (file_exists($cache_file)) {
    // read from cache
    $cf = fopen($cache_file, "r");
    exec("touch " . $cache_file);

    $matched_docs = unserialize(fgets($cf));
    fclose($cf);

    $from_cache = true;
} else {

    // do actual search
    // pertama dengan threshold 0.8
    $th = 0.8;
    $matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, $filtered, $th);

    // jika ternyata tanpa hasil, turunkan threshold jadi 0.7
    if (count($matched_docs) == 0) {
        $th = 0.7;
        $matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, $filtered, $th);
    }

    // jika ternyata tanpa hasil, turunkan threshold jadi 0.6
    if (count($matched_docs) == 0) {
        $th = 0.6;
        $matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, $filtered, $th);
    }

    // jika masih tanpa hasil, ya sudah
    //$matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, false, 1);

    if (count($matched_docs) > 0) {

        // penandaan posisi untuk highlight
        // baca file posisi mapping
        $mapping_data = file('../data/mapping_posisi_vokal.txt', FILE_IGNORE_NEW_LINES);

        foreach ($matched_docs as $doc) {

            list(,,, $doc_text) = explode('|', $quran_text[$doc->id - 1]);
            $doc_text = ar_string_to_array($doc_text);

            // memetakan posisi kemunculan untuk highlighting
            $posisi_real = array();
            $posisi_hilight = array();
            $map_posisi = explode(',', $mapping_data[$doc->id - 1]);
            $seq = array();

            // pad by 3
            foreach (array_values($doc->matched_terms) as $pos) {
                $seq[] = $pos;
                $seq[] = $pos + 1;
                $seq[] = $pos + 2;
            }
            $seq = array_unique($seq);
            foreach ($seq as $pos) {
                $posisi_real[] = intval($map_posisi[$pos - 1]);
            }

            if ($vowel)
                $doc->highlight_positions = longest_highlight_lookforward($posisi_real, 3);
            else
                $doc->highlight_positions = longest_highlight_lookforward($posisi_real, 6);

            // penambahan bobot jika penandaan berakhir pada karakter spasi
            $end_pos = end($doc->highlight_positions);
            $end_pos = $end_pos[1];

            if ($doc_text[$end_pos + 1] == ' ' || !isset($doc_text[$end_pos + 1]))
                $doc->score += 0.001;
            else if ($doc_text[$end_pos + 2] == ' ' || !isset($doc_text[$end_pos + 2]))
                $doc->score += 0.001;
            else if ($doc_text[$end_pos + 3] == ' ' || !isset($doc_text[$end_pos + 3]))
                $doc->score += 0.001;
        }

        // diurutkan 
        usort($matched_docs, 'matched_docs_cmp');

        // write to cache
        $cf = fopen($cache_file, "w");
        fwrite($cf, serialize($matched_docs));
        fclose($cf);

        $from_cache = false;

        // clean cache except 50 newest; linux only
        $old_caches = array();
        exec("ls -t ../cache/ | sed -e '1,50d'", $old_caches);

        if (count($old_caches) > 0) {
            foreach ($old_caches as $old_cache) {
                unlink("../cache/" . $old_cache);
            }
        }
    }
}

$num_doc_found = count($matched_docs);

// paging
if (isset($_GET['page']))
    $page = intval($_GET['page']);
else
    $page = 1;

$limit_per_page = 10;
$num_pages = ceil($num_doc_found / $limit_per_page) + 1;

// =============================================================================

$search_result = array();
$response = array();

for ($i = ($page - 1) * $limit_per_page; $i < ($page - 1) * $limit_per_page + $limit_per_page; $i++) {

    if (isset($matched_docs[$i])) {

        $doc = $matched_docs[$i];

        list($d_no_surat, $d_nama_surat, $d_no_ayat, $d_isi_teks) = explode('|', $quran_text[$doc->id - 1]);

        $search_result[] = array(
            'hl' => $doc->highlight_positions,
            'ns' => $d_nama_surat,
            'na' => (int) $d_no_ayat,
            'tx' => $d_isi_teks
        );
    }
}

$response['num_result'] = $num_doc_found;
$response['result'] = $search_result;

header('Content-Type: application/json');

echo my_json_encode($response);

// tambahan ====================================================================

function my_json_encode($arr) {
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) {
                if (is_string($item))
                    $item = mb_encode_numericentity($item, array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
            });
    return mb_decode_numericentity(json_encode($arr), array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
}

