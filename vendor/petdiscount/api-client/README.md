Petdiscount PHP API Client
==========

This project is a PHP Library to use the Petdiscount API from your PHP application.

Information about our API can be found on [api.petdiscount.nl](https://api.petdiscount.nl)

## Installation
This project can easily be installed through Composer.

```
composer require petdiscount/api-client
```

## Example: Get orders
```php
<?php

require __DIR__ . '/vendor/autoload.php';

$email = 'info@domeinnaam.com';
$apikey = 'jCcvAfVW6UZqt6s';

$apiclient = new Petdiscount\Api\Client($email, $apikey);

$products = $apiclient->getProducts();
var_dump($products);
```

## More examples
Review the examples in the examples/ folder.

## Support
Need support implementing the Petdiscount API? Feel free to [contact us](http://www.petdiscount.nl/contacts)
