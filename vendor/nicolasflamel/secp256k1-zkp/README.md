# Secp256k1-zkp PHP Library

### Description
PHP library for parts of [libsecp256k1-zkp](https://github.com/mimblewimble/secp256k1-zkp).

### Installing
Run the following command from the root of your project to install this library and configure your project to use it.
```
composer require nicolasflamel/secp256k1-zkp
```

### Usage
After an `Secp256k1Zkp` object has been created, it can be used to perform all the secp256k1-zkp functions that this library implements.

The following code briefly shows how to use this library. A more complete example is available [here](https://github.com/NicolasFlamel1/Secp256k1-zkp-PHP-Library/tree/master/example).
```
<?php

// Require dependencies
require_once __DIR__ . "/vendor/autoload.php";

// Use secp256k1-zkp
use Nicolasflamel\Secp256k1Zkp\Secp256k1Zkp;

// Initialize secp256k1-zkp
$secp256k1Zkp = new Secp256k1Zkp();

// Get if private key is valid
$privateKeyIsValid = $secp256k1Zkp->isValidPrivateKey(hex2bin("8c3882fbd7966085e760e000b1ea9eb1ad3df1eec02e720adaa5104c6bd9fd88"));

// Get public key
$publicKey = $secp256k1Zkp->getPublicKey(hex2bin("8c3882fbd7966085e760e000b1ea9eb1ad3df1eec02e720adaa5104c6bd9fd88"));

// Add private keys
$privateKeySum = hex2bin("8c3882fbd7966085e760e000b1ea9eb1ad3df1eec02e720adaa5104c6bd9fd88");
$secp256k1Zkp->addPrivateKeys($privateKeySum, hex2bin("7f64b1861b9139c0601f637957826da80bb3773adbc8c70265a0c3edb6fda33b"));

// Get blinding factor
$blindingFactor = $secp256k1Zkp->getBlindingFactor(hex2bin("8c3882fbd7966085e760e000b1ea9eb1ad3df1eec02e720adaa5104c6bd9fd88"), "123456789");

// Get commitment
$commitment = $secp256k1Zkp->getCommitment(hex2bin("8c3882fbd7966085e760e000b1ea9eb1ad3df1eec02e720adaa5104c6bd9fd88"), "123456789");

// Get Bulletproof
$bulletproof = $secp256k1Zkp->getBulletproof(hex2bin("08883a3f816419d4ce5bf44e320c24c5b09b0621c70fb780d7a35c86570bd354"), "123456789", hex2bin("74265668b4c2d901b5835de953f3ba1e1d7ce88b7e8ca89e6256145404aac330"), hex2bin("583f58e1515282cfd576319867afb4f612461993c0061a344b89e13056725eab"), hex2bin("000000021cadecb940302338354182ee67a213ba"));

// Get private nonce
$privateNonce = $secp256k1Zkp->getPrivateNonce();

// Combine public keys
$combinedPublicKey = $secp256k1Zkp->combinePublicKeys([hex2bin("03e7e3dd547cc3171ffdc403824fcc5d5d03712a29f459ca10668c2864c088e951"), hex2bin("033c44db7d8accfb8d89ada18934c4e5daf9902df8638a3e959d8d57aa6ca977cd"), hex2bin("03011d606ad1bd8470d1b6dbf6cb5eae25e42ea1b55915a0899b5a26020c59bd6f")]);

// Get partial single-signer signature
$partialSingleSignerSignature = $secp256k1Zkp->getPartialSingleSignerSignature(hex2bin("8c3882fbd7966085e760e000b1ea9eb1ad3df1eec02e720adaa5104c6bd9fd88"), hex2bin("10f3f976ecfd891b95ac8dddec7ca41685f5e5b034facba4a3ef8c3d319fea54"), hex2bin("e770cbe631b86e65417355157d4696c0a9eff485a8f0a0b005a4e86e5e31f9c9"), hex2bin("02500d2963a767c6be0121b2ca0350f54b37473be066a2d30dbbc4065d5b1fee41"), hex2bin("03fdfbccfaecc71ce664b2e03b8fb535ef8497ea743a0d2644cfb267524b6c7cee"));

// Convert public key to commitment
$commitment = $secp256k1Zkp->publicKeyToCommitment(hex2bin("02883a3f816419d4ce5bf44e320c24c5b09b0621c70fb780d7a35c86570bd35475"));

?>
```

### Functions
1. Secp256k1-zkp constructor: `constructor(): Secp256k1Zkp`

   This constructor is used to create a `Secp256k1Zkp` object and it returns the following value:
   * `Secp256k1Zkp`: An `Secp256k1Zkp` object.

2. Secp256k1-zkp is valid private key method: `isValidPrivateKey(string $privateKey): bool`

   This method is used to check if a provided private key is valid and it accepts the following parameters:
   * `string $privateKey`: The private key to validate.

   This method returns the following values:
   * `bool`: `TRUE` if the private key is valid or `FALSE` if it is not.

3. Secp256k1-zkp get public key method: `getPublicKey(string $privateKey): string | FALSE`

   This method is used to get a provided private key's public key and it accepts the following parameters:
   * `string $privateKey`: The private key to get the public key for.

   This method returns the following values:
   * `string`: The private key's public key.
   * `FALSE`: Getting the public key failed.

4. Secp256k1-zkp add private keys method: `addPrivateKeys(string $firstPrivateKey, string $secondPrivateKey): bool`

   This method is used to add two private keys and it accepts the following parameters:
   * `string $firstPrivateKey`: The private key to add the second private key to. This variable will contain the sum of the private keys if the function returned `TRUE`.
   * `string $secondPrivateKey`: The private key to add to the first private key.

   This method returns the following values:
   * `bool`: `TRUE` if adding private keys was successful or `FALSE` if it failed.

5. Secp256k1-zkp get blinding factor method: `getBlindingFactor(string $blind, string $value): string | FALSE`

   This method is used to get the blinding factor from a provided blind and value and it accepts the following parameters:
   * `string $blind`: The blind to use.
   * `string $value`: The value to use. This must be a non-negative integer.

   This method returns the following values:
   * `string`: The blinding factor for the provided blind and value.
   * `FALSE`: Getting the blinding factor failed.

6. Secp256k1-zkp get commitment method: `getCommitment(string $blindingFactor, string $value): string | FALSE`

   This method is used to get the commitment for a provided value using a provided blinding factor and it accepts the following parameters:
   * `string $blindingFactor`: The blinding factor to use.
   * `string $value`: The value to commit. This must be a non-negative integer.

   This method returns the following values:
   * `string`: The commitment for the provided value using the provided blinding factor.
   * `FALSE`: Getting the commitment failed.

7. Secp256k1-zkp get Bulletproof method: `getBulletproof(string $blindingFactor, string $value, string $rewindNonce, string $privateNonce, string $message): string | FALSE`

   This method is used to get Bulletproof for a provided value committed with a provided blinding factor using a provided rewind nonce, private nonce, and message and it accepts the following parameters:
   * `string $blindingFactor`: The blinding factor use to commit the value.
   * `string $value`: The value committed to. This must be a non-negative integer.
   * `string $rewindNonce`: The rewind nonce to use.
   * `string $privateNonce`: The private nonce to use.
   * `string $message`: The message to use.

   This method returns the following values:
   * `string`: The Bulletproof for the provided value committed with the provided blinding factor using the provided rewind nonce, private nonce, and message.
   * `FALSE`: Getting the Bulletproof failed.

8. Secp256k1-zkp get private nonce method: `getPrivateNonce(): string | FALSE`

   This method is used to get a random private nonce that can be used when creating a partial single-signer signature and it returns the following values:
   * `string`: A private nonce.
   * `FALSE`: Getting a private nonce failed.

9. Secp256k1-zkp combine public keys method: `combinePublicKeys(array $publicKeys): string | FALSE`

   This method is used to get the combined public key for provided public keys and it accepts the following parameters:
   * `array $publicKeys`: The public keys to combine.

   This method returns the following values:
   * `string`: The combined public key.
   * `FALSE`: Combing public keys failed.

0. Secp256k1-zkp get partial single-signer signature method: `getPartialSingleSignerSignature(string $privateKey, string $message, string $privateNonce, string $publicKey, string $publicNonce): string | FALSE`

   This method is used to get the partial single-signer signature for a provided message signed with a provided private key using a provided private nonce, public key, and public nonce and it accepts the following parameters:
   * `string $privateKey`: The private key to use.
   * `string $message`: The message to sign.
   * `string $privateNonce`: The private nonce to use.
   * `string $publicKey`: The public key to use.
   * `string $publicNonce`: The public nonce to use.

   This method returns the following values:
   * `string`: The partial single-signer signature for the provided message signed with the provided private key using the provided private nonce, public key, and public nonce.
   * `FALSE`: Getting the partial single-signer signature failed.

1. Secp256k1-zkp public key to commitment method: `publicKeyToCommitment(string $publicKey): string | FALSE`

   This method is used to convert a provided public key to a commitment and it accepts the following parameters:
   * `string $publicKey`: The public key to convert to a commitment.

   This method returns the following values:
   * `string`: The commitment.
   * `FALSE`: Converting the public key failed.
