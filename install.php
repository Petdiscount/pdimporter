<?php
/**
 * Petdiscount Magento Importer
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 */

/**
 * Check if already installed
 */


if (file_exists("config/config.json")) {
    echo "Already installed, if you want to reinstall you need to delete config/config.json";
    die;
}

require_once "vendor/autoload.php";

foreach (glob("app/controllers/*") as $controller) {
    require $controller;
}

require_once '../app/Mage.php'; //include magento

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


/**
 * Check if data has been submitted
 * If yes, save config
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $configfile = fopen("config/config.json", "w") or die("Unable to open file!");
    $configdata['email'] = $_POST['email'];
    $configdata['apikey'] = $_POST['apikey'];
    $configdata['lang'] = $_POST['lang'];
    $configdata['country'] = $_POST['country'];
    $configdata['categories'] = $_POST['categories'];
    $configdata = json_encode($configdata);
    fwrite($configfile, $configdata);
    fclose($configfile);

    /**
     * Setup the needed attributes
     */
    $attributecontroller = new AttributeController();
    $attributecontroller->CreateAttributeGroup('Petdiscount');
    $attributecontroller->createAttribute("pd_product", "Petdiscount Product", "boolean", "", "Petdiscount");
    $attributecontroller->createAttribute("pd_import", "Petdiscount Import Status", "boolean", "", "Petdiscount");
    $attributecontroller->createAttribute("pd_ean_number", "EAN Number", "text", "simple", "Petdiscount");
    $attributecontroller->createAttribute("pd_brand", "Brand", "select", "", "Petdiscount");

    echo "Installed!";
    die;
}



?>

<html>
<head>
    <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
    <style>
        /*custom font*/

        @import url(http://fonts.googleapis.com/css?family=Montserrat);
        /*basic reset*/
        * {
            margin: 0;
            padding: 0;
        }
        html {
            height: 100%;

            background: linear-gradient(rgba(0, 0, 0, 0.2), rgb(204, 209, 223));
        }
        body {
            font-family: montserrat, arial, verdana;
        }
        /*form styles*/
        #msform {
            width: 500px;
            margin: 50px auto;
            text-align: center;
            position: relative;
        }
        #msform fieldset {
            background: white;
            border: 0 none;
            border-radius: 3px;
            box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
            padding: 20px 30px;
            box-sizing: border-box;
            width: 80%;
            margin: 0 10%;
            /*stacking fieldsets above each other*/
            position: absolute;
        }
        /*Hide all except first fieldset*/
        #msform fieldset:not(:first-of-type) {
            display: none;
        }
        /*inputs*/
        #msform input, #msform textarea {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
            font-family: montserrat;
            color: #2C3E50;
            font-size: 13px;
        }
        /*buttons*/
        #msform .action-button {
            width: 100px;
            background: #0b82b3;
            font-weight: bold;
            color: white;
            border: 0 none;
            border-radius: 1px;
            cursor: pointer;
            padding: 10px 5px;
            margin: 10px 5px;
        }
        #msform .action-button:hover, #msform .action-button:focus {
            box-shadow: 0 0 0 2px white, 0 0 0 3px #0b82b3;
        }
        /*headings*/
        .fs-title {
            font-size: 15px;
            text-transform: uppercase;
            color: #2C3E50;
            margin-bottom: 10px;
        }
        .fs-subtitle {
            font-weight: normal;
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
        }
        /*progressbar*/
        #progressbar {
            margin-bottom: 30px;
            overflow: hidden;
            /*CSS counters to number the steps*/
            counter-reset: step;
        }
        #progressbar li {
            list-style-type: none;
            color: white;
            text-transform: uppercase;
            font-size: 9px;
            width: 25%;
            float: left;
            position: relative;
        }
        #progressbar li:before {
            content: counter(step);
            counter-increment: step;
            width: 20px;
            line-height: 20px;
            display: block;
            font-size: 10px;
            color: #333;
            background: white;
            border-radius: 3px;
            margin: 0 auto 5px auto;
        }
        /*progressbar connectors*/
        #progressbar li:after {
            content: '';
            width: 100%;
            height: 2px;
            background: white;
            position: absolute;
            left: -50%;
            top: 9px;
            z-index: -1; /*put it behind the numbers*/
        }
        #progressbar li:first-child:after {
            /*connector not needed before the first step*/
            content: none;
        }
        /*marking active/completed steps green*/
        /*The number of the step and the connector before it = green*/
        #progressbar li.active:before, #progressbar li.active:after {
            background: #0b82b3;
            color: white;
        }

        select {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
            font-family: montserrat;
            color: #2C3E50;
            font-size: 13px;
        }

    </style>
    <title>jQuery Multi-Step Form Example</title>
