<?php


// Enforce strict types
declare(strict_types=1);

// Namespace
namespace Nicolasflamel\Blake2b;


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
	
// BLAKE2b class
final class Blake2b {

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
		$headerFile = @file_get_contents(__DIR__ . "/blake2b.h");
		if($headerFile === FALSE) {
		
			// Throw error
			throw new \Exception("Reading header file failed");
		}
		
		// Check operating system
		switch(PHP_OS_FAMILY) {
		
			// Windows
			case "Windows":
			
				
				// Set file
				$file = "blake2b-windows-" . (PHP_INT_SIZE * 8) . ".dll";
				
				// Break
				break;
			
			// macOS
			case "Darwin":
			
				// Set file
				$file = "blake2b-macos.dylib";
				
				// Break
				break;
			
			// Default
			default:
			
				// Set file
				$file = "blake2b-linux-" . (PHP_INT_SIZE * 8) . ".so";
				
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
	}
	
	// Compute
	public function compute(string $input, ?string $key = NULL, int $resultSize = 32): string | FALSE {
	
		// Check if allocating memory for result failed
		$result = new SecureMemory($this->library, $resultSize);
		if($result->getMemory() === NULL) {
		
			// Return false
			return FALSE;
		}
		
		// Check if computing failed
		if($this->library->compute($result->getMemory(), $resultSize, $input, strlen($input), $key, ($key === NULL) ? 0 : strlen($key)) !== TRUE) {
		
			// Return false
			return FALSE;
		}
		
		// Return result
		return $result->getString();
	}
}


?>
