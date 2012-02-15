<?php

// visit log
$lf = fopen("../log.txt", "a");
$ls = date("Y-m-d H:i:s") . " : From " . $_SERVER['REMOTE_ADDR'] . " using " . $_SERVER['HTTP_USER_AGENT'] . "\n";
fwrite($lf, $ls);
fclose($lf);

if (isset($_GET['q']) && $_GET['q'] != "") {

    $order = ($_GET['order'] == 'on');
    $vowel = ($_GET['vowel'] == 'on');
    
    include '../search/search_ff.php';
    include '../lib/fonetik_id.php';

    // profiling
    $time_start = microtime(true);

    $query = $_GET['q'];

    $query_final = id_fonetik($query, !$vowel);
    $query_trigrams_count = strlen($query_final) - 2;

    if ($vowel) {
        $term_list_filename = "../data/index_termlist_vokal.txt";
        $post_list_filename = "../data/index_postlist_vokal.txt";
    } else {
        $term_list_filename = "../data/index_termlist_nonvokal.txt";
        $post_list_filename = "../data/index_postlist_nonvokal.txt";
    }
    
    $cache_file = "../cache/" . $query_final;
    if ($order) $cache_file .= "_";
    
    if (file_exists($cache_file)) {
        // read from cache
        $cf = fopen($cache_file, "r");
        
        $matched_docs = unserialize(fgets($cf));
        fclose($cf);
        
        $from_cache = true;
        
    } else {
        // do actual search
        $matched_docs = & search($query_final, $term_list_filename, $post_list_filename, $order); // using ff
        
        // write to cache
        $cf = fopen($cache_file, "w");
        fwrite($cf, serialize($matched_docs));
        fclose($cf);

        $from_cache = false;    
        
        // clean cache except 50 newest; linux only
        $old_caches = array();
        exec("ls -t ../cache/ | sed -e '1,50d'", $old_caches);
        
        if (count($old_caches) > 0)
            foreach ($old_caches as $old_cache) {
                unlink("../cache/" . $old_cache);
            }
    }
    

    $num_doc_found = count($matched_docs);
    $quran_text = file("../data/quran_teks.txt", FILE_IGNORE_NEW_LINES);

    // paging
    if (isset($_GET['page'])) 
        $page = intval($_GET['page']);
    else
        $page = 1;
    
    $limit_per_page = 10;
    $num_pages = ceil($num_doc_found / $limit_per_page) + 1;
    
    // hasil profiling waktu eksekusi
    $time_end = microtime(true);
    $time = $time_end - $time_start;

}

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Pencarian</title>
    </head>
    <body>
        <h3>Pencarian fonetik (<em>lafadz</em>) Al-Quran</h3>
        <div style="background-color: #CCCCCC; padding: 10px; margin-bottom: 20px;">
            <form action="" method="get">
                Cari : <input type="text" name="q" size="40" value="<?php if (isset($_GET['q'])) echo $_GET['q'] ?>"/>
                <input type="submit" value="  Cari  "/>
                <p>Pilihan</p>
                <div>
                    <input type="checkbox" id="os" name="order" <?php if(isset($order) && $order == true) echo 'checked="checked"' ?>/>
                    <label for="os"><em>Ranking</em> keterurutan <em>term</em></label>
                </div>
                <div>
                    <input type="checkbox" id="vw" name="vowel" <?php if(isset($vowel) && $vowel == true) echo 'checked="checked"' ?>/>
                    <label for="vw">Perhitungkan huruf vokal</label>
                </div>
            </form>
        </div>
        <?php if (isset($_GET['q']) && $_GET['q'] != "") : ?>
        <div style="background-color: #EEEEEE; padding: 10px;">
            <strong>Hasil pencarian</strong><br/>
            <table>
                <tr>
                    <td>Query</td>
                    <td>: <?php echo $query ?></td>
                </tr>
                <tr>
                    <td>Kode fonetik</td>
                    <td>: <?php echo $query_final ?></td>
                </tr>
                <tr>
                    <td>Jumlah trigram query</td>
                    <td>: <?php echo $query_trigrams_count ?></td>
                </tr>
                <tr>
                    <td>Ditemukan</td>
                    <td>: <?php echo $num_doc_found ?></td>
                </tr>
            </table>

            <div style="background-color: #CCCCCC; padding: 10px; margin-bottom: 10px; text-align: center">
                Halaman : 
                <!-- TODO : secure this -->
                <input type="button" value="Sebelumnya" onclick="window.location = '<?php echo "?q=" . urlencode($_GET['q']) . "&order={$_GET['order']}&vowel={$_GET['vowel']}&page=" . ($page-1) ?>'" <?php if($page==1) echo 'disabled="disabled"' ?>/>
                <select name="page"  onchange='window.location = "<?php echo "?q=" . urlencode($_GET['q']) . "&order={$_GET['order']}&vowel={$_GET['vowel']}&page=" ?>" + this.value'>
                    <?php for ($p = 1; $p < $num_pages; $p++) : ?>
                    <option value="<?php echo $p ?>" <?php if($p == $page) echo 'selected="selected"' ?>><?php echo $p ?></option>
                    <?php endfor; ?>
                </select>
                <input type="button" value="Selanjutnya" onclick="window.location = '<?php echo "?q=" . urlencode($_GET['q']) . "&order={$_GET['order']}&vowel={$_GET['vowel']}&page=" . ($page+1) ?>'" <?php if($page==$num_pages-1) echo 'disabled="disabled"' ?>/>
            </div>
            
            <?php
            
                for ($i = ($page-1)*$limit_per_page; $i < ($page-1)*$limit_per_page + $limit_per_page; $i++) {

                    if (isset($matched_docs[$i])) {
                    
                        echo '<div style="background-color: #CCCCCC; padding: 10px; margin-bottom: 10px;">';

                        $doc = $matched_docs[$i];
                        if ($order)
                            echo ($i + 1) . ". Dokumen #{$doc->id} (jumlah trigram cocok : {$doc->matched_trigrams_count}; skor jumlah trigram : ".round($doc->matched_terms_count_score,2) ."; skor keterurutan : ".round($doc->matched_terms_order_score,2)."; skor kedekatan : ".round($doc->matched_terms_contiguity_score,2).";  skor total : ".round($doc->score, 2).")\n";
                        else
                            echo ($i + 1) . ". Dokumen #{$doc->id} (jumlah trigram cocok : {$doc->matched_trigrams_count}; skor jumlah trigram : ".round($doc->matched_terms_count_score,2) ."; skor total : ".round($doc->score, 2).")\n";

                        echo "<br/>";

                        $doc_data = explode('|', $quran_text[$doc->id - 1]);

                        echo '<small><br/>';                    
                        echo "<em>";
                        echo "Surat {$doc_data[1]} ({$doc_data[0]}) ayat {$doc_data[2]}\n";
                        echo "</em><br/>";

                        echo "Posisi kemunculan : ".  implode(',', array_values($doc->matched_terms))."\n\n";

                        if ($order)
                            echo "<br/>LIS : ".  implode(',', $doc->LIS)."\n\n";

                        echo '</small>';

                        echo "<br/><br/>";
                        echo '<div style="text-align: right; font-size: 25px">';
                        echo $doc_data[3] . "\n\n";
                        echo '</div>';

                        echo '</div>';
                        
                    }
                }            
            
            ?>
            
            <div style="background-color: #CCCCCC; padding: 10px; margin-bottom: 10px; text-align: center">
                Halaman : 
                <!-- TODO : secure this -->
                <input type="button" value="Sebelumnya" onclick="window.location = '<?php echo "?q=" . urlencode($_GET['q']) . "&order={$_GET['order']}&vowel={$_GET['vowel']}&page=" . ($page-1) ?>'" <?php if($page==1) echo 'disabled="disabled"' ?>/>
                <select name="page"  onchange='window.location = "<?php echo "?q=" . urlencode($_GET['q']) . "&order={$_GET['order']}&vowel={$_GET['vowel']}&page=" ?>" + this.value'>
                    <?php for ($p = 1; $p < $num_pages; $p++) : ?>
                    <option value="<?php echo $p ?>" <?php if($p == $page) echo 'selected="selected"' ?>><?php echo $p ?></option>
                    <?php endfor; ?>
                </select>
                <input type="button" value="Selanjutnya" onclick="window.location = '<?php echo "?q=" . urlencode($_GET['q']) . "&order={$_GET['order']}&vowel={$_GET['vowel']}&page=" . ($page+1) ?>'" <?php if($page==$num_pages-1) echo 'disabled="disabled"' ?>/>
            </div>            
            
            <p>
                <?php
                    echo "\nPencarian dalam $time detik ";
                    echo ($from_cache) ? '[cache hit]' : '[cache miss]';
                    echo  "<br/>";
                    echo "Memory usage      : " . memory_get_usage() . " bytes<br/>";
                    echo "Memory peak usage : " . memory_get_peak_usage() . " bytes<br/>";
                ?>
            </p>
            
        </div>
        <?php endif; ?>
    </body>
</html>
