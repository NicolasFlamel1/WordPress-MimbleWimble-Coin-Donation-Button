<?php


// Enforce strict types
declare(strict_types=1);

// Namespace
namespace Nicolasflamel\Secp256k1Zkp;


// Classes

// Secure memory class
final class SecureMemory {

	// Memory
	private ?\FFI\CData $memory;
	
	// Constructor
	public function __construct(private \FFI $library, int $size) {
	
		// Try
		try {
		
			// Allocate memory
			$this->memory = $this->library->new($this->library::arrayType($this->library->type("uint8_t"), [$size]));
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Set memory to null
			$this->memory = NULL;
		}
	}
	
	// Destructor
	public function __destruct() {
	
		// Check if memory exists
		if($this->memory !== NULL) {
		
			// Clear memory
			$this->library::memset($this->memory, 0, $this->library::sizeof($this->memory));
		}
	}
	
	// Get memory
	public function getMemory(): ?\FFI\CData {
	
		// Return memory
		return $this->memory;
	}
	
	// Get string
	public function getString(): string {
	
		// Return string created from memory
		return $this->library::string($this->memory, $this->library::sizeof($this->memory));
	}
}
	
// Secp256k1-zkp class
final class Secp256k1Zkp {

	// Library
	private \FFI $library;
	
	// Constructor
	public function __construct() {
	
		// Check if FFI isn't enabled
		if(ini_get("ffi.enable") !== "1" && (PHP_SAPI !== "cli" || ini_get("ffi.enable") !== "preload")) {
		
			// Throw error
			throw new \Exception("FFI isn't enabled");
		}
		
		// Check if reading header file failed
		$headerFile = @file_get_contents(__DIR__ . "/secp256k1-zkp.h");
		if($headerFile === FALSE) {
		
			// Throw error
			throw new \Exception("Reading header file failed");
		}
		
		// Check operating system
		switch(PHP_OS_FAMILY) {
		
			// Windows
			case "Windows":
			
				
				// Set file
				$file = "secp256k1-zkp-windows-" . (PHP_INT_SIZE * 8) . ".dll";
				
				// Break
				break;
			
			// macOS
			case "Darwin":
			
				// Set file
				$file = "secp256k1-zkp-macos.dylib";
				
				// Break
				break;
			
			// Default
			default:
			
				// Set file
				$file = "secp256k1-zkp-linux-" . (PHP_INT_SIZE * 8) . ".so";
				
				// Break
				break;
		}
		
		// Try
		try {
		
			// Load library
			$this->library = \FFI::cdef(preg_replace('/^extern (?:"C" )?__attribute__\(\(dllexport, visibility\("default"\)\)\) /um', "extern ", $headerFile), __DIR__ . "/$file");
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Throw error
			throw new \Exception("Loading library failed");
		}
		
		// Check if library failed while initializing
		if($this->library->initializingSucceeded() !== TRUE) {
		
			// Throw error
			throw new \Exception("Initializing library failed");
		}
	}
	
	// Is valid private key
	public function isValidPrivateKey(string $privateKey): bool {
	
		// Check if private key is invalid
		if(strlen($privateKey) < $this->library->PRIVATE_KEY_SIZE) {
		
			// Return false
			return FALSE;
		}
		
		// Return if private key is a valid private key
		return $this->library->isValidPrivateKey($privateKey) === TRUE;
	}
	
