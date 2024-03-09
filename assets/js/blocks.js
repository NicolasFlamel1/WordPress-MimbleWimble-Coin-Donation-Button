// Main function
(() => {

	// Use strict
	"use strict";

	// Set blocks category
	wp.blocks.setCategories([

		// Existing blocks categories
		...wp.blocks.getCategories(),
		
		// New category
		{
		
			// Slug
			slug: MimbleWimbleCoinDonationButton_blocks_script_parameters.category_id,
			
			// Title
			title: MimbleWimbleCoinDonationButton_blocks_script_parameters.category_title
		}
	]);
})();
