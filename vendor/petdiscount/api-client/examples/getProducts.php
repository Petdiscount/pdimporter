<?php
require('../src/Client.php');

// Start 
$apiclient = new Petdiscount\Api\Client('--email--', '--apikey--');

// Retrieve all products from Petdiscount
$products = $apiclient->getProducts();

var_dump($products);
