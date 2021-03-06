<?php
/**
 * Petdiscount Magento Importer
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 *
 * Not the fastest importer ever made
 * But feel free to modify & use!
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * INSTALL:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 * Run install.php in your browser
 *
 * Set a cronjob to "cron.php stock" every 30 minutes
 * Set a cronjob to "cron.php full" once a day
 *
 * Logs will be saved to /var/petdiscount.log
 *
 */

$execution_time_start = microtime(true);

if (!file_exists("config/config.json")) {
    echo "not installed";
    die();
}

require_once "vendor/autoload.php";

foreach (glob("app/controllers/*") as $controller) {
    require $controller;
}

require_once '../app/Mage.php'; //include magento

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

/**
 * Initiate Petdiscount API
 */

$config = json_decode(file_get_contents("config/config.json"));
$apiclient = new Petdiscount\Api\Client($config->email, $config->apikey);

/**
 * Initiate Controllers
 */

$productcontroller = new ProductController();
$imagecontroller = new ImageController();
$categorycontroller = new CategoryController();

/**
 * Testing zone
 */


/**
 * Uncomment to clear product database before import
 * This includes also NOT petdiscount products
 * Only for testing purposes, NEVER use this in production
 */

if ($argv[1] == "delete") {
    $productcontroller->RemoveProducts();
    echo "All products removed";
    die;
}

/**
 * Get products from API
 */
$products = $apiclient->getProducts();

/**
 * Arguments passed to script
 */

if ($argv[1] == "stock") {
    $onlystock = TRUE;
    Mage::log('cron/stock initiated', null, 'petdiscount.log');
}

if ($argv[1] == "full") {
    $fullimport = TRUE;
    Mage::log('cron/full initiated', null, 'petdiscount.log');
    /**
     * Download images on full import
     */

    $imagecontroller->DownloadImages();
}


/**
 * Set all products to "not imported"
 */

$productcontroller->MarkNotImported();



/**
 * Loop through products
 */
foreach ($products['data'] as $product) {



    /**
     * Only import products from whitelisted categories
     *
     */
    if($config->categories[0] != ""){
        if (!in_array($product['category']['subid'], $config->categories)) {
            continue;
        }
    }



    /**
     * Reset data
     */
    $grouped = false;

    /**
     * Check if product is grouped
     */

    if (count($product['variants']) > 1 && $product['variants'][0]['sku'] != $product['sku']) {
        $grouped = true;
    }

    /**
     * Check if product exists
     */
    $magento_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $product['sku']);


    if (!$magento_product) {

        /**
         * Only add new products on full update
         */

        if($fullimport != TRUE){ continue; }

        /**
         * Product not found, create a new one!
         */
        if ($grouped == true) {
            /**
             * Create grouped product + children
             */
            echo $productcontroller->CreateGroupedProduct($product) . PHP_EOL;
        } else {
            /**
             * Create simple product
             */
            echo $productcontroller->CreateSimpleProduct($product) . PHP_EOL;
        }

    } else {
        /**
         * Update product
         *
         */

        if ($grouped == true) {
            /**
             * Update grouped product + children
             */
            echo $productcontroller->UpdateGroupedProduct($product) . PHP_EOL;
        } else {
            /**
             * Update simple product
             */
            echo $productcontroller->UpdateSimpleProduct($product) . PHP_EOL;
        }

    }


}


/**
 * Disable all products who didn't get the 'imported' flag.
 * So if a product isn't available anymore, it will be disabled.
 */

$productcontroller->DisableNotImported();



$execution_time_end = microtime(true);
$execution_time = $execution_time_end - $execution_time_start;
Mage::log("Finished in $execution_time seconds", null, 'petdiscount.log');
die;
