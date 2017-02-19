<?php
require('../src/Client.php');

// Start 
$apiclient = new Petdiscount\Api\Client('--email--', '--apikey--');
$sku = "--sku--";

// Retrieve product by SKU
$product = $apiclient->getProduct($sku);

var_dump($product);
