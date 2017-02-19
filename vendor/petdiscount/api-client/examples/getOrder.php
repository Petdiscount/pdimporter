<?php
require('../src/Client.php');

// Start
$apiclient = new Petdiscount\Api\Client('--email--', '--apikey--');
$ordernumber = "--petdiscount ordernumber--";

// Retrieve all your orders from Petdiscount
$order = $apiclient->getOrder($ordernumber);

var_dump($order);
