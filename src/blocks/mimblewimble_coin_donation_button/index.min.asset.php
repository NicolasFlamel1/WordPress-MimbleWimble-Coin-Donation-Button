<?php


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}

// Return
return [

	// Dependencies
	"dependencies" => ["wp-block-editor", "wp-blocks", "wp-element", "wp-i18n", "MimbleWimbleCoinDonationButton_blocks_script"],
	
	// Version
	"version" => "0.1.0"
];


?>
