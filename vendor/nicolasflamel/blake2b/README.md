# BLAKE2b PHP Library

### Description
PHP library for parts of [the official BLAKE2b implementation](https://github.com/BLAKE2/BLAKE2).

### Installing
Run the following command from the root of your project to install this library and configure your project to use it.
```
composer require nicolasflamel/blake2b
```

### Usage
After a `Blake2b` object has been created, it can be used to compute the BLAKE2b hash of a string with an optional key and size;

The following code briefly shows how to use this library. A more complete example is available [here](https://github.com/NicolasFlamel1/BLAKE2b-PHP-Library/tree/master/example).
```
<?php

// Require dependencies
require_once __DIR__ . "/vendor/autoload.php";

// Use BLAKE2b
use Nicolasflamel\Blake2b\Blake2b;

// Initialize BLAKE2b
$blake2b = new Blake2b();

// Compute hash
$hash = $blake2b->compute(hex2bin("0102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f20"), hex2bin("0102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f20"), 32);

?>
```

### Functions
1. BLAKE2b constructor: `constructor(): Blake2b`

   This constructor is used to create a `Blake2b` object and it returns the following value:
   * `Blake2b`: An `Blake2b` object.

2. BLAKE2b compute method: `compute(string $input, ?string $key = NULL, int $resultSize = 32): string | FALSE`

   This method is used to compute the BLAKE2b hash for provided input and it accepts the following parameters:
   * `string $input`: The input to hash.
   * `?string $key` (optional): The key to use when computing the hash. If `NULL` then no key will be used.
   * `int $resultSize` (optional): The desired size of the hash.

   This method returns the following values:
   * `string`: The BLAKE2b hash for the provided input using the key if provided and at the specified size.
   * `FALSE`: Computing the hash failed.
