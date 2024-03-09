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


// Check if uint64 class doesn't exist
if(class_exists("Uint64") === FALSE) {

	// Uint64 class
	final class Uint64 {

		// Component number of bits
		private const COMPONENT_NUMBER_OF_BITS = 16;
		
		// Number of components
		private const NUMBER_OF_COMPONENTS = 64 / self::COMPONENT_NUMBER_OF_BITS;
		
		// Component max value
		private const COMPONENT_MAX_VALUE = 2 ** self::COMPONENT_NUMBER_OF_BITS - 1;
		
		// Components
		private array $components;
		
		// Constructor
		public function __construct(string $serializedUint64 = "") {
		
			// Check if serialized uint64 isn't provided
			if($serializedUint64 === "") {
			
				// Set components to zero
				$this->components = array_fill(0, self::NUMBER_OF_COMPONENTS, 0);
			}
			
			// Otherwise
			else {
			
				// Check if unpacking serialized uint64 failed
				$components = unpack("n" . self::NUMBER_OF_COMPONENTS, $serializedUint64);
				if($components === FALSE) {
				
					// Throw error
					throw new \Exception("Invalid serialized uint64");
				}
				
				// Set components
				$this->components = array_values($components);
			}
		}
		
		// Serialize
		public function serialize(): string {
		
			// Return serialized components
			return pack("n" . self::NUMBER_OF_COMPONENTS, ...$this->components);
		}
		
		// Clone
		public function clone(): Uint64 {
		
			// Return clone
			return new Uint64($this->serialize());
		}
		
		// Increment
		public function increment(): void {
		
			// Increment last component
			++$this->components[self::NUMBER_OF_COMPONENTS - 1];
			
			// Go through all components backwards
			for($i = self::NUMBER_OF_COMPONENTS - 1; $i >= 0; --$i) {
			
				// Check if component has overflowed
				if($this->components[$i] > self::COMPONENT_MAX_VALUE) {
				
					// Fix overflow
					$this->components[$i] &= self::COMPONENT_MAX_VALUE;
					
					// Check if a previous component exists
					if($i > 0) {
					
						// Increment next component
						++$this->components[$i - 1];
					}
				}
				
				// Otherwise
				else {
				
					// Break
					break;
				}
			}
		}
		
		// Get upper uint32
		public function getUpperUint32(): int {
		
			// Return upper half of components
			return ($this->components[0] << self::COMPONENT_NUMBER_OF_BITS) | $this->components[1];
		}
		
		// Get lower uint32
		public function getLowerUint32(): int {
		
			// Return lower half of components
			return ($this->components[2] << self::COMPONENT_NUMBER_OF_BITS) | $this->components[3];
		}
	}
}


?>
