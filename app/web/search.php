<?php

// visit log
// $lf = fopen("../log.txt", "a");
// $ls = date("Y-m-d H:i:s") . " : From " . $_SERVER['REMOTE_ADDR'] . " using " . $_SERVER['HTTP_USER_AGENT'] . "\n";
// fwrite($lf, $ls);
// fclose($lf);

if (isset($_GET['q']) && $_GET['q'] != "") {

    //if (isset($_GET['order'])) {
	//$order = ($_GET['order'] == 'on');
    //}
    //else {
	//$order = 0;
    //}
	$order = true;

    if (isset($_GET['vowel'])) {
	$vowel = ($_GET['vowel'] == 'on');
    }
    else {
	$vowel = 0;
    }
    
    $verbose  = isset($_GET['debug']);
    $filtered = !isset($_GET['all']);
    
    include '../search/search_ff.php';
    include '../lib/fonetik_id.php';
    include '../lib/fonetik.php';

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
    
    // baca data teks quran untuk ditampilkan
    
    $quran_text = file("../data/quran_teks.txt", FILE_IGNORE_NEW_LINES);
    $quran_trans = file("../data/trans-indonesian.txt", FILE_IGNORE_NEW_LINES);
    
    // khusus ayat dengan fawatihussuwar
    
    $quran_text_muqathaat = file("../data/quran_muqathaat.txt", FILE_IGNORE_NEW_LINES);
    $quran_text_muqathaat_map = array();
    
    foreach ($quran_text_muqathaat as $line) {
        list ($no_surah, $nama_surah, $no_ayat, $teks) = explode('|', $line);
        $quran_text_muqathaat_map[$no_surah][$no_ayat] = $teks;
    }    
    
    // sistem cache
    
    $cache_file = "../cache/" . $query_final;
    if ($order) $cache_file .= "_o";
    if ($filtered) $cache_file .= "_f";
    
    if (file_exists($cache_file)) {
    //if (false) { // deactivate cache
        // read from cache
        $cf = fopen($cache_file, "r");
        exec("touch " . $cache_file);
        
        $matched_docs = unserialize(fgets($cf));
        fclose($cf);
        
        $from_cache = true;
        
    } else {
        // do actual search
        
        // pertama dengan threshold 0.8
        $th = 0.95; //0.8;
        $matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, $filtered, $th); 

        // jika ternyata tanpa hasil, turunkan threshold jadi 0.7
        if(count($matched_docs) == 0) {
            $th = 0.8; //0.7;
            $matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, $filtered, $th); 
        }

        // jika ternyata tanpa hasil, turunkan threshold jadi 0.6
        if(count($matched_docs) == 0) {
            $th = 0.7; //0.6;
            $matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, $filtered, $th); 
        }
        
        // jika masih tanpa hasil, ya sudah
                
        //$matched_docs = search($query_final, $term_list_filename, $post_list_filename, $order, false, 1);
        
        if(count($matched_docs) > 0) {
        
            // penandaan posisi untuk highlight

            // baca file posisi mapping
            if ($vowel)
                $mapping_data = file('../data/mapping_posisi_vokal.txt', FILE_IGNORE_NEW_LINES);
            else 
                $mapping_data = file('../data/mapping_posisi.txt', FILE_IGNORE_NEW_LINES);

            foreach ($matched_docs as $doc) {

                list(,,,$doc_text) = explode('|', $quran_text[$doc->id - 1]);
                $doc_text = ar_string_to_array($doc_text);

                // memetakan posisi kemunculan untuk highlighting
                $posisi_real = array();
                $posisi_hilight = array();
                $map_posisi = explode(',', $mapping_data[$doc->id - 1] );
                $seq = array();

                // pad by 3
                foreach ($doc->LIS as $pos) {
                    $seq[] = $pos;
                    $seq[] = $pos+1;
                    $seq[] = $pos+2;
                }
                $seq = array_unique($seq);
                foreach ($seq as $pos) {
                    $posisi_real[] = $map_posisi[$pos-1];
                }

                if ($vowel) 
                    $doc->highlight_positions = longest_highlight_lookforward($posisi_real, 6);
                else 
                    $doc->highlight_positions = longest_highlight_lookforward($posisi_real, 6);

                // penambahan bobot jika penandaan berakhir pada karakter spasi
                $end_pos = end($doc->highlight_positions);
                $end_pos = $end_pos[1];

                if ($doc_text[$end_pos+1] == ' ' || !isset($doc_text[$end_pos+1])) $doc->score += 0.001;
                else if (!isset($doc_text[$end_pos+2]) || $doc_text[$end_pos+2] == ' ') $doc->score += 0.001;
                else if (!isset($doc_text[$end_pos+3]) || $doc_text[$end_pos+3] == ' ') $doc->score += 0.001;

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
    
    // hasil profiling waktu eksekusi
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Lafzi - Hasil Pencarian</title>
        <link rel="stylesheet" type="text/css" href="res/hilight.css"/>
        <link rel="shortcut icon" href="res/img/favicon.ico" type="image/x-icon" />
        <link href="res/main.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="res/jquery.1.7.js"></script>        
        <script type="text/javascript" src="res/hilight.js"></script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-41627176-1', 'ipb.ac.id');
  ga('send', 'pageview');

</script>

    </head>
    <body>
        <div class="overlay" id='copy-overlay' style="display: none;">
            <div class="modalDialog" id='copy-dialog' style="display: none;">
                <p style="color: #666;">Salin dengan menekan CTRL+C pada <em>keyboard</em>.</p>
                <textarea id="copy-text" readonly="readonly"></textarea>
                <input type="button" class="graybtn" value="Selesai" onclick="hideCopyDialog();" style="color: #666666; font-size: 10px; padding: 2px 5px; float: right; margin-top: 10px;"/>
                <div style="clear: both"></div>
            </div>
        </div>
        <!-- <?php /*echo $th*/ ?>  -->
        <div id="main-wrap" class="bg-dots-light">
            <div id="main">    
                
                <div id="header">
                    <a href="./">
                    <img src="res/img/logo-s.png" alt="Lafzi" id="logo-small" width="124" height="54"/>
                    </a>

                    <form action="" method="get" id="srp-search-form">

                        <div id="search-form-container">
                            <input type="text" name="q" id="search-box" value="<?php if (isset($_GET['q'])) echo $_GET['q'] ?>" autocomplete="off"/><input type="submit" value="Cari" id="search-submit"/>
                        </div>

                        <div id="search-options-container">
                            <input type="button" class="search-option" value="Bantuan &raquo;" id="button-help"/>
                            <input type="button" class="search-option" value="Pengaturan &raquo;" id="button-option" title="Pengaturan tambahan"/>
                            <div id="search-checkboxes">
                                <?php /*<input type="checkbox" id="os" name="order" <?php if(isset($order) && $order == true) echo 'checked="checked"' ?>/>
                                <label for="os">Perhitungkan keterurutan</label>*/ ?>
                                <input type="checkbox" id="vw" name="vowel" <?php if(isset($vowel) && $vowel == true) echo 'checked="checked"' ?>/>
                                <label for="vw">Perhitungkan huruf vokal</label>
                            </div>                        
                        </div>

                    </form>

                    <br style="clear: both"/>

                    <div id="search-help-box" style="position: absolute; left : 180px; width: 500px; z-index: 200">
                        Ketikkan potongan ayat atau lafaz dalam Al-Quran (tidak harus benar cara penulisannya), contoh:

                        <ul>
                            <li>alhamdulillahi rabbil-'alamin</li>
                            <li>innalloha ma'a shoobiriin</li>
                            <li>laa ilaaha illallaah</li>
                            <li>kun fayakuun</li>
                        </ul>

                        Tips: Gunakan spasi untuk pemisah antar kata agar lebih akurat.
                    </div>
                </div>
                
                <?php if (isset($_GET['q']) && $_GET['q'] != "") : ?>
                    <div id="srp-header">
                        <h3>Hasil Pencarian (<?php echo number_format($num_doc_found) ?> hasil)</h3>
                        <?php if($num_doc_found > 0) : ?>
                        <div id="hl-switch" title="Tampilkan sorotan pada bagian yang kira-kira cocok">
                            <input type="checkbox" id="cx_tr" checked="checked" onchange="if(this.checked == true) showTrans(); else hideTrans();"/>
                            <label for="cx_tr">Tampilkan terjemahan</label>
                            <input type="checkbox" id="hl1" checked="checked" onchange="if(this.checked == true) showHilight(); else hideHilight();"/>
                            <label for="hl1">Tampilkan sorotan</label>
                        </div>
                        <?php endif; ?>
                        <br style="clear: both"/>

                        <?php if($verbose) : ?>
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
                        <?php endif; ?>
                        
                    </div>

                    <div id="srb-container">
                    <?php
                        $max_score = $query_trigrams_count;

                        $js_hl_functions = "";
                        
                        for ($i = ($page-1)*$limit_per_page; $i < ($page-1)*$limit_per_page + $limit_per_page; $i++) {

                            if (isset($matched_docs[$i])) {

                                $doc = $matched_docs[$i];
                                
                                if ($i%2 == 0)
                                    echo '<div class="search-result-block">';
                                else 
                                    echo '<div class="search-result-block alt">';
                                
                                list($d_no_surat, $d_nama_surat, $d_no_ayat, $d_isi_teks) = explode('|', $quran_text[$doc->id - 1]);
                                list(,, $terjemah) = explode('|', $quran_trans[$doc->id - 1]);
                                
                                echo "<div class='sura-name'>";
                                echo "<div class='num'>".($i+1)."</div>";
                                echo "<span id='aya_name_$i'>Surat {$d_nama_surat} ({$d_no_surat}) ayat {$d_no_ayat}</span>";
                                echo "</div>";
                                
                                $percent_relevance = min(floor($doc->score / $max_score * 100), 100);
                                if ($percent_relevance == 0) $percent_relevance = 1;
                                
                                echo "<div class='rel-bar' title='Kecocokan {$percent_relevance}%'>";
                                echo     "<div class='relevance'>{$percent_relevance}%</div>";
                                echo     "<div class='relevance-bar'>";
                                echo         "<div class='fill' style='width: {$percent_relevance}%'></div>";
                                echo     "</div>";
                                echo "</div>";
                                
                                // to JS array
                                $js_array_str = "";
                                foreach ($doc->highlight_positions as $pos) {
                                    $js_array_str .= ",[";
                                    $js_array_str .= $pos[0] . ',' . $pos[1];
                                    $js_array_str .= "]";
                                }
                                $js_array_str = substr($js_array_str, 1);
                                $js_array_str = "[" . $js_array_str . "]";
                                
                                if ($verbose) {
                                    echo '<br/><br/><small style="color: #AAAAAA">';
                                    if ($order)
                                        echo "Dokumen #{$doc->id} (jumlah trigram cocok : {$doc->matched_trigrams_count}; skor jumlah trigram : ".round($doc->matched_terms_count_score,2) ."; skor keterurutan : ".round($doc->matched_terms_order_score,2)."; skor kedekatan : ".round($doc->matched_terms_contiguity_score,2).";  skor total : ".round($doc->score, 4).")\n";
                                    else
                                        echo "Dokumen #{$doc->id} (jumlah trigram cocok : {$doc->matched_trigrams_count}; skor jumlah trigram : ".round($doc->matched_terms_count_score,2) ."; skor total : ".round($doc->score, 4).")\n";
                                    echo "<br/>";

                                    echo "Posisi kemunculan: ".  implode(',', array_values_flatten($doc->matched_terms))."<br/>";
                                    if ($order)
                                        echo "<br/>LIS : ".  implode(',', $doc->LIS)."<br/>";
                                    echo "HL : " . $js_array_str;    
                                    echo '</small>';
                                }

                                echo     '<div class="aya_container">';

                                echo         '<div class="aya_text" id="aya_res_'.$i.'">';
                                
                                // if ayat mengandung muqathaat
                                if (isset($quran_text_muqathaat_map[$d_no_surat][$d_no_ayat])) {
                                    echo     $quran_text_muqathaat_map[$d_no_surat][$d_no_ayat];
                                } else {
                                    echo     $d_isi_teks;
                                }
                                echo         '</div>';
                                
                                echo         '<div class="aya_trans" id="aya_trans_'.$i.'">';
                                echo         $terjemah;
                                echo         '</div>';
                                
                                echo         '<div class="aya-tools">';
                                echo            "<a class='sura-link graybtn' title='Buka ayat ini di Al-Quran online' href='http://quran.ksu.edu.sa/index.php?l=id#aya={$d_no_surat}_{$d_no_ayat}&m=hafs&qaree=husary&trans=id_indonesian' target='_blank'><span class='icon'></span> Buka di Al-Quran</a>";
                                echo            "<a class='sura-link graybtn' title='Salin' href='#' onclick='showCopyDialog($i); return false;' style='margin-right: 5px;'>Salin Teks</a>";
                                echo         '</div>';
                                echo         '<div style="clear:both"></div>';
                                
                                echo     '</div>';

                                echo '</div>';
                                
/*                                echo '<div class="copyzone">';
                                echo '<p>Salin dengan CTRL+C pada <em>keyboard</em>.</p>';
                                echo '<textarea>';
                                echo "Surat {$d_nama_surat} ({$d_no_surat}) ayat {$d_no_ayat} \n\n";
                                if (isset($quran_text_muqathaat_map[$d_no_surat][$d_no_ayat])) {
                                    echo     $quran_text_muqathaat_map[$d_no_surat][$d_no_ayat];
                                } else {
                                    echo     $d_isi_teks;
                                }
                                echo "\n\n";
                                echo $terjemah;
                                echo '</textarea>';
                                echo '</div>';*/

                                $js_hl_functions .= "hilightTo('aya_res_$i', $js_array_str);"; 
                                
                            }
                        }            
                    ?>
                    </div>

                    <?php if($num_doc_found == 0) : ?>
                    <p style="padding: 10px;">
                        Tidak ada hasil. Pastikan lafaz yang dicari adalah lafaz pada Al-Quran.
                    </p>
                    <?php endif; ?>
                
                    <?php if ($num_doc_found > 10) : ?>
                    <div class="pager">
                         
                        <input type="button" value="Sebelumnya" onclick="window.location = '<?php echo "?q=" . urlencode($_GET['q']) . "&order=" . (isset($_GET['order']) ? $_GET['order'] : "off") ."&vowel=" . (isset($_GET['vowel']) ? $_GET['vowel'] : "off") . "&page=" . ($page-1) ?>'" <?php if($page==1) echo 'disabled="disabled"' ?>/>
                        Halaman:
                        <select name="page" id="page-jump"  onchange='window.location = "<?php echo "?q=" . urlencode($_GET['q']) . "&order=" . (isset($_GET['order']) ? $_GET['order'] : "off") ."&vowel=" . (isset($_GET['vowel']) ? $_GET['vowel'] : "off") . "&page=" ?>" + this.value'>
                           
                        </select>
                        <input type="button" value="Selanjutnya" onclick="window.location = '<?php echo "?q=" . urlencode($_GET['q']) . "&order=" . (isset($_GET['order']) ? $_GET['order'] : "off") ."&vowel=" . (isset($_GET['vowel']) ? $_GET['vowel'] : "off") . "&page=" . ($page+1) ?>'" <?php if($page==$num_pages-1) echo 'disabled="disabled"' ?>/>
                    </div>            
                    <?php endif; ?>

                    <?php if ($verbose) {
                        echo "\nPencarian dalam $time detik ";
                        echo ($from_cache) ? '[cache hit]' : '[cache miss]';
                        echo  "<br/>";
                        echo "Memory usage      : " . memory_get_usage() . " bytes<br/>";
                        echo "Memory peak usage : " . memory_get_peak_usage() . " bytes<br/>";
                    }
                    ?>

                <?php endif; ?>
                
                <?php if($num_doc_found > 0) : ?>
                <p style="color: #AAAAAA; font-size: 11px;">Pencarian dalam <?php echo round($time, 2) ?> detik</p>
                <?php endif; ?>
                
                <?php include 'footer.php'; ?>
                
            </div>
        </div>
        
        <script type="text/javascript">
            
            var placeHolderText = "Ketikkan lafaz di sini";
            
            $(document).ready(function(){
                
                $('#search-box').focus(function(){
                    if ($(this).val() == placeHolderText) {
                        $(this).removeClass('empty');
                        $(this).val('');
                    }
                });

                $('#search-box').blur(function(){
                    if ($(this).val() == '') {
                        $(this).addClass('empty');
                        $(this).val(placeHolderText);
                    }
                });
                
                $('#button-option').click(function(){
                    $(this).hide(); 
                    $('#search-checkboxes').css({display : 'inline-block'});
                });

                $('#button-help').click(function(){
                    $('#search-help-box').slideToggle('fast');
                });
                
                $('#srp-search-form').submit(function(){
                    if($('#search-box').val() == placeHolderText || $('#search-box').val() == '')
                        return false;
                });
                
                // mengisi dropdown pager
                var pages = [];
                var p;
                var numPages = <?php echo $num_pages ?>;
                var currPage = <?php echo $page ?>;
                for (p = 1; p < numPages; p++) {
                    if (p == currPage)
                        pages.push('<option value="'+p+'" selected="selected">'+p+'</option>');
                    else 
                        pages.push('<option value="'+p+'">'+p+'</option>');
                }
                $('#page-jump').html(pages.join(''));
                
                var srbHeight = $('#srb-container').height();
                var vpHeight = $(window).height();
                
                if (srbHeight < (vpHeight - 250)) {
                    $('#footer').css({position : 'absolute'});
                }
                
                $('#copy-overlay').click(function(e) {
                    e.stopPropagation();
                    hideCopyDialog();
                });                
                $('#copy-dialog').click(function(e) {
                    e.stopPropagation();
                });                
                $('#copy-text').click(function(){
                    $(this).select();
                });

            });
            
            function hideHilight() {
                $('.aya_text').each(function(){
                    $(this).html($(this).text());
                });
            }
            
            function showHilight() {
                <?php echo $js_hl_functions ?>
            }

            function showTrans() {
                $('.aya_trans').fadeIn('fast');
            }
            
            function hideTrans() {
                $('.aya_trans').fadeOut('fast');
            }
            
            showHilight();
            
            function hideCopyDialog() {
                $('#copy-overlay').fadeOut('fast');
                $('#copy-dialog').fadeOut('fast');
            }
            
            function showCopyDialog(id) {
                var suraName = $('#aya_name_' + id).text();
                var ayat = $('#aya_res_' + id).text();
                var trans = $('#aya_trans_' + id).text();
                
                $('#copy-text').val(ayat + "\n\n" + trans + " [" + suraName + "]");
                
                $('#copy-overlay').fadeIn('fast');
                $('#copy-dialog').fadeIn('fast');                

                $('#copy-text').select();
            }

        </script>        
        
    </body>
</html>
