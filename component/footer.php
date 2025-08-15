
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="logo"><img src="<?php echo APP_LOGO ?>" alt="logo" class="logo-white" width="250"></div>
                    <p><?php echo APP_NAME ?> Capital Advisors Corporation is an investment advisor registered with the Securities and Exchange Commission ("SEC"). Any reference to the advisory services refers to <?php echo APP_NAME ?> Capital Advisors Corporation. Contracts for difference (‘CFDs’) is a complex financial product, with speculative character, the trading of which involves significant risks of loss of capital. Trading CFDs, which is a marginal product, may result in the loss of your entire balance. Remember that leverage in CFDs can work both to your advantage and disadvantage. CFDs traders do not own, or have any rights to, the underlying assets.</p>

                </div>

            </div>
        </div>
        <div class="down-footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">

                        <p><?php echo APP_COPYRIGHT ?></p>
                        <ul class="footer-menu">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="About.php" class="smooth-scroll">About</a></li>
                            <li><a href="Terms.php" class="smooth-scroll">Terms </a></li>
                            <li><a href="Contact.php" class="smooth-scroll">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="lib/jquery/dist/jquery.js"></script>
    <script src="js/libs.js"></script>
    <!--Use scripts-->
    <script src="js/common.js"></script>
    
      
    <script type="text/javascript">
        var listCountries = ['Brazil', 'USA', 'Germany', 'France', 'Italy', 'India', 'Australia', 'China', 'Canada', 'Argentina', 'Saudi Arabia', 'Mexico', 'Indonesia', 'Egypt', 'Venezuela', 'Madagascar', 'Sweden', 'Philippines', 'Finland', 'Italy', 'Ecuador', 'United Kingdom', 'Belarus', 'Greece', 'Cuba', 'Hungary', 'Portugal', 'Austria', 'Iceland', 'Panama', 'Croatia', 'Denmark', 'Netherlands', 'Switzerland', 'Belgium', 'Israel', 'Cyprus'];
        var listPlans = ['$1000', '$54950', '$45000', '$20560', '$105000', '$50000', '$60000', '$3000', '$15000', '$5000'];
        interval = Math.floor(Math.random() * (40000 - 8000 + 1) + 8000);
        var run = setInterval(request, interval);

        function request() {
            clearInterval(run);
            interval = Math.floor(Math.random() * (40000 - 8000 + 1) + 8000);
            var country = listCountries[Math.floor(Math.random() * listCountries.length)];
            var plan = listPlans[Math.floor(Math.random() * listPlans.length)];
            var msg = 'Someone from <b>' + country + '</b> just Withdraw <a href="javascript:void(0);" onclick="javascript:void(0);">' + plan + '</a>';
            $(".mgm .txt").html(msg);
            $(".mgm").stop(true).fadeIn(300);
            window.setTimeout(function () {
                $(".mgm").stop(true).fadeOut(300);
            }, 6000);
            run = setInterval(request, interval);
        }
    </script>
    <div style="position:fixed;bottom:0px;left:8%;z-index:999999;" id="gtranslate_wrapper">
        <!-- GTranslate: https://gtranslate.io/ -->
        <style>
            .switcher {
                font-family: Arial;
                font-size: 12pt;
                text-align: left;
                cursor: pointer;
                overflow: hidden;
                width: 173px;
                line-height: 17px;
            }

                .switcher a {
                    text-decoration: none;
                    display: block;
                    font-size: 12pt;
                    -webkit-box-sizing: content-box;
                    -moz-box-sizing: content-box;
                    box-sizing: content-box;
                }

                    .switcher a img {
                        vertical-align: middle;
                        display: inline;
                        border: 0;
                        padding: 0;
                        margin: 0;
                        opacity: 0.8;
                    }

                    .switcher a:hover img {
                        opacity: 1;
                    }

                .switcher .selected {
                    background: #fff linear-gradient(180deg, #efefef 0%, #fff 70%);
                    position: relative;
                    z-index: 9999;
                }

                    .switcher .selected a {
                        border: 1px solid #ccc;
                        color: #070707;
                        padding: 3px 5px;
                        width: 161px;
                    }

                        .switcher .selected a:after {
                            height: 24px;
                            display: inline-block;
                            position: absolute;
                            right: 10px;
                            width: 15px;
                            background-position: 50%;
                            background-size: 11px;
                            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 285 285'><path d='M282 76.5l-14.2-14.3a9 9 0 0 0-13.1 0L142.5 174.4 30.3 62.2a9 9 0 0 0-13.2 0L3 76.5a9 9 0 0 0 0 13.1l133 133a9 9 0 0 0 13.1 0l133-133a9 9 0 0 0 0-13z' style='fill:%23050505'/></svg>");
                            background-repeat: no-repeat;
                            content: "" !important;
                            transition: all .2s;
                        }

                        .switcher .selected a.open:after {
                            -webkit-transform: rotate(-180deg);
                            transform: rotate(-180deg);
                        }

                        .switcher .selected a:hover {
                            background: #fff;
                        }

                .switcher .option {
                    position: relative;
                    z-index: 9998;
                    border-left: 1px solid #ccc;
                    border-right: 1px solid #ccc;
                    border-bottom: 1px solid #ccc;
                    background-color: #eee;
                    display: none;
                    width: 171px;
                    max-height: 198px;
                    -webkit-box-sizing: content-box;
                    -moz-box-sizing: content-box;
                    box-sizing: content-box;
                    overflow-y: auto;
                    overflow-x: hidden;
                }

                    .switcher .option a {
                        color: #000;
                        padding: 3px 5px;
                    }

                        .switcher .option a:hover {
                            background: #fff;
                        }

                        .switcher .option a.selected {
                            background: #fff;
                        }

            #selected_lang_name {
                float: none;
            }

            .l_name {
                float: none !important;
                margin: 0;
            }

            .switcher .option::-webkit-scrollbar-track {
                -webkit-box-shadow: inset 0 0 3px rgba(0,0,0,0.3);
                border-radius: 5px;
                background-color: #f5f5f5;
            }

            .switcher .option::-webkit-scrollbar {
                width: 5px;
            }

            .switcher .option::-webkit-scrollbar-thumb {
                border-radius: 5px;
                -webkit-box-shadow: inset 0 0 3px rgba(0,0,0,.3);
                background-color: #888;
            }
        </style>
        <div class="switcher notranslate">
            <div class="selected">
                <a href="#" onclick="return false;">English</a>
            </div>
            <div class="option">
                <a href="#" onclick="doGTranslate('en|af');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Afrikaans" class="nturl"> Afrikaans</a><a href="#" onclick="doGTranslate('en|sq');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Shqip" class="nturl"> Shqip</a><a href="#" onclick="doGTranslate('en|am');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="አማርኛ" class="nturl"> አማርኛ</a><a href="#" onclick="doGTranslate('en|ar');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="العربية" class="nturl"> العربية</a><a href="#" onclick="doGTranslate('en|hy');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Հայերեն" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/hy.png" height="24" width="24" alt="hy"> Հայերեն</a><a href="#" onclick="doGTranslate('en|az');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Azərbaycan dili" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/az.png" height="24" width="24" alt="az"> Azərbaycan dili</a><a href="#" onclick="doGTranslate('en|eu');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Euskara" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/eu.png" height="24" width="24" alt="eu"> Euskara</a><a href="#" onclick="doGTranslate('en|be');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Беларуская мова" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/be.png" height="24" width="24" alt="be"> Беларуская мова</a><a href="#" onclick="doGTranslate('en|bn');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="বাংলা" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/bn.png" height="24" width="24" alt="bn"> বাংলা</a><a href="#" onclick="doGTranslate('en|bs');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Bosanski" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/bs.png" height="24" width="24" alt="bs"> Bosanski</a><a href="#" onclick="doGTranslate('en|bg');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Български" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/bg.png" height="24" width="24" alt="bg"> Български</a><a href="#" onclick="doGTranslate('en|ca');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Català" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ca.png" height="24" width="24" alt="ca"> Català</a><a href="#" onclick="doGTranslate('en|ceb');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Cebuano" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ceb.png" height="24" width="24" alt="ceb"> Cebuano</a><a href="#" onclick="doGTranslate('en|ny');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Chichewa" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ny.png" height="24" width="24" alt="ny"> Chichewa</a><a href="#" onclick="doGTranslate('en|zh-CN');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="简体中文" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/zh-CN.png" height="24" width="24" alt="zh-CN"> 简体中文</a><a href="#" onclick="doGTranslate('en|zh-TW');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="繁體中文" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/zh-TW.png" height="24" width="24" alt="zh-TW"> 繁體中文</a><a href="#" onclick="doGTranslate('en|co');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Corsu" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/co.png" height="24" width="24" alt="co"> Corsu</a><a href="#" onclick="doGTranslate('en|hr');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Hrvatski" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/hr.png" height="24" width="24" alt="hr"> Hrvatski</a><a href="#" onclick="doGTranslate('en|cs');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Čeština‎" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/cs.png" height="24" width="24" alt="cs"> Čeština‎</a><a href="#" onclick="doGTranslate('en|da');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Dansk" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/da.png" height="24" width="24" alt="da"> Dansk</a><a href="#" onclick="doGTranslate('en|nl');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Nederlands" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/nl.png" height="24" width="24" alt="nl"> Nederlands</a><a href="#" onclick="doGTranslate('en|en');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="English" class="nturl selected"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/en-us.png" height="24" width="24" alt="en"> English</a><a href="#" onclick="doGTranslate('en|eo');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Esperanto" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/eo.png" height="24" width="24" alt="eo"> Esperanto</a><a href="#" onclick="doGTranslate('en|et');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Eesti" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/et.png" height="24" width="24" alt="et"> Eesti</a><a href="#" onclick="doGTranslate('en|tl');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Filipino" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/tl.png" height="24" width="24" alt="tl"> Filipino</a><a href="#" onclick="doGTranslate('en|fi');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Suomi" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/fi.png" height="24" width="24" alt="fi"> Suomi</a><a href="#" onclick="doGTranslate('en|fr');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Français" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/fr.png" height="24" width="24" alt="fr"> Français</a><a href="#" onclick="doGTranslate('en|fy');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Frysk" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/fy.png" height="24" width="24" alt="fy"> Frysk</a><a href="#" onclick="doGTranslate('en|gl');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Galego" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/gl.png" height="24" width="24" alt="gl"> Galego</a><a href="#" onclick="doGTranslate('en|ka');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ქართული" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ka.png" height="24" width="24" alt="ka"> ქართული</a><a href="#" onclick="doGTranslate('en|de');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Deutsch" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/de.png" height="24" width="24" alt="de"> Deutsch</a><a href="#" onclick="doGTranslate('en|el');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Ελληνικά" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/el.png" height="24" width="24" alt="el"> Ελληνικά</a><a href="#" onclick="doGTranslate('en|gu');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ગુજરાતી" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/gu.png" height="24" width="24" alt="gu"> ગુજરાતી</a><a href="#" onclick="doGTranslate('en|ht');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Kreyol ayisyen" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ht.png" height="24" width="24" alt="ht"> Kreyol ayisyen</a><a href="#" onclick="doGTranslate('en|ha');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Harshen Hausa" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ha.png" height="24" width="24" alt="ha"> Harshen Hausa</a><a href="#" onclick="doGTranslate('en|haw');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Ōlelo Hawaiʻi" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/haw.png" height="24" width="24" alt="haw"> Ōlelo Hawaiʻi</a><a href="#" onclick="doGTranslate('en|iw');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="עִבְרִית" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/iw.png" height="24" width="24" alt="iw"> עִבְרִית</a><a href="#" onclick="doGTranslate('en|hi');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="हिन्दी" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/hi.png" height="24" width="24" alt="hi"> हिन्दी</a><a href="#" onclick="doGTranslate('en|hmn');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Hmong" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/hmn.png" height="24" width="24" alt="hmn"> Hmong</a><a href="#" onclick="doGTranslate('en|hu');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Magyar" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/hu.png" height="24" width="24" alt="hu"> Magyar</a><a href="#" onclick="doGTranslate('en|is');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Íslenska" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/is.png" height="24" width="24" alt="is"> Íslenska</a><a href="#" onclick="doGTranslate('en|ig');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Igbo" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ig.png" height="24" width="24" alt="ig"> Igbo</a><a href="#" onclick="doGTranslate('en|id');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Bahasa Indonesia" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/id.png" height="24" width="24" alt="id"> Bahasa Indonesia</a><a href="#" onclick="doGTranslate('en|ga');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Gaelige" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ga.png" height="24" width="24" alt="ga"> Gaelige</a><a href="#" onclick="doGTranslate('en|it');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Italiano" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/it.png" height="24" width="24" alt="it"> Italiano</a><a href="#" onclick="doGTranslate('en|ja');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="日本語" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ja.png" height="24" width="24" alt="ja"> 日本語</a><a href="#" onclick="doGTranslate('en|jw');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Basa Jawa" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/jw.png" height="24" width="24" alt="jw"> Basa Jawa</a><a href="#" onclick="doGTranslate('en|kn');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ಕನ್ನಡ" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/kn.png" height="24" width="24" alt="kn"> ಕನ್ನಡ</a><a href="#" onclick="doGTranslate('en|kk');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Қазақ тілі" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/kk.png" height="24" width="24" alt="kk"> Қазақ тілі</a><a href="#" onclick="doGTranslate('en|km');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ភាសាខ្មែរ" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/km.png" height="24" width="24" alt="km"> ភាសាខ្មែរ</a><a href="#" onclick="doGTranslate('en|ko');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="한국어" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ko.png" height="24" width="24" alt="ko"> 한국어</a><a href="#" onclick="doGTranslate('en|ku');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="كوردی‎" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ku.png" height="24" width="24" alt="ku"> كوردی‎</a><a href="#" onclick="doGTranslate('en|ky');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Кыргызча" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ky.png" height="24" width="24" alt="ky"> Кыргызча</a><a href="#" onclick="doGTranslate('en|lo');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ພາສາລາວ" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/lo.png" height="24" width="24" alt="lo"> ພາສາລາວ</a><a href="#" onclick="doGTranslate('en|la');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Latin" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/la.png" height="24" width="24" alt="la"> Latin</a><a href="#" onclick="doGTranslate('en|lv');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Latviešu valoda" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/lv.png" height="24" width="24" alt="lv"> Latviešu valoda</a><a href="#" onclick="doGTranslate('en|lt');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Lietuvių kalba" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/lt.png" height="24" width="24" alt="lt"> Lietuvių kalba</a><a href="#" onclick="doGTranslate('en|lb');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Lëtzebuergesch" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/lb.png" height="24" width="24" alt="lb"> Lëtzebuergesch</a><a href="#" onclick="doGTranslate('en|mk');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Македонски јазик" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/mk.png" height="24" width="24" alt="mk"> Македонски јазик</a><a href="#" onclick="doGTranslate('en|mg');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Malagasy" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/mg.png" height="24" width="24" alt="mg"> Malagasy</a><a href="#" onclick="doGTranslate('en|ms');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Bahasa Melayu" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ms.png" height="24" width="24" alt="ms"> Bahasa Melayu</a><a href="#" onclick="doGTranslate('en|ml');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="മലയാളം" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ml.png" height="24" width="24" alt="ml"> മലയാളം</a><a href="#" onclick="doGTranslate('en|mt');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Maltese" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/mt.png" height="24" width="24" alt="mt"> Maltese</a><a href="#" onclick="doGTranslate('en|mi');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Te Reo Māori" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/mi.png" height="24" width="24" alt="mi"> Te Reo Māori</a><a href="#" onclick="doGTranslate('en|mr');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="मराठी" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/mr.png" height="24" width="24" alt="mr"> मराठी</a><a href="#" onclick="doGTranslate('en|mn');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Монгол" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/mn.png" height="24" width="24" alt="mn"> Монгол</a><a href="#" onclick="doGTranslate('en|my');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ဗမာစာ" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/my.png" height="24" width="24" alt="my"> ဗမာစာ</a><a href="#" onclick="doGTranslate('en|ne');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="नेपाली" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ne.png" height="24" width="24" alt="ne"> नेपाली</a><a href="#" onclick="doGTranslate('en|no');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Norsk bokmål" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/no.png" height="24" width="24" alt="no"> Norsk bokmål</a><a href="#" onclick="doGTranslate('en|ps');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="پښتو" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ps.png" height="24" width="24" alt="ps"> پښتو</a><a href="#" onclick="doGTranslate('en|fa');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="فارسی" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/fa.png" height="24" width="24" alt="fa"> فارسی</a><a href="#" onclick="doGTranslate('en|pl');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Polski" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/pl.png" height="24" width="24" alt="pl"> Polski</a><a href="#" onclick="doGTranslate('en|pt');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Português" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/pt.png" height="24" width="24" alt="pt"> Português</a><a href="#" onclick="doGTranslate('en|pa');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ਪੰਜਾਬੀ" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/pa.png" height="24" width="24" alt="pa"> ਪੰਜਾਬੀ</a><a href="#" onclick="doGTranslate('en|ro');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Română" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ro.png" height="24" width="24" alt="ro"> Română</a><a href="#" onclick="doGTranslate('en|ru');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Русский" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ru.png" height="24" width="24" alt="ru"> Русский</a><a href="#" onclick="doGTranslate('en|sm');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Samoan" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sm.png" height="24" width="24" alt="sm"> Samoan</a><a href="#" onclick="doGTranslate('en|gd');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Gàidhlig" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/gd.png" height="24" width="24" alt="gd"> Gàidhlig</a><a href="#" onclick="doGTranslate('en|sr');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Српски језик" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sr.png" height="24" width="24" alt="sr"> Српски језик</a><a href="#" onclick="doGTranslate('en|st');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Sesotho" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/st.png" height="24" width="24" alt="st"> Sesotho</a><a href="#" onclick="doGTranslate('en|sn');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Shona" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sn.png" height="24" width="24" alt="sn"> Shona</a><a href="#" onclick="doGTranslate('en|sd');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="سنڌي" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sd.png" height="24" width="24" alt="sd"> سنڌي</a><a href="#" onclick="doGTranslate('en|si');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="සිංහල" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/si.png" height="24" width="24" alt="si"> සිංහල</a><a href="#" onclick="doGTranslate('en|sk');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Slovenčina" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sk.png" height="24" width="24" alt="sk"> Slovenčina</a><a href="#" onclick="doGTranslate('en|sl');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Slovenščina" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sl.png" height="24" width="24" alt="sl"> Slovenščina</a><a href="#" onclick="doGTranslate('en|so');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Afsoomaali" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/so.png" height="24" width="24" alt="so"> Afsoomaali</a><a href="#" onclick="doGTranslate('en|es');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Español" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/es.png" height="24" width="24" alt="es"> Español</a><a href="#" onclick="doGTranslate('en|su');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Basa Sunda" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/su.png" height="24" width="24" alt="su"> Basa Sunda</a><a href="#" onclick="doGTranslate('en|sw');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Kiswahili" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sw.png" height="24" width="24" alt="sw"> Kiswahili</a><a href="#" onclick="doGTranslate('en|sv');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Svenska" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/sv.png" height="24" width="24" alt="sv"> Svenska</a><a href="#" onclick="doGTranslate('en|tg');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Тоҷикӣ" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/tg.png" height="24" width="24" alt="tg"> Тоҷикӣ</a><a href="#" onclick="doGTranslate('en|ta');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="தமிழ்" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ta.png" height="24" width="24" alt="ta"> தமிழ்</a><a href="#" onclick="doGTranslate('en|te');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="తెలుగు" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/te.png" height="24" width="24" alt="te"> తెలుగు</a><a href="#" onclick="doGTranslate('en|th');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="ไทย" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/th.png" height="24" width="24" alt="th"> ไทย</a><a href="#" onclick="doGTranslate('en|tr');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Türkçe" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/tr.png" height="24" width="24" alt="tr"> Türkçe</a><a href="#" onclick="doGTranslate('en|uk');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Українська" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/uk.png" height="24" width="24" alt="uk"> Українська</a><a href="#" onclick="doGTranslate('en|ur');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="اردو" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/ur.png" height="24" width="24" alt="ur"> اردو</a><a href="#" onclick="doGTranslate('en|uz');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="O‘zbekcha" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/uz.png" height="24" width="24" alt="uz"> O‘zbekcha</a><a href="#" onclick="doGTranslate('en|vi');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Tiếng Việt" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/vi.png" height="24" width="24" alt="vi"> Tiếng Việt</a><a href="#" onclick="doGTranslate('en|cy');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Cymraeg" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/cy.png" height="24" width="24" alt="cy"> Cymraeg</a><a href="#" onclick="doGTranslate('en|xh');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="isiXhosa" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/xh.png" height="24" width="24" alt="xh"> isiXhosa</a><a href="#" onclick="doGTranslate('en|yi');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="יידיש" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/yi.png" height="24" width="24" alt="yi"> יידיש</a><a href="#" onclick="doGTranslate('en|yo');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Yorùbá" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/yo.png" height="24" width="24" alt="yo"> Yorùbá</a><a href="#" onclick="doGTranslate('en|zu');jQuery('div.switcher div.selected a').html(jQuery(this).html());return false;" title="Zulu" class="nturl"><img data-gt-lazy-src="//unvacationportal.org/wp-content/plugins/gtranslate/flags/24/zu.png" height="24" width="24" alt="zu"> Zulu</a>
            </div>
        </div>
        <script>
            jQuery('.switcher .selected').click(function () { jQuery('.switcher .option a img').each(function () { if (!jQuery(this)[0].hasAttribute('src')) jQuery(this).attr('src', jQuery(this).attr('data-gt-lazy-src')) }); if (!(jQuery('.switcher .option').is(':visible'))) { jQuery('.switcher .option').stop(true, true).delay(100).slideDown(500); jQuery('.switcher .selected a').toggleClass('open') } });
            jQuery('.switcher .option').bind('mousewheel', function (e) { var options = jQuery('.switcher .option'); if (options.is(':visible')) options.scrollTop(options.scrollTop() - e.originalEvent.wheelDelta / 10); return false; });
            jQuery('body').not('.switcher').click(function (e) { if (jQuery('.switcher .option').is(':visible') && e.target != jQuery('.switcher .option').get(0)) { jQuery('.switcher .option').stop(true, true).delay(100).slideUp(500); jQuery('.switcher .selected a').toggleClass('open') } });
        </script>
        <style>
            #goog-gt-tt {
                display: none !important;
            }

            .goog-te-banner-frame {
                display: none !important;
            }

            .goog-te-menu-value:hover {
                text-decoration: none !important;
            }

            .goog-text-highlight {
                background-color: transparent !important;
                box-shadow: none !important;
            }

            body {
                top: 0 !important;
            }

            #google_translate_element2 {
                display: none !important;
            }
        </style>

        <div id="google_translate_element2"></div>
        <script>
            function googleTranslateElementInit2() { new google.translate.TranslateElement({ pageLanguage: 'en', autoDisplay: false }, 'google_translate_element2'); }
        </script>
        <script src="translate.google.com/translate_a/element7876.js?cb=googleTranslateElementInit2"></script>


        <script>
            function GTranslateGetCurrentLang() { var keyValue = document['cookie'].match('(^|;) ?googtrans=([^;]*)(;|$)'); return keyValue ? keyValue[2].split('index.html')[2] : null; }
            function GTranslateFireEvent(element, event) { try { if (document.createEventObject) { var evt = document.createEventObject(); element.fireEvent('on' + event, evt) } else { var evt = document.createEvent('HTMLEvents'); evt.initEvent(event, true, true); element.dispatchEvent(evt) } } catch (e) { } }
            function doGTranslate(lang_pair) { if (lang_pair.value) lang_pair = lang_pair.value; if (lang_pair == '') return; var lang = lang_pair.split('|')[1]; if (GTranslateGetCurrentLang() == null && lang == lang_pair.split('|')[0]) return; var teCombo; var sel = document.getElementsByTagName('select'); for (var i = 0; i < sel.length; i++)if (sel[i].className.indexOf('goog-te-combo') != -1) { teCombo = sel[i]; break; } if (document.getElementById('google_translate_element2') == null || document.getElementById('google_translate_element2').innerHTML.length == 0 || teCombo.length == 0 || teCombo.innerHTML.length == 0) { setTimeout(function () { doGTranslate(lang_pair) }, 500) } else { teCombo.value = lang; GTranslateFireEvent(teCombo, 'change'); GTranslateFireEvent(teCombo, 'change') } }
            if (GTranslateGetCurrentLang() != null) jQuery(document).ready(function () { var lang_html = jQuery('div.switcher div.option').find('img[alt="' + GTranslateGetCurrentLang() + '"]').parent().html(); if (typeof lang_html != 'undefined') jQuery('div.switcher div.selected a').html(lang_html.replace('data-gt-lazy-', '')); });
        </script>
        <script>jQuery(document).ready(function () { var allowed_languages = ["af", "sq", "am", "ar", "hy", "az", "eu", "be", "bn", "bs", "bg", "ca", "ceb", "ny", "zh-CN", "zh-TW", "co", "hr", "cs", "da", "nl", "en", "eo", "et", "tl", "fi", "fr", "fy", "gl", "ka", "de", "el", "gu", "ht", "ha", "haw", "iw", "hi", "hmn", "hu", "is", "ig", "id", "ga", "it", "ja", "jw", "kn", "kk", "km", "ko", "ku", "ky", "lo", "la", "lv", "lt", "lb", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mn", "my", "ne", "no", "ps", "fa", "pl", "pt", "pa", "ro", "ru", "sm", "gd", "sr", "st", "sn", "sd", "si", "sk", "sl", "so", "es", "su", "sw", "sv", "tg", "ta", "te", "th", "tr", "uk", "ur", "uz", "vi", "cy", "xh", "yi", "yo", "zu"]; var accept_language = navigator.language.toLowerCase() || navigator.userLanguage.toLowerCase(); switch (accept_language) { case 'zh-cn': var preferred_language = 'zh-CN'; break; case 'zh': var preferred_language = 'zh-CN'; break; case 'zh-tw': var preferred_language = 'zh-TW'; break; case 'zh-hk': var preferred_language = 'zh-TW'; break; case 'he': var preferred_language = 'iw'; break; default: var preferred_language = accept_language.substr(0, 2); break; }if (preferred_language != 'en' && GTranslateGetCurrentLang() == null && document.cookie.match('gt_auto_switch') == null && allowed_languages.indexOf(preferred_language) >= 0) { doGTranslate('en|' + preferred_language); document.cookie = 'gt_auto_switch=1; expires=Thu, 05 Dec 2030 08:08:08 UTC; path=/;'; var lang_html = jQuery('div.switcher div.option').find('img[alt="' + preferred_language + '"]').parent().html(); if (typeof lang_html != 'undefined') jQuery('div.switcher div.selected a').html(lang_html.replace('data-gt-lazy-', '')); } });</script>
    </div>
    
</body>

<!-- Mirrored from digixtradesecurities.com/ by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 23 May 2025 13:04:44 GMT -->
</html>