	// Get public key
	public function getPublicKey(string $privateKey): string | FALSE {
	
		// Check if private key is invalid
		if(strlen($privateKey) < $this->library->PRIVATE_KEY_SIZE) {
		
			// Return false
			return FALSE;
		}
		
		// Check if allocating memory for public key failed
		$publicKey = new SecureMemory($this->library, $this->library->PUBLIC_KEY_SIZE);
		if($publicKey->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if getting private key's public key failed
		if($this->library->getPublicKey($publicKey->getMemory(), $privateKey) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return public key
		return $publicKey->getString();
	}
	
	// Add private keys
	public function addPrivateKeys(string $firstPrivateKey, string $secondPrivateKey): bool {
	
		// Check if first private key or second private key are invalid
		if(strlen($firstPrivateKey) < $this->library->PRIVATE_KEY_SIZE || strlen($secondPrivateKey) < $this->library->PRIVATE_KEY_SIZE) {
		
			// Return false
			return FALSE;
		}
		
		// Return if adding second private key to the first private key was successful
		return $this->library->addPrivateKeys($firstPrivateKey, $secondPrivateKey) === TRUE;
	}
	
	// Get blinding factor
	public function getBlindingFactor(string $blind, string $value): string | FALSE {
	
		// Check if blind or value are invalid
		if(strlen($blind) < $this->library->PRIVATE_KEY_SIZE || preg_match('/^(?:0|[1-9]\d*)$/u', $value) !== 1) {
		
			// Return false
			return FALSE;
		}
		
		// Check if allocating memory for blinding factor failed
		$blindingFactor = new SecureMemory($this->library, $this->library->BLINDING_FACTOR_SIZE);
		if($blindingFactor->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if getting blinding factor failed
		if($this->library->getBlindingFactor($blindingFactor->getMemory(), $blind, $value) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return blinding factor
		return $blindingFactor->getString();
	}
	
	// Get commitment
	public function getCommitment(string $blindingFactor, string $value): string | FALSE {
	
		// Check if blinding factor or value are invalid
		if(strlen($blindingFactor) !== $this->library->BLINDING_FACTOR_SIZE || preg_match('/^(?:0|[1-9]\d*)$/u', $value) !== 1) {
		
			// Return false
			return FALSE;
		}
		
		// Check if allocating memory for commitment failed
		$commitment = new SecureMemory($this->library, $this->library->COMMITMENT_SIZE);
		if($commitment->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if getting commitment failed
		if($this->library->getCommitment($commitment->getMemory(), $blindingFactor, $value) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return commitment
		return $commitment->getString();
	}
	
	// Get Bulletproof
	public function getBulletproof(string $blindingFactor, string $value, string $rewindNonce, string $privateNonce, string $message): string | FALSE {
	
		// Check if blinding factor, value, rewind nonce, private nonce, or message are invalid
		if(strlen($blindingFactor) !== $this->library->BLINDING_FACTOR_SIZE || preg_match('/^(?:0|[1-9]\d*)$/u', $value) !== 1 || strlen($rewindNonce) !== $this->library->SCALAR_SIZE || strlen($privateNonce) !== $this->library->SCALAR_SIZE || strlen($message) !== $this->library->BULLETPROOF_MESSAGE_SIZE) {
		
			// Return false
			return FALSE;
		}
		
		// Check if allocating memory for Bulletproof failed
		$bulletproof = new SecureMemory($this->library, $this->library->BULLETPROOF_SIZE);
		if($bulletproof->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if getting Bulletproof failed
		if($this->library->getBulletproof($bulletproof->getMemory(), $blindingFactor, $value, $rewindNonce, $privateNonce, $message) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return Bulletproof
		return $bulletproof->getString();
	}
	
	// Get private nonce
	public function getPrivateNonce(): string | FALSE {
	
		// Check if allocating memory for private nonce failed
		$privateNonce = new SecureMemory($this->library, $this->library->SCALAR_SIZE);
		if($privateNonce->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if getting private nonce failed
		if($this->library->getPrivateNonce($privateNonce->getMemory()) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return private nonce
		return $privateNonce->getString();
	}
	
	// Combine public keys
	public function combinePublicKeys(array $publicKeys): string | FALSE {
	
		// Check if public keys are invalid
		if(count($publicKeys) === 0) {
		
			// Return false
			return FALSE;
		}
		
		// Go through all public keys
		foreach($publicKeys as $publicKey) {
		
			// Check if public key is invalid
			if(is_string($publicKey) === FALSE || strlen($publicKey) !== $this->library->PUBLIC_KEY_SIZE) {
			
				// Return false
				return FALSE;
			}
		}
		
		// Check if allocating memory for combined public key failed
		$combinedPublicKey = new SecureMemory($this->library, $this->library->PUBLIC_KEY_SIZE);
		if($combinedPublicKey->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Try
		try {
		
			// Check if allocating memory for serialized public keys failed
			$serializedPublicKeys = $this->library->new($this->library::arrayType($this->library->type("const char *"), [count($publicKeys)]));
			if($serializedPublicKeys === NULL) {
			
				// Throw error
				throw new \Exception();
			}
			
			// Go through all public keys
			$serializedPublicKeysReferences = [];
			for($i = 0; $i < count($publicKeys); ++$i) {
			
				// Check if allocateing memory for serialized public key failed
				$serializedPublicKey = $this->library->new($this->library::arrayType($this->library->type("const char"), [strlen($publicKeys[$i])]));
				if($serializedPublicKey === NULL) {
				
					// Throw error
					throw new \Exception();
				}
				
				// Set serialzied public key to the public key
				$this->library::memcpy($serializedPublicKey, $publicKeys[$i], strlen($publicKeys[$i]));
				
				// Set serialized public key in the list of serialized public keys
				$serializedPublicKeys[$i] = $this->library->cast("const char *", $serializedPublicKey);
				
				// Keep reference to serialize public key to prevent memory from being freed
				$serializedPublicKeysReferences[] = $serializedPublicKey;
			}
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Return false
			return FALSE;
		}
		
		// Check if combining public keys failed
		if($this->library->combinePublicKeys($combinedPublicKey->getMemory(), $serializedPublicKeys, count($publicKeys)) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return combined public key
		return $combinedPublicKey->getString();
	}
	
	// Get partial single-signer signature
	public function getPartialSingleSignerSignature(string $privateKey, string $message, string $privateNonce, string $publicKey, string $publicNonce): string | FALSE {
	
		// Check if private key, message, private nonce, public key, or public nonce are invalid
		if(strlen($privateKey) !== $this->library->PRIVATE_KEY_SIZE || strlen($message) !== $this->library->SINGLE_SIGNER_SIGNATURE_MESSAGE_SIZE || strlen($privateNonce) !== $this->library->SCALAR_SIZE || strlen($publicKey) !== $this->library->PUBLIC_KEY_SIZE || strlen($publicNonce) !== $this->library->PUBLIC_KEY_SIZE) {
		
			// Return false
			return FALSE;
		}
		
		// Check if allocating memory for signature failed
		$signature = new SecureMemory($this->library, $this->library->SINGLE_SIGNER_SIGNATURE_SIZE);
		if($signature->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if getting partial single-signer signature failed
		if($this->library->getPartialSingleSignerSignature($signature->getMemory(), $privateKey, $message, $privateNonce, $publicKey, $publicNonce) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return signature
		return $signature->getString();
	}
	
	// Public key to commitment
	public function publicKeyToCommitment(string $publicKey): string | FALSE {
	
		// Check if public key is invalid
		if(strlen($publicKey) !== $this->library->PUBLIC_KEY_SIZE) {
		
			// Return false
			return FALSE;
		}
		
		// Check if allocating memory for commitment failed
		$commitment = new SecureMemory($this->library, $this->library->COMMITMENT_SIZE);
		if($commitment->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if converting public key to commitment failed
		if($this->library->publicKeyToCommitment($commitment->getMemory(), $publicKey) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return commitment
		return $commitment->getString();
	}
}


?>