</head>
<body>
<!-- multistep form -->
<form id="msform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <!-- progressbar -->
    <ul id="progressbar">
        <li class="active">Credentials</li>
        <li>localisation</li>
        <li>Catalog</li>
        <li>Finalize</li>
    </ul>
    <!-- fieldsets -->
    <fieldset>
        <h2 class="fs-title">Petdiscount API</h2>
        <h3 class="fs-subtitle">Fill in your credentials</h3>
        <input type="text" name="email" placeholder="Email" />
        <input type="password" name="apikey" placeholder="Apikey" />
        <input type="button" name="next" class="next action-button" value="Next" />
    </fieldset>
    <fieldset>
        <h2 class="fs-title">Localisation</h2>
        <h3 class="fs-subtitle">Meet the language and other requirements of your target market</h3>
        <select name="lang">
            <option>Your webshop language</option>
            <option value="nl">Dutch (NL</option>
            <option value="en">English (EN)</option>
            <option value="de">German (DE)</option>
            <option value="fr">French (FR)</option>
        </select>
        <select name="country">
            <option>Your (main) target country</option>
            <option value="nl">Netherlands</option>
            <option value="be">Belgium</option>
            <option value="eu">Rest of Europe</option>
        </select>

        <input type="button" name="previous" class="previous action-button" value="Previous" />
        <input type="button" name="next" class="next action-button" value="Next" />
    </fieldset>
    <fieldset>
        <h2 class="fs-title">Catalog</h2>
        <h3 class="fs-subtitle">Select the categories you want in your shop.</h3>
        <select name="categories[]" multiple style="height:450px;">
            <option value="332">Benchkussens</option>
            <option value="327">Hondenbench</option>
            <option value="329">Hondenkussens</option>
            <option value="330">Hondenmanden</option>
            <option value="321">Hondensnacks</option>
            <option value="312">Kamervolieres</option>
            <option value="314">Knaagdierkooien</option>
            <option value="315">Konijnenhokken</option>
            <option value="319">Krabpalen</option>
            <option value="333">Lijnen &amp; Halsbanden</option>
            <option value="318">Mandjes en kussens</option>
            <option value="322">Overige</option>
            <option value="317">Overige</option>
            <option value="313">Papegaaienkooien</option>
            <option value="328">Puppy Panelen Prof.</option>
            <option value="326">Puppyrennen</option>
            <option value="325">Speelgoed</option>
            <option value="316">Speelgoed voor Katten</option>
            <option value="334">Trimtafels</option>
            <option value="335">Vervoer &amp; Transport</option>
            <option value="324">Verzorging</option>
            <option value="331">Vet Bedding</option>
            <option value="323">Voerbakken</option>
            <option value="320">Warmtelampen</option>
        </select>

        <input type="button" name="previous" class="previous action-button" value="Previous" />
        <input type="button" name="next" class="next action-button" value="Next" />
    </fieldset>
    <fieldset>
        <h2 class="fs-title">Finalize</h2>
        <h3 class="fs-subtitle">Initial setup</h3>
        <p>
            Via SSH, goto the "pdimporter" directory and run "php cron full".
        </p>
        <h3 class="fs-subtitle">Setup cronjob</h3>
        <p>
            Run "php cron full" every day, run "php cron stock" every 30 minutes.
        </p>

        <input type="button" name="previous" class="previous action-button" value="Previous" />
        <input type="submit" name="submit" class="submit action-button" value="Submit" />
    </fieldset>
</form>

<!-- jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<!-- jQuery easing plugin -->
<script src="assets/js/jquery.easing.min.js" type="text/javascript"></script>
<script>
    $(function() {

//jQuery time
        var current_fs, next_fs, previous_fs; //fieldsets
        var left, opacity, scale; //fieldset properties which we will animate
        var animating; //flag to prevent quick multi-click glitches

        $(document).on('keyup keypress', 'form input[type="text"]', function(e) {
            if(e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });

        $(".next").click(function(){
            if(animating) return false;
            animating = true;

            current_fs = $(this).parent();
            next_fs = $(this).parent().next();

            //activate next step on progressbar using the index of next_fs
            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

            //show the next fieldset
            next_fs.show();
            //hide the current fieldset with style
            current_fs.animate({opacity: 0}, {
                step: function(now, mx) {
                    //as the opacity of current_fs reduces to 0 - stored in "now"
                    //1. scale current_fs down to 80%
                    scale = 1 - (1 - now) * 0.2;
                    //2. bring next_fs from the right(50%)
                    left = (now * 50)+"%";
                    //3. increase opacity of next_fs to 1 as it moves in
                    opacity = 1 - now;
                    current_fs.css({'transform': 'scale('+scale+')'});
                    next_fs.css({'left': left, 'opacity': opacity});
                },
                duration: 800,
                complete: function(){
                    current_fs.hide();
                    animating = false;
                },
                //this comes from the custom easing plugin
                easing: 'easeInOutBack'
            });
        });

        $(".previous").click(function(){
            if(animating) return false;
            animating = true;

            current_fs = $(this).parent();
            previous_fs = $(this).parent().prev();

            //de-activate current step on progressbar
            $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

            //show the previous fieldset
            previous_fs.show();
            //hide the current fieldset with style
            current_fs.animate({opacity: 0}, {
                step: function(now, mx) {
                    //as the opacity of current_fs reduces to 0 - stored in "now"
                    //1. scale previous_fs from 80% to 100%
                    scale = 0.8 + (1 - now) * 0.2;
                    //2. take current_fs to the right(50%) - from 0%
                    left = ((1-now) * 50)+"%";
                    //3. increase opacity of previous_fs to 1 as it moves in
                    opacity = 1 - now;
                    current_fs.css({'left': left});
                    previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
                },
                duration: 800,
                complete: function(){
                    current_fs.hide();
                    animating = false;
                },
                //this comes from the custom easing plugin
                easing: 'easeInOutBack'
            });
        });



    });
</script>
</body>
</html>