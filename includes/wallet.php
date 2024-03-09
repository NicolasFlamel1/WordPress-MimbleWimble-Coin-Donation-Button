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


// Check if Wallet class doesn't exist
if(class_exists("Wallet") === FALSE) {

	// Wallet class
	final class Wallet {

		// Seed size
		public const SEED_SIZE = 32;
		
		// Extended private key MAC digest
		private const EXTENDED_PRIVATE_KEY_MAC_DIGEST = "sha512";
		
		// Extended private key MAC seed
		private const EXTENDED_PRIVATE_KEY_MAC_SEED = "IamVoldemort";
		
		// Secp256k1-zkp
		private \Nicolasflamel\Secp256k1Zkp\Secp256k1Zkp $secp256k1Zkp;
		
		// BLAKE2b
		private \Nicolasflamel\Blake2b\Blake2b $blake2b;
		
		// Extended private key
		private string $extendedPrivateKey = "";
		
		// Constructor
		public function __construct(private string &$seed = "") {
		
			// Include dependencies
			require_once plugin_dir_path(__FILE__) . "../vendor/autoload.php";
			require_once plugin_dir_path(__FILE__) . "common.php";
			
			// Try
			try {
				
				// Create secp256k1-zkp
				$this->secp256k1Zkp = new \Nicolasflamel\Secp256k1Zkp\Secp256k1Zkp();
				
				// Create BLAKE2b
				$this->blake2b = new \Nicolasflamel\Blake2b\Blake2b();
			}
			
			// Catch errors
			catch(\Throwable $error) {
			
				// Perform destructor
				$this->__destruct();
				
				// Throw error
				throw $error;
			}
			
			// Try
			try {
			
				// Check if a seed isn't provided
				if($this->seed === "") {
				
					// Loop while extended private key's private key isn't a valid secp256k1 private key
					do {
					
						// Create seed
						$this->seed = random_bytes(self::SEED_SIZE);
						
						// Get extended private key from the seed
						$this->extendedPrivateKey = hash_hmac(self::EXTENDED_PRIVATE_KEY_MAC_DIGEST, $this->seed, self::EXTENDED_PRIVATE_KEY_MAC_SEED, TRUE);
						
					} while($this->secp256k1Zkp->isValidPrivateKey($this->extendedPrivateKey) === FALSE);
				}
				
				// Otherwise
				else {
				
					// Check if seed is invalid
					if(strlen($this->seed) !== self::SEED_SIZE) {
					
						// Throw error
						throw new \Exception();
					}
					
					// Get extended private key from the seed
					$this->extendedPrivateKey = hash_hmac(self::EXTENDED_PRIVATE_KEY_MAC_DIGEST, $this->seed, self::EXTENDED_PRIVATE_KEY_MAC_SEED, TRUE);
					
					// Check if extended private key's private key isn't a valid secp256k1 private key
					if($this->secp256k1Zkp->isValidPrivateKey($this->extendedPrivateKey) === FALSE) {
					
						// Throw error
						throw new \Exception();
					}
				}
			}
			
			// Catch errors
			catch(\Throwable $error) {
			
				// Perform destructor
				$this->__destruct();
				
				// Throw error
				throw new \Exception("Creating extended private key failed");
			}
		}
		
		// Destructor
		public function __destruct() {
		
			// Securely clear extended private key
			Common::securelyClear($this->extendedPrivateKey);
			
			// Securely clear seed
			Common::securelyClear($this->seed);
		}
		
		// Display passphrase
		public function displayPassphrase(): void {
		
			// Include dependencies
			require_once plugin_dir_path(__FILE__) . "mnemonic.php";
			
			// Display seed's mnemonic passphrase
			Mnemonic::displayPassphrase($this->seed);
		}
		
		// Add output to slate
		public function addOutputToSlate(Uint64 $identifierPath, array $slate): array | FALSE {
		
			// Include dependencies
			require_once plugin_dir_path(__FILE__) . "crypto.php";
			
			// Check if slate is invalid
			if(is_array($slate) === FALSE || array_key_exists("amount", $slate) === FALSE || is_string($slate["amount"]) === FALSE || preg_match('/^[1-9]\d*$/u', $slate["amount"]) !== 1 || array_key_exists("fee", $slate) === FALSE || is_string($slate["fee"]) === FALSE || preg_match('/^[1-9]\d*$/u', $slate["fee"]) !== 1 || strlen($slate["fee"]) > strlen(Common::UINT64_MAX) || strlen(Common::decimalToHexadecimal($slate["fee"])) > Common::BYTES_IN_A_UINT64 || array_key_exists("num_participants", $slate) === FALSE || $slate["num_participants"] !== 2 || array_key_exists("participant_data", $slate) === FALSE || is_array($slate["participant_data"]) === FALSE || Common::isAssociativeArray($slate["participant_data"]) === TRUE || count($slate["participant_data"]) !== 1 || is_array($slate["participant_data"][0]) === FALSE || array_key_exists("id", $slate["participant_data"][0]) === FALSE || $slate["participant_data"][0]["id"] !== "0" || array_key_exists("public_blind_excess", $slate["participant_data"][0]) === FALSE || is_string($slate["participant_data"][0]["public_blind_excess"]) === FALSE || preg_match('/^[0-9a-f]{' . (Crypto::SECP256K1_PUBLIC_KEY_SIZE * Common::HEXADECIMAL_CHARACTER_SIZE) . '}$/ui', $slate["participant_data"][0]["public_blind_excess"]) !== 1 || array_key_exists("public_nonce", $slate["participant_data"][0]) === FALSE || is_string($slate["participant_data"][0]["public_nonce"]) === FALSE || preg_match('/^[0-9a-f]{' . (Crypto::SECP256K1_PUBLIC_KEY_SIZE * Common::HEXADECIMAL_CHARACTER_SIZE) . '}$/ui', $slate["participant_data"][0]["public_nonce"]) !== 1 || array_key_exists("tx", $slate) === FALSE || is_array($slate["tx"]) === FALSE || array_key_exists("body", $slate["tx"]) === FALSE || is_array($slate["tx"]["body"]) === FALSE || array_key_exists("outputs", $slate["tx"]["body"]) === FALSE || is_array($slate["tx"]["body"]["outputs"]) === FALSE || Common::isAssociativeArray($slate["tx"]["body"]["outputs"]) === TRUE || array_key_exists("kernels", $slate["tx"]["body"]) === FALSE || is_array($slate["tx"]["body"]["kernels"]) === FALSE || Common::isAssociativeArray($slate["tx"]["body"]["kernels"]) === TRUE || count($slate["tx"]["body"]["kernels"]) !== 1 || is_array($slate["tx"]["body"]["kernels"][0]) === FALSE || array_key_exists("features", $slate["tx"]["body"]["kernels"][0]) === FALSE || $slate["tx"]["body"]["kernels"][0]["features"] !== "Plain") {
			
				// Return false
				return FALSE;
			}
			
			// Go through all slate outputs
			foreach($slate["tx"]["body"]["outputs"] as $output) {
			
				// Check if output is invalid
				if(is_array($output) === FALSE || array_key_exists("features", $output) === FALSE || $output["features"] !== "Plain" || array_key_exists("commit", $output) === FALSE || is_string($output["commit"]) === FALSE || preg_match('/^(?:[0-9a-f]{' . Common::HEXADECIMAL_CHARACTER_SIZE . '})+$/ui', $output["commit"]) !== 1) {
				
					// Return false
					return FALSE;
				}
			}
			
			// Check if getting commitment for the output failed
			$commitment = $this->getCommitment($identifierPath, $slate["amount"]);
			if($commitment === FALSE) {
			
				// Return false
				return FALSE;
			}
			
			// Check if getting Bulletproof for the output failed
			$bulletproof = $this->getBulletproof($identifierPath, $slate["amount"]);
			if($bulletproof === FALSE) {
			
				// Return false
				return FALSE;
			}
			
			// Append output to slate's outputs
			$slate["tx"]["body"]["outputs"][] = [
			
				// Features
				"features" => "Plain",
				
				// Commit
				"commit" => bin2hex($commitment),
				
				// Proof
				"proof" => bin2hex($bulletproof)
			];
			
			// Sort slate's outputs
			$errorOccurred = FALSE;
			usort($slate["tx"]["body"]["outputs"], function(array $firstOutput, array $secondOutput) use (&$errorOccurred): int {
			
				// Check if an error occurred or getting first or second hash failed
				$firstHash = $this->blake2b->compute(chr(0) . hex2bin($firstOutput["commit"]));
				$secondHash = $this->blake2b->compute(chr(0) . hex2bin($secondOutput["commit"]));
				if($errorOccurred === TRUE || $firstHash === FALSE || $secondHash === FALSE) {
				
					// Set error occurred to true
					$errorOccurred = TRUE;
					
					// Return outputs are equal
					return 0;
				}
				
				// Otherwise
				else {
				
					// Return first hash compared to second hash
					return strcmp($firstHash, $secondHash);
				}
			});
			
			// Check if an error occurred
			if($errorOccurred === TRUE) {
			
				// Return false
				return FALSE;
			}
			
			// Check if getting blinding factor for the output failed
			$blindingFactor = $this->getBlindingFactor($identifierPath, $slate["amount"]);
			if($blindingFactor === FALSE) {
			
				// Return false
				return FALSE;
			}
			
			// Check if creating private nonce failed
			$privateNonce = $this->secp256k1Zkp->getPrivateNonce();
			if($privateNonce === FALSE) {
			
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting public blind excess failed
			$publicBlindExcess = $this->secp256k1Zkp->getPublicKey($blindingFactor);
			if($publicBlindExcess === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting public nonce failed
			$publicNonce = $this->secp256k1Zkp->getPublicKey($privateNonce);
			if($publicNonce === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting public blind excess sum failed
			$publicBlindExcessSum = $this->secp256k1Zkp->combinePublicKeys([
			
				// Sender's public blind excess
				hex2bin($slate["participant_data"][0]["public_blind_excess"]),
				
				// Public blind excess
				$publicBlindExcess
			]);
			if($publicBlindExcessSum === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting public nonce sum failed
			$publicNonceSum = $this->secp256k1Zkp->combinePublicKeys([
			
				// Sender's public nonce
				hex2bin($slate["participant_data"][0]["public_nonce"]),
				
				// Public nonce
				$publicNonce
			]);
			if($publicNonceSum === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting kernel data failed
			$kernelData = $this->blake2b->compute(chr(0) . str_pad(Common::decimalToHexadecimal($slate["fee"]), Common::BYTES_IN_A_UINT64, chr(0), STR_PAD_LEFT));
			if($kernelData === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if creating partial signature failed
			$partialSignature = $this->secp256k1Zkp->getPartialSingleSignerSignature($blindingFactor, $kernelData, $privateNonce, $publicBlindExcessSum, $publicNonceSum);
			if($partialSignature === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear private nonce
			Common::securelyClear($privateNonce);
			
			// Securely clear blinding factor
			Common::securelyClear($blindingFactor);
			
			// Append participant to slate's participants
			$slate["participant_data"][] = [
			
				// Id
				"id" => "1",
			
				// Public blind excess
				"public_blind_excess" => bin2hex($publicBlindExcess),
				
				// Public nonce
				"public_nonce" => bin2hex($publicNonce),
				
				// Message
				"message" => NULL,
				
				// Partial signature
				"part_sig" => bin2hex($partialSignature),
				
				// Message signature
				"message_sig" => NULL
			];
			
			// Return slate
			return $slate;
		}
		
		// Get commitment
		private function getCommitment(Uint64 $identifierPath, string $value): string | FALSE {
		
			// Check if getting blinding factor failed
			$blindingFactor = $this->getBlindingFactor($identifierPath, $value);
			if($blindingFactor === FALSE) {
			
				// Return false
				return FALSE;
			}
			
			// Check if getting commitment failed
			$commitment = $this->secp256k1Zkp->getCommitment($blindingFactor, $value);
			if($commitment === FALSE) {
			
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear blinding factor
			Common::securelyClear($blindingFactor);
			
			// Return commitment
			return $commitment;
		}
		
		// Get Bulletproof
		private function getBulletproof(Uint64 $identifierPath, string $value): string | FALSE {
		
			// Check if getting blinding factor failed
			$blindingFactor = $this->getBlindingFactor($identifierPath, $value);
			if($blindingFactor === FALSE) {
			
				// Return false
				return FALSE;
			}
			
			// Check if getting commitment failed
			$commitment = $this->secp256k1Zkp->getCommitment($blindingFactor, $value);
			if($commitment === FALSE) {
			
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Get extended private key's private key
			$privateKey = substr($this->extendedPrivateKey, 0, Crypto::SECP256K1_PRIVATE_KEY_SIZE);
			
			// Check if getting private hash failed
			$privateHash = $this->blake2b->compute($privateKey);
			if($privateHash === FALSE) {
			
				// Securely clear private key
				Common::securelyClear($privateKey);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear private key
			Common::securelyClear($privateKey);
			
			// Check if getting private nonce failed
			$privateNonce = $this->blake2b->compute($privateHash, $commitment);
			if($privateNonce === FALSE) {
			
				// Securely clear private hash
				Common::securelyClear($privateHash);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear private hash
			Common::securelyClear($privateHash);
			
			// Check if private nonce isn't a valid secp256k1 private key
			if($this->secp256k1Zkp->isValidPrivateKey($privateNonce) === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting extended private key's public key failed
			$publicKey = $this->secp256k1Zkp->getPublicKey($this->extendedPrivateKey);
			if($publicKey === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Check if getting rewind hash failed
			$rewindHash = $this->blake2b->compute($publicKey);
			if($rewindHash === FALSE) {
			
				// Securely clear public key
				Common::securelyClear($publicKey);
				
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear public key
			Common::securelyClear($publicKey);
			
			// Check if getting rewind nonce failed
			$rewindNonce = $this->blake2b->compute($rewindHash, $commitment);
			if($rewindNonce === FALSE) {
			
				// Securely clear rewind hash
				Common::securelyClear($rewindHash);
				
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear rewind hash
			Common::securelyClear($rewindHash);
			
			// Check if rewind nonce isn't a valid secp256k1 private key
			if($this->secp256k1Zkp->isValidPrivateKey($rewindNonce) === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Create message
			$childPath = $this->getChildPath($identifierPath);
			$message = pack("xxCCN*", Crypto::REGULAR_SWITCH_TYPE, count($childPath), ...$childPath);
			
			// Check if getting Bulletproof failed
			$bulletproof = $this->secp256k1Zkp->getBulletproof($blindingFactor, $value, $rewindNonce, $privateNonce, $message);
			if($bulletproof === FALSE) {
			
				// Securely clear private nonce
				Common::securelyClear($privateNonce);
				
				// Securely clear blinding factor
				Common::securelyClear($blindingFactor);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear private nonce
			Common::securelyClear($privateNonce);
			
			// Securely clear blinding factor
			Common::securelyClear($blindingFactor);
			
			// Return Bulletproof
			return $bulletproof;
		}
		
		// Get blinding factor
		private function getBlindingFactor(Uint64 $identifierPath, string $value): string | FALSE {
		
			// Check if value is invalid
			if(preg_match('/^(?:0|[1-9]\d*)$/u', $value) !== 1) {
			
				// Return false
				return FALSE;
			}
			
			// Check if deriving child extended private key failed
			$childExtendedPrivateKey = Crypto::deriveChildExtendedPrivateKey($this->secp256k1Zkp, $this->extendedPrivateKey, $this->getChildPath($identifierPath));
			if($childExtendedPrivateKey === FALSE) {
			
				// Return false
				return FALSE;
			}
			
			// Check if getting the blinding factor from the child extended private key's private key and value failed
			$blindingFactor = $this->secp256k1Zkp->getBlindingFactor($childExtendedPrivateKey, $value);
			if($blindingFactor === FALSE) {
			
				// Securely clear child extended private key
				Common::securelyClear($childExtendedPrivateKey);
				
				// Return false
				return FALSE;
			}
			
			// Securely clear child extended private key
			Common::securelyClear($childExtendedPrivateKey);
			
			// Return blinding factor
			return $blindingFactor;
		}
		
		// Get child path
		private function getChildPath(Uint64 $identifierPath): array {
		
			// Return child path at a non-standard path used to allow 2^64 unique identifiers that other wallet software won't use
			return [
				$identifierPath->getUpperUint32(),
				$identifierPath->getLowerUint32(),
				0,
				0
			];
		}
	}
}


?>
