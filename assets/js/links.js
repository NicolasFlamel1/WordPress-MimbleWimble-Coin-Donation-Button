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
});
