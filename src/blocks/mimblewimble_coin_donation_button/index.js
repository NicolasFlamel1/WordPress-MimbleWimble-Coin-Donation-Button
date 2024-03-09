// Main function
(() => {

	// Use strict
	"use strict";
	
	// Register block type
	wp.blocks.registerBlockType("mimblewimble-coin-donation-button/mimblewimble-coin-donation-button", {
	
		// Edit
		edit: () => wp.element.createElement("div", wp.blockEditor.useInnerBlocksProps(wp.blockEditor.useBlockProps({
			
			// Reference
			ref: (element) => {
			
				// Check if element exists
				if(element !== null) {
				
					// Make element's backround and background color transparent
					element.style.setProperty("background", "transparent", "important");
					element.style.setProperty("background-color", "transparent", "important");
				}
			}
			
		})), wp.element.createElement("div", {
		
			// Classname
			className: wp.blockEditor.useBlockProps().className,
			
			// Style
			style: wp.blockEditor.useBlockProps().style
			
		}, [
	
			// Image
			wp.element.createElement("img", {
		
				// Source
				src: MimbleWimbleCoinDonationButton_blocks_script_parameters.blocks_path + "mimblewimble_coin_donation_button/mimblewimble_coin_logo.svg",
				
				// Alternative
				alt: wp.i18n.__("MimbleWimble Coin donate now", "mimblewimble-coin-donation-button")
			}),
			
			// Text
			wp.element.createElement("p", null, [
			
				// First line
				wp.element.createElement("span", null, wp.i18n.__("MimbleWimble Coin", "mimblewimble-coin-donation-button")),
				
				// Second line
				wp.element.createElement("span", null, wp.i18n.__("Donate now", "mimblewimble-coin-donation-button"))
			])
		]))
	});
})();
