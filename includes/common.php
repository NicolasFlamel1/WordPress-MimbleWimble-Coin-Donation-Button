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


// Check if common class doesn't exist
if(class_exists("Common") === FALSE) {

	// Common class
	final class Common {

		// Bits in a byte
		public const BITS_IN_A_BYTE = 8;
		
		// Bits in a uint32
		public const BITS_IN_A_UINT32 = 4 * self::BITS_IN_A_BYTE;
		
		// Bytes in a uint64
		public const BYTES_IN_A_UINT64 = 64 / self::BITS_IN_A_BYTE;
		
		// Uint64 max
		public const UINT64_MAX = "18446744073709551615";
		
		// Hexadecimal character size
		public const HEXADECIMAL_CHARACTER_SIZE = 2;
		
		// Decimal number base
		private const DECIMAL_NUMBER_BASE = 10;
		
		// Hexadecimal number base
		private const HEXADECIMAL_NUMBER_BASE = 16;
		
		// Securely clear
		public static function securelyClear(string &$data): void {
		
			// Go through all bytes in the data
			for($i = 0; $i < strlen($data); ++$i) {
			
				// Clear byte
				$data[$i] = chr(0);
			}
		}
		
		// Is associative array
		public static function isAssociativeArray(array $array): bool {
		
			// Return if array is an associative array
			return array_values($array) !== $array;
		}
		
		// Decimal to hexadecimal
		public static function decimalToHexadecimal(string $decimalDigits): string {
		
			// Check if there's no decimal digits
			if(strlen($decimalDigits) === 0) {
			
				// Return empty string
				return "";
			}
			
			// Initialize hexadecimal digits
			$hexadecimalDigits = [0];
			
			// Go through all decimal digits
			for($i = 0; $i < strlen($decimalDigits); ++$i) {
			
				// Set carry to the digits as a number
				$carry = ord($decimalDigits[$i]) - ord("0");
				
				// Go through all hexadecimal digits
				for($j = 0; $j < count($hexadecimalDigits); ++$j) {
				
					// Update carry to include the decimal digit
					$carry += $hexadecimalDigits[$j] * self::DECIMAL_NUMBER_BASE;
					
					// Update hexadecimal digit
					$hexadecimalDigits[$j] = $carry % self::HEXADECIMAL_NUMBER_BASE;
					
					// Update carry
					$carry = (int)($carry / self::HEXADECIMAL_NUMBER_BASE);
				}
				
				// Check if carry exists
				if($carry !== 0) {
				
					// Append carry to hexadecimal digits
					$hexadecimalDigits[] = $carry;
				}
			}
			
			// Check if there isn't an even number of hexadecimal digits
			if(count($hexadecimalDigits) % self::HEXADECIMAL_CHARACTER_SIZE !== 0) {
			
				// Append zero to hexadecimal digits
				$hexadecimalDigits[] = 0;
			}
			
			// Initialize result
			$result = "";
			
			// Go through all pairs of hexadecimal digits
			for($i = count($hexadecimalDigits) - self::HEXADECIMAL_CHARACTER_SIZE; $i >= 0; $i -= self::HEXADECIMAL_CHARACTER_SIZE) {
			
				// Append pairs of hexadecimal digits as a character to the result
				$result .= chr(($hexadecimalDigits[$i + 1] << (int)(self::BITS_IN_A_BYTE / 2)) | $hexadecimalDigits[$i]);
			}
			
			// Return result
			return $result;
		}
	}
}


?>
