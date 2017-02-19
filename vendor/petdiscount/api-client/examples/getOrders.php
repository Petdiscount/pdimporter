<?php
require('../src/Client.php');

// Start 
$apiclient = new Petdiscount\Api\Client('--email--', '--apikey--');

// Retrieve all products from Petdiscount
$orders = $apiclient->getOrders();

var_dump($orders);