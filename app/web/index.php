<?php

if (isset($_GET['q']) && $_GET['q'] != "") {

    include '../search/search_ff.php';
    include '../lib/fonetik_id.php';

    // profiling
    $time_start = microtime(true);

    $query = $_GET['q'];

    $query_final = id_fonetik($query, false);
    $query_trigrams_count = strlen($query_final) - 2;

    $term_list_filename = "../data/index_termlist_vokal.txt";
    $post_list_filename = "../data/index_postlist_vokal.txt";

    $matched_docs = & search($query_final, $term_list_filename, $post_list_filename); // using ff

    $num_doc_found = count($matched_docs);
    $quran_text = file("../data/quran_teks.txt", FILE_IGNORE_NEW_LINES);

    $limit = 10;

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
        <h3>Pencarian fonetik</h3>
        <div style="background-color: #CCCCCC; padding: 10px; margin-bottom: 20px;">
            <form action="" method="get">
                Cari : <input type="text" name="q" size="40" value="<?php if (isset($_GET['q'])) echo $_GET['q'] ?>"/>
                <input type="submit" value="  Cari  "/>
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
            
            <br/><strong>Top <?php echo $limit ?> dokumen : </strong><br/>
            
            <?php
            
                for ($i = 0; $i < $limit; $i++) {

                    echo '<div style="background-color: #CCCCCC; padding: 10px; margin-bottom: 10px;">';
                    
                    $doc = $matched_docs[$i];
                    echo ($i + 1) . ". Dokumen #{$doc->id} (jumlah trigram cocok : {$doc->matched_trigrams_count}; skor : ".round($doc->score, 2).")\n";
                    echo "<br/>";
                    
                    $doc_data = explode('|', $quran_text[$doc->id - 1]);

                    echo '<small>';                    
                    echo "<em>";
                    echo "Surat {$doc_data[1]} ({$doc_data[0]}) ayat {$doc_data[2]}\n";
                    echo "</em><br/>";
                    
                    echo "Posisi kemunculan : ".  implode(',', array_values($doc->matched_terms))."\n\n";
                    echo '</small>';
                    
                    echo "<br/><br/>";
                    echo '<div style="text-align: right; font-size: 25px">';
                    echo $doc_data[3] . "\n\n";
                    echo '</div>';
                    
                    echo '</div>';
                }            
            
            ?>
            
            <p>
                <?php
                    echo "\nPencarian dalam $time detik<br/>";
                    echo "Memory usage      : " . memory_get_usage() . "<br/>";
                    echo "Memory peak usage : " . memory_get_peak_usage() . "<br/>";
                ?>
            </p>
            
        </div>
        <?php endif; ?>
    </body>
</html>
