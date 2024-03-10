// Main function
jQuery(($) => {

	// Use strict
	"use strict";
	
	// MimbleWimble Coin donation button link click event
	$(document).on("click", "div.wp-block-mimblewimble-coin-donation-button-mimblewimble-coin-donation-button > a", (event) => {
	
		// Get URL
		const url = $(event.currentTarget);
		
		// Get URL's link
		const link = url.attr("href");
	
		// Check if MWC Wallet extension is installed and the event isn't recursive
		if(typeof MwcWallet !== "undefined" && event.originalEvent.isTrusted !== false) {
		
			// Prevent default
			event.preventDefault();
			
			// Start transaction with the MWC Wallet extension and catch errors
			MwcWallet.startTransaction(MwcWallet.MWC_WALLET_TYPE, MwcWallet.MAINNET_NETWORK_TYPE, link).catch((error) => {
			
				// Trigger modal URL click event
				event.originalEvent.target.click();
			});
		}
		
		// Otherwise
		else {
		
			// Add protocol to URL's link
			url.attr("href", "web+mwc" + link);
			
			// Set timeout
			setTimeout(() => {
			
				// Remove protocol from URL's link
				url.attr("href", link);
			}, 0);
		}
	});
	
	// Try
	try {
	
		// Initialize QR code
		qrcode.stringToBytes = qrcode.stringToBytesFuncs["UTF-8"];
		const qrCode = qrcode(0, "L");
		
		// Add donation address to QR code
		qrCode.addData(MimbleWimbleCoinDonationButton_blocks_script_parameters.donation_address, "Byte");
		
		// Create QR code
		qrCode.make();
		
		// Get QR code's data URL
		const dataUrl = qrCode.createDataURL(0, 0);
		
		// Go through all MimbleWimble Coin donation button QR code images
		$("div.wp-block-mimblewimble-coin-donation-button-mimblewimble-coin-donation-button img:last-of-type").each(function() {
		
			// Set QR code image's source to the data URL and show it
			$(this).attr("src", dataUrl).removeClass("mimblewimble-coin-donation-button-mimblewimble-coin-donation-button_hide");
		});
	}
	
	// Catch errors
	catch(error) {
	
	}
});
