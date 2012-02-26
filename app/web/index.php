<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Lafzi - Pencarian Lafaz Quran</title>
        <link href="res/main.css" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="res/jquery.1.7.js"></script>
    </head>
    <body>
        <div id="main-wrap">
            <div id="main">
                <div id="main-logo">
                    <img src="res/img/logo.png" alt="Lafzi - Pencarian lafaz Quran" width="394" height="172"/>
                </div>

                <form action="search.php" method="get" id="main-search-form">

                    <div id="search-form-container">
                        <input type="text" name="q" id="search-box" class="empty" value="Ketikkan lafaz yang dicari" autocomplete="off"/><input type="submit" value="Cari" id="search-submit"/>
                    </div>

                    <div id="search-options-container">
                        <input type="button" class="search-option" value="Bantuan &raquo;" id="button-help"/>
                        <input type="button" class="search-option" value="Pengaturan &raquo;" id="button-option" title="Pengaturan tambahan"/>
                        <div id="search-checkboxes">
                            <input type="checkbox" id="os" name="order" checked="checked"/>
                            <label for="os">Perhitungkan keterurutan</label>
                            <input type="checkbox" id="vw" name="vowel" checked="checked"/>
                            <label for="vw">Perhitungkan huruf vokal</label>
                        </div>                        
                    </div>

                    <div id="search-help-box">
                        Ketikkan potongan ayat atau lafaz dalam Al-Quran (tidak harus benar cara penulisannya), contoh:

                        <ul>
                            <li>alhamdulillahi rabbil-'alamin</li>
                            <li>innalloha ma'a shoobiriin</li>
                            <li>laa ilaaha illallaah</li>
                            <li>kun fayakuun</li>
                        </ul>

                        Tips: Gunakan spasi untuk pemisah antar kata agar lebih akurat.
                    </div>

                </form>

                <?php include 'footer.php'; ?>

            </div>
        </div>

        <script type="text/javascript">
            
            var placeHolderText = "";
            
            $(document).ready(function(){
                placeHolderText = $('#search-box').val();
                
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

                //                $('#search-checkboxes').mouseleave(function(){
                //                    $(this).hide(); 
                //                    $('#button-option').css({display : 'inline-block'});
                //                });
                
                $('#button-help').click(function(){
                    $('#search-help-box').slideToggle('fast');
                });
                
                $('#main-search-form').submit(function(){
                    if($('#search-box').val() == placeHolderText || $('#search-box').val() == '')
                        return false;
                });

            });
        </script>

    </body>
</html>