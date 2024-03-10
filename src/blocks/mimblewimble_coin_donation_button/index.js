// Main function
(() => {

	// Use strict
	"use strict";
	
	// Try
	let qrCode;
	try {
	
		// Initialize QR code
		qrcode.stringToBytes = qrcode.stringToBytesFuncs["UTF-8"];
		qrCode = qrcode(0, "L");
		
		// Add donation address to QR code
		qrCode.addData(MimbleWimbleCoinDonationButton_blocks_script_parameters.donation_address, "Byte");
		
		// Create QR code
		qrCode.make();
	}
	
	// Catch errors
	catch(error) {
	
		// Set that QR code doesn't exist
		qrCode = null;
	}
	
	// Register block type
	wp.blocks.registerBlockType("mimblewimble-coin-donation-button/mimblewimble-coin-donation-button", {
	
		// Edit
		edit: (props) => [
		
			// Inspector controls
			wp.element.createElement(wp.blockEditor.InspectorControls, null, [
			
				// Panel body
				wp.element.createElement(wp.components.PanelBody, {
				
					// Title
					title: wp.i18n.__("Settings", "mimblewimble-coin-donation-button")
				
				// Toggle control
				}, wp.element.createElement(wp.components.ToggleControl, {
				
					// Checked
					checked: props.attributes.invertQrCodeColor === true,
					
					// Label
					label: wp.i18n.__("Invert QR code color", "mimblewimble-coin-donation-button"),
					
					// On change
					onChange: () => props.setAttributes({
					
						// Invert QR code color
						invertQrCodeColor: !props.attributes.invertQrCodeColor
					})
				}))
			]),
			
			// Element
			wp.element.createElement("div", wp.blockEditor.useInnerBlocksProps(wp.blockEditor.useBlockProps({
				
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
			
				// Class name
				className: wp.blockEditor.useBlockProps().className,
				
				// Style
				style: wp.blockEditor.useBlockProps().style
				
			}, [
		
				// Image
				wp.element.createElement("img", {
			
					// Source
					src: MimbleWimbleCoinDonationButton_blocks_script_parameters.blocks_path + "mimblewimble_coin_donation_button/mimblewimble_coin_logo.svg",
					
					// Alternative
					alt: wp.i18n.__("MimbleWimble Coin logo", "mimblewimble-coin-donation-button")
				}),
				
				// Text
				wp.element.createElement("p", null, [
				
					// First line
					wp.element.createElement("span", null, wp.i18n.__("MimbleWimble Coin", "mimblewimble-coin-donation-button")),
					
					// Second line
					wp.element.createElement("span", null, wp.i18n.__("Donate now", "mimblewimble-coin-donation-button"))
				]),
				
				// QR code
				wp.element.createElement("img", {
			
					// Class name
					className: (qrCode === null) ? "mimblewimble-coin-donation-button-mimblewimble-coin-donation-button_hide" : ((props.attributes.invertQrCodeColor === true) ? "mimblewimble-coin-donation-button-mimblewimble-coin-donation-button_invert" : ""),
					
					// Source
					src: (qrCode !== null) ? qrCode.createDataURL(0, 0) : "",
					
					// Alternative
					alt: wp.i18n.__("MimbleWimble Coin donation address QR code", "mimblewimble-coin-donation-button")
				})
			]))
		]
	});
})();
