<?php
require('../src/Client.php');

// Start 
$apiclient = new Petdiscount\Api\Client('--email--', '--apikey--');


// Add a new order to Petdiscount
$order = array(
    'firstname' => 'John',
    'lastname' => 'Doe',
    'street' => 'Hoofdstraat',
    'house_number' => '1',
    'house_number_ext' => '',
    'postalcode' => '1234AB',
    'city' => 'Amsterdam',
    'country' => 'NL',
    'email' => 'info@petdiscount.nl',
    'phone' => '0612341234',
    'pickup' => '0',
    'packingslip' => '',
    'ordernumber' => 'PO8257257',
    'products' => array(
        '16-01-122' => 1,
        '22-YL23791' => 6
    )
);

$result = $apiclient->addOrder($order);
var_dump($result);