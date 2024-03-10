<?php


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}


?>

<div <?= get_block_wrapper_attributes([

	// Style
	"style" => "background: transparent !important; background-color: transparent !important;"
	
]); ?>>

	<a <?= get_block_wrapper_attributes(); ?> href="<?= esc_url(get_rest_url(NULL, "donate-mimblewimble-coin")); ?>" aria-label="<?= esc_attr__("Open MimbleWimble Coin donation URL", "mimblewimble-coin-donation-button"); ?>" target="_blank" rel="nofollow noopener noreferrer" tabindex="-1">
	
		<img src="<?= esc_url(plugins_url("mimblewimble_coin_logo.svg", __FILE__)); ?>" alt="<?= esc_attr__("MimbleWimble Coin logo", "mimblewimble-coin-donation-button"); ?>">
		
		<p>
		
			<span><?= esc_html__("MimbleWimble Coin", "mimblewimble-coin-donation-button"); ?></span>
			
			<span><?= esc_html__("Donate now", "mimblewimble-coin-donation-button"); ?></span>
			
		</p>
		
		<img class="mimblewimble-coin-donation-button-mimblewimble-coin-donation-button_hide<?= (array_key_exists("invertQrCodeColor", $attributes) === TRUE && $attributes["invertQrCodeColor"] === TRUE) ? " mimblewimble-coin-donation-button-mimblewimble-coin-donation-button_invert" : ""; ?>" alt="<?= esc_attr__("MimbleWimble Coin donation address QR code", "mimblewimble-coin-donation-button"); ?>">
		
	</a>
	
</div>
