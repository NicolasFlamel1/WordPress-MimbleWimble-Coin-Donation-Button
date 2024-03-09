<?php


// Enforce strict types
declare(strict_types=1);

// Namespace
namespace MimbleWimbleCoinDonationButton;


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}


// Check if crypto class doesn't exist
if(class_exists("Crypto") === FALSE) {

	// Crypto class
	final class Crypto {

		// Secp256k1 private key size
		public const SECP256K1_PRIVATE_KEY_SIZE = 32;
		
		// Secp256k1 public key size
		public const SECP256K1_PUBLIC_KEY_SIZE = 33;
		
		// Regular switch type
		public const REGULAR_SWITCH_TYPE = 1;
		
		// Derive child extended private key MAC digest
		private const DERIVE_CHILD_EXTENDED_PRIVATE_KEY_MAC_DIGEST = "sha512";
		
		// Path hardened mask
		private const PATH_HARDENED_MASK = 1 << (Common::BITS_IN_A_UINT32 - 1);
		
		// Derive child extended private key
		public static function deriveChildExtendedPrivateKey(\Nicolasflamel\Secp256k1Zkp\Secp256k1Zkp $secp256k1Zkp, string $extendedPrivateKey, array $childPath): string | FALSE {
		
			// Initialize child extended private key to the extended private key
			$childExtendedPrivateKey = $extendedPrivateKey;
			
			// Try
			try {
			
				// Go through all paths
				foreach($childPath as $currentPath) {
				
					// Get child extended private key's chain code
					$chainCode = substr($childExtendedPrivateKey, self::SECP256K1_PRIVATE_KEY_SIZE);
					
					// Try
					try {
					
						// Initialize hash context with the chain code
						$hashContext = hash_init(self::DERIVE_CHILD_EXTENDED_PRIVATE_KEY_MAC_DIGEST, HASH_HMAC, $chainCode);
					}
					
					// Catch errors
					catch(\Throwable $error) {
					
						// Securely clear chain code
						Common::securelyClear($chainCode);
						
						// Throw error
						throw $error;
					}
					
					// Securely clear chain code
					Common::securelyClear($chainCode);
					
					// Check if path is hardened
					if(($currentPath & self::PATH_HARDENED_MASK) !== 0) {
					
						// Get child extended private key's private key
						$privateKey = substr($childExtendedPrivateKey, 0, self::SECP256K1_PRIVATE_KEY_SIZE);
						
						// Update hash context with zero and the private key
						hash_update($hashContext, chr(0));
						hash_update($hashContext, $privateKey);
						
						// Securely clear private key
						Common::securelyClear($privateKey);
					}
					
					// Otherwise
					else {
					
						// Check if getting the child extended private key's public key failed
						$publicKey = $secp256k1Zkp->getPublicKey($childExtendedPrivateKey);
						if($publicKey === FALSE) {
						
							// Throw error
							throw new \Exception();
						}
						
						// Update hash context with the public key
						hash_update($hashContext, $publicKey);
						
						// Securely clear public key
						Common::securelyClear($publicKey);
					}
					
					// Update hash with the current path
					hash_update($hashContext, pack("N", $currentPath));
					
					// Get the new extended private key from the hash context
					$newExtendedPrivateKey = hash_final($hashContext, TRUE);
					
					// Check if new extended private key's private key isn't a valid secp256k1 private key
					if($secp256k1Zkp->isValidPrivateKey($newExtendedPrivateKey) === FALSE) {
					
						// Securely clear new extended private key
						Common::securelyClear($newExtendedPrivateKey);
						
						// Throw error
						throw new \Exception();
					}
					
					// Check if adding child extended private key's private key to the new extended private key's private key failed
					if($secp256k1Zkp->addPrivateKeys($newExtendedPrivateKey, $childExtendedPrivateKey) === FALSE) {
					
						// Securely clear new extended private key
						Common::securelyClear($newExtendedPrivateKey);
						
						// Throw error
						throw new \Exception();
					}
					
					// Securely clear child extended private key
					Common::securelyClear($childExtendedPrivateKey);
					
					// Set child extended private key to the new extended private key
					$childExtendedPrivateKey = $newExtendedPrivateKey;
					
					// Securely clear new extended private key
					Common::securelyClear($newExtendedPrivateKey);
				}
			}
			
			// Catch errors
			catch(\Throwable $error) {
			
				// Securely clear child extended private key
				Common::securelyClear($childExtendedPrivateKey);
				
				// Return false
				return FALSE;
			}
			
			// Return child extended private key
			return $childExtendedPrivateKey;
		}
	}
}


?>
