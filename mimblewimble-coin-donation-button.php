<?php
/**
 * Plugin Name: MimbleWimble Coin Donation Button
 * Plugin URI: https://github.com/NicolasFlamel1/WordPress-MimbleWimble-Coin-Donation-Button
 * Description: Plugin for WordPress that adds a MimbleWimble Coin donation button to WordPress's block editor blocks that's capable of accepting MimbleWimble Coin donations without having to run any wallet software.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Nicolas Flamel
 * License: MIT
 * License URI: https://github.com/NicolasFlamel1/WordPress-MimbleWimble-Coin-Donation-Button/blob/master/LICENSE
 * Text Domain: mimblewimble-coin-donation-button
 * Domain Path: /languages
*/


// Enforce strict types
declare(strict_types=1);

// Namespace
namespace MimbleWimbleCoinDonationButton;


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}


// Check if MimbleWimble Coin donation button class doesn't exist
if(class_exists("MimbleWimbleCoinDonationButton") === FALSE) {

	// MimbleWimble Coin donation button class
	final class MimbleWimbleCoinDonationButton {
	
		// Blocks category ID
		private const BLOCKS_CATEGORY_ID = "mimblewimble-coin-donation-button";
		
		// Recovery passphrase displayed option name
		private const RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME = "mimblewimble_coin_donation_button_recovery_passphrase_displayed";
		
		// Seed option name
		private const SEED_OPTION_NAME = "mimblewimble_coin_donation_button_seed";
		
		// Identifier path option name
		private const IDENTIFIER_PATH_OPTION_NAME = "mimblewimble_coin_donation_button_identifier_path";
		
		// Display recovery passphrase query name
		private const DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME = "mimblewimble_coin_donation_button_display_recovery_passphrase";
		
		// API route namespace
		private const API_ROUTE_NAMESPACE = "donate-mimblewimble-coin/v2";
		
		// Constructor
		public function __construct() {
		
			// Include dependencies
			require_once ABSPATH . "wp-admin/includes/plugin.php";
			
			// Check if compatible
			register_activation_hook(__FILE__, __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::checkIfCompatible");
			
			// Delete seed
			register_uninstall_hook(__FILE__, __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::deleteSeed");
			
			// Add translations and blocks
			add_action("init", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::addTranslationsAndBlocks");
			
			// Display recovery passphrase
			add_action("admin_notices", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::displayRecoveryPassphrase");
			
			// Add display recovery passphrase link
			add_filter("plugin_action_links_" . plugin_basename(__FILE__), __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::addDisplayRecoveryPassphraseLink");
			
			// Add scripts
			add_action("wp_enqueue_scripts", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::addScripts");
			
			// Register API
			add_action("rest_api_init", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::registerApi");
		}
		
		// Check if compatible
		public static function checkIfCompatible(): void {
		
			// Try
			try {
			
				// Get wallet
				self::getWallet();
			}
			
			// Catch errors
			catch(\Throwable $error) {
			
				// Throw error
				throw new \Exception("MimbleWimble Coin Donation Button isn't compatible");
			}
		}
		
		// Delete seed
		public static function deleteSeed(): void {
		
			// Delete recovery passphrase displayed
			delete_option(self::RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME);
			
			// Delete seed
			delete_option(self::SEED_OPTION_NAME);
			
			// Delete identifier path
			delete_option(self::IDENTIFIER_PATH_OPTION_NAME);
		}
		
		// Add translations and blocks
		public static function addTranslationsAndBlocks(): void {
		
			// Get plugin data
			$pluginData = get_plugin_data(__FILE__);
			
			// Add translations
			load_plugin_textdomain($pluginData["TextDomain"], FALSE, dirname(__FILE__) . $pluginData["DomainPath"]);
			
			// Add blocks script
			wp_enqueue_script("MimbleWimbleCoinDonationButton_blocks_script", plugins_url("assets/js/blocks.min.js", __FILE__), ["wp-blocks"], $pluginData["Version"], TRUE);
			
			// Load translations for blocks scripts
			wp_set_script_translations("MimbleWimbleCoinDonationButton_blocks_script", $pluginData["TextDomain"], dirname(__FILE__) . $pluginData["DomainPath"]);
			
			// Pass parameters to blocks script
			wp_add_inline_script("MimbleWimbleCoinDonationButton_blocks_script", "const MimbleWimbleCoinDonationButton_blocks_script_parameters = " . json_encode([
			
				// Category ID
				"category_id" => self::BLOCKS_CATEGORY_ID,
				
				// Category title
				"category_title" => __("MimbleWimble Coin Donation Button", "mimblewimble-coin-donation-button"),
				
				// Blocks path
				"blocks_path" => plugins_url("src/blocks/", __FILE__)
				
			]), "before");
			
			// Add blocks
			register_block_type(plugin_dir_path(__FILE__) . "src/blocks/mimblewimble_coin_donation_button");
		}
		
		// Display recovery passphrase
		public static function displayRecoveryPassphrase(): void {
		
			// Check if user requested to display recovery passphrase or recovery passphrase hasn't been displayed
			if((isset($_GET) === TRUE && array_key_exists(self::DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME, $_GET) === TRUE) || get_option(self::RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME) === FALSE) {
				
				// Check if setting that recovery passphrase was displayed was successful or user requested to display recovery passphrase
				if(update_option(self::RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME, TRUE, FALSE) === TRUE || (isset($_GET) === TRUE && array_key_exists(self::DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME, $_GET) === TRUE)) {
				
					// Display start of info
					echo "<div class=\"notice notice-info is-dismissible\"><p>" . esc_html__("Your MimbleWimble Coin donation wallet's recovery passphrase is: ", "mimblewimble-coin-donation-button") . "<strong>";
					
					// Display wallets passphrase
					self::getWallet()->displayPassphrase();
					
					// Display end of info
					echo "</strong></p></div>";
				}
			}
		}
		
		// Add display recovery passphrase link
		public static function addDisplayRecoveryPassphraseLink(array $actions): array {
		
			// Create display recovery passphrase link
			$displayRecoveryPassphraseLink = ["display_recovery_passphrase" => "<a href=\"" . esc_url(admin_url("plugins.php?" . self::DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME)) . "\" aria-label=\"" . esc_attr__("Display your MimbleWimble Coin donation wallet's recovery passphrase", "mimblewimble-coin-donation-button") . "\">" . esc_html__("Display recovery passphrase", "mimblewimble-coin-donation-button") . "</a>"];
			
			// Add display recovery passphrase link to actions and return it
			return array_merge($displayRecoveryPassphraseLink, $actions);
		}
		
		// Add scripts
		public static function addScripts(): void {
		
			// Get plugin data
			$pluginData = get_plugin_data(__FILE__);
			
			// Add scripts
			wp_enqueue_script("MimbleWimbleCoinDonationButton_links_script", plugins_url("assets/js/links.min.js", __FILE__), ["jquery"], $pluginData["Version"], TRUE);
		}
		
		// Register API
		public static function registerApi(): void {
		
			// Register foreign API route
			register_rest_route(self::API_ROUTE_NAMESPACE, "/foreign", [
			
				// Methods
				"methods" => \WP_REST_Server::CREATABLE,
				
				// Callback
				"callback" => __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::processApiRequests"
			]);
		}
		
		// Process API requests
		public static function processApiRequests(\WP_REST_Request $request): \WP_REST_Response {
		
			// Set no cache headers
			nocache_headers();
			
			// Check if request isn't a JSON-RPC request
			$json = $request->get_json_params();
			if(is_array($json) === FALSE || array_key_exists("jsonrpc", $json) === FALSE || $json["jsonrpc"] !== "2.0" || array_key_exists("id", $json) === FALSE || is_int($json["id"]) === FALSE || array_key_exists("method", $json) === FALSE || is_string($json["method"]) === FALSE) {
			
				// Return bad request response
				return new \WP_REST_Response(NULL, 400);
			}
			
			// Otherwise
			else {
			
				// Check request's method
				switch($json["method"]) {
				
					// Check version
					case "check_version":
					
						// Set response to version info
						$response = [
						
							// JSON-RPC
							"jsonrpc" => "2.0",
							
							// ID
							"id" => $json["id"],
							
							// Result
							"result" => [
							
								// Ok
								"Ok" => [
								
									// Foreign API version
									"foreign_api_version" => 2,
									
									// Supported slate versions
									"supported_slate_versions" => [
									
										// Version two
										"V2"
									]
								]
							]
						];
						
						// Break
						break;
					
					// Receive transaction
					case "receive_tx":
					
						// Include dependencies
						require_once plugin_dir_path(__FILE__) . "includes/common.php";
					
						// Check if request's parameters are invalid
						if(array_key_exists("params", $json) === FALSE || is_array($json["params"]) === FALSE || Common::isAssociativeArray($json["params"]) === TRUE || count($json["params"]) < 1 || is_array($json["params"][0]) === FALSE) {
						
							// Set response to invalid parameters
							$response = [
							
								// JSON-RPC
								"jsonrpc" => "2.0",
								
								// ID
								"id" => $json["id"],
								
								// Error
								"error" => [
								
									// Code
									"code" => -32602,
									
									// Message
									"message" => "Invalid parameters"
								]
							];
						}
						
						// Otherwise
						else {
						
							// Include dependencies
							require_once plugin_dir_path(__FILE__) . "includes/uint64.php";
							
							// Try
							try {
							
								// Get identifier path
								$savedIdentifierPath = hex2bin(get_option(self::IDENTIFIER_PATH_OPTION_NAME, ""));
								
								$identifierPath = new Uint64(($savedIdentifierPath === FALSE) ? "" : $savedIdentifierPath);
							}
							
							// Catch errors
							catch(\Throwable $error) {
							
								// Set identifier path to initial value
								$identifierPath = new Uint64();
							}
							
							// Get next identifier path
							$nextIdentifierPath = $identifierPath->clone();
							$nextIdentifierPath->increment();
							
							// Check if saving next identifier path failed
							if(update_option(self::IDENTIFIER_PATH_OPTION_NAME, bin2hex($nextIdentifierPath->serialize()), FALSE) === FALSE) {
							
								// Set response to internal error
								$response = [
								
									// JSON-RPC
									"jsonrpc" => "2.0",
									
									// ID
									"id" => $json["id"],
									
									// Error
									"error" => [
									
										// Code
										"code" => -32603,
										
										// Message
										"message" => "Internal error"
									]
								];
							}
							
							// Otherwise
							else {
							
								// Check if adding wallet's output to request's slate failed
								$slateResponse = self::getWallet()->addOutputToSlate($identifierPath, $json["params"][0]);
								if($slateResponse === FALSE) {
								
									// Set response to internal error
									$response = [
									
										// JSON-RPC
										"jsonrpc" => "2.0",
										
										// ID
										"id" => $json["id"],
										
										// Error
										"error" => [
										
											// Code
											"code" => -32603,
											
											// Message
											"message" => "Internal error"
										]
									];
								}
								
								// Otherwise
								else {
								
									// Set response to slate response
									$response = [
									
										// JSON-RPC
										"jsonrpc" => "2.0",
										
										// ID
										"id" => $json["id"],
										
										// Result
										"result" => [
										
											// Ok
											"Ok" => $slateResponse
										]
									];
									
									// Check if admin's email is valid
									if(is_email(get_site_option("admin_email")) !== FALSE) {
									
										// Use WordPress locale
										global $wp_locale;
										
										// Send email to admin
										wp_mail(get_site_option("admin_email"), __("MimbleWimble Coin Donation Received", "mimblewimble-coin-donation-button"), sprintf(__("You received a donation of %s MWC. You shouldn't consider this donation to be legitimate until it's been confirmed on the blockchain.", "mimblewimble-coin-donation-button"), (isset($wp_locale) === TRUE) ? preg_replace('/\./u', $wp_locale->number_format["decimal_point"], $slateResponse["amount"], 1) : $slateResponse["amount"]));
									}
								}
							}
						}
						
						// Break
						break;
					
					// Default
					default:
					
						// Set response to method not found
						$response = [
						
							// JSON-RPC
							"jsonrpc" => "2.0",
							
							// ID
							"id" => $json["id"],
							
							// Error
							"error" => [
							
								// Code
								"code" => -32601,
								
								// Message
								"message" => "Method not found"
							]
						];
						
						// Break
						break;
				}
				
				// Return ok response
				return new \WP_REST_Response($response);
			}
		}
		
		// Get wallet
		private static function getWallet(): Wallet {
		
			// Include dependencies
			require_once plugin_dir_path(__FILE__) . "includes/wallet.php";
			
			// Check if getting seed failed
			$seed = hex2bin(get_option(self::SEED_OPTION_NAME, ""));
			if($seed === FALSE) {
			
				// Set seed to no seed
				$seed = "";
			}
			
			// Set save seed to if seed doesn't exist
			$saveSeed = $seed === "";
			
			// Create wallet with seed
			$wallet = new Wallet($seed);
			
			// Check if saving seed
			if($saveSeed === TRUE) {
			
				// Check if saving seed failed
				if(update_option(self::SEED_OPTION_NAME, bin2hex($seed), FALSE) === FALSE) {
				
					// Throw error
					throw new \Exception("Saving seed failed");
				}
			}
			
			// Return wallet
			return $wallet;
		}
	}

	// Create MimbleWimble Coin donation button
	$mimbleWimbleCoinDonationButton = new MimbleWimbleCoinDonationButton();
}


?>
