<?php
/**
 * Plugin Name: MimbleWimble Coin Donation Button
 * Plugin URI: https://github.com/NicolasFlamel1/WordPress-MimbleWimble-Coin-Donation-Button
 * Description: Plugin for WordPress that adds a MimbleWimble Coin donation button to WordPress's block editor blocks that's capable of accepting MimbleWimble Coin donations without having to run any wallet software.
 * Version: 0.1.2
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
		
		// Wallet created option name
		private const WALLET_CREATED_OPTION_NAME = "mimblewimble_coin_donation_button_wallet_created";
		
		// Recovery passphrase displayed option name
		private const RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME = "mimblewimble_coin_donation_button_recovery_passphrase_displayed";
		
		// Seed option name
		private const SEED_OPTION_NAME = "mimblewimble_coin_donation_button_seed";
		
		// Identifier path option name
		private const IDENTIFIER_PATH_OPTION_NAME = "mimblewimble_coin_donation_button_identifier_path";
		
		// Display recovery passphrase query name
		private const DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME = "mimblewimble_coin_donation_button_display_recovery_passphrase";
		
		// Display address query name
		private const DISPLAY_ADDRESS_QUERY_NAME = "mimblewimble_coin_donation_button_display_address";
		
		// API route namespace
		private const API_ROUTE_NAMESPACE = "donate-mimblewimble-coin";
		
		// API route version
		private const API_ROUTE_VERSION = 2;
		
		// MimbleWimble Coin number of decimal digits
		private const MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS = 9;
		
		// MimbleWimble Coin block explorer URL
		private const MIMBLEWIMBLE_COIN_BLOCK_EXPLORER_URL = "https://explorer.mwc.mw/#k";
		
		// Constructor
		public function __construct() {
		
			// Include dependencies
			require_once ABSPATH . "wp-admin/includes/plugin.php";
			
			// Check if compatible
			register_activation_hook(__FILE__, __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::checkIfCompatible");
			
			// Delete seed
			register_uninstall_hook(__FILE__, __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::deleteSeed");
			
			// Check if FFI is enabled
			if(ini_get("ffi.enable") === "1") {
			
				// Create wallet
				add_action("plugins_loaded", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::createWallet");
				
				// Add translations and blocks
				add_action("init", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::addTranslationsAndBlocks");
				
				// Display recovery passphrase and address
				add_action("admin_notices", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::displayRecoveryPassphraseAndAddress");
				
				// Add display recovery passphrase and address links
				add_filter("plugin_action_links_" . plugin_basename(__FILE__), __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::addDisplayRecoveryPassphraseAndAddressLinks");
				
				// Add scripts
				add_action("wp_enqueue_scripts", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::addScripts");
				
				// Register API
				add_action("rest_api_init", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::registerApi");
			}
			
			// Otherwise
			else {
			
				// Display FFI requirement
				add_action("admin_notices", __NAMESPACE__ . "\MimbleWimbleCoinDonationButton::displayFfiRequirement");
			}
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
			
				// Check if FFI is enabled
				if(ini_get("ffi.enable") === "1") {
				
					// Throw error
					throw new \Exception("MimbleWimble Coin Donation Button isn't compatible");
				}
				
				// Otherwise check if setting that wallet wasn't created failed
				else if(get_option(self::WALLET_CREATED_OPTION_NAME) !== "false" && update_option(self::WALLET_CREATED_OPTION_NAME, "false", TRUE) === FALSE) {
				
					// Throw error
					throw new \Exception("Saving that wallet wasn't created failed");
				}
				
				// Return
				return;
			}
			
			// Check if setting that wallet was created failed
			if(get_option(self::WALLET_CREATED_OPTION_NAME) !== "true" && update_option(self::WALLET_CREATED_OPTION_NAME, "true", TRUE) === FALSE) {
			
				// Throw error
				throw new \Exception("Saving that wallet was created failed");
			}
		}
		
		// Delete seed
		public static function deleteSeed(): void {
		
			// Delete wallet created
			delete_option(self::WALLET_CREATED_OPTION_NAME);
			
			// Delete recovery passphrase displayed
			delete_option(self::RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME);
			
			// Delete seed
			delete_option(self::SEED_OPTION_NAME);
			
			// Delete identifier path
			delete_option(self::IDENTIFIER_PATH_OPTION_NAME);
		}
		
		// Create wallet
		public static function createWallet(): void {
		
			// Check if wallet hasn't been created
			if(get_option(self::WALLET_CREATED_OPTION_NAME) !== "true") {
			
				// Check if compatible
				self::checkIfCompatible();
			}
		}
		
		// Add translations and blocks
		public static function addTranslationsAndBlocks(): void {
		
			// Get plugin data
			$pluginData = get_plugin_data(__FILE__);
			
			// Add translations
			load_plugin_textdomain($pluginData["TextDomain"], FALSE, dirname(__FILE__) . $pluginData["DomainPath"]);
			
			// Add blocks scripts
			wp_enqueue_script("MimbleWimbleCoinDonationButton_qrcode-generator_script", plugins_url("assets/js/qrcode-generator-1.4.4.min.js", __FILE__), [], $pluginData["Version"], TRUE);
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
				"blocks_path" => plugins_url("src/blocks/", __FILE__),
				
				// Donation address
				"donation_address" => get_rest_url(NULL, self::API_ROUTE_NAMESPACE)
				
			]), "before");
			
			// Add blocks
			register_block_type(plugin_dir_path(__FILE__) . "src/blocks/mimblewimble_coin_donation_button");
		}
		
		// Display recovery passphrase and address
		public static function displayRecoveryPassphraseAndAddress(): void {
		
			// Check if user requested to display recovery passphrase or recovery passphrase hasn't been displayed
			if((isset($_GET) === TRUE && array_key_exists(self::DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME, $_GET) === TRUE) || get_option(self::RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME) !== "true") {
				
				// Check if setting that recovery passphrase was displayed was successful or user requested to display recovery passphrase
				if(update_option(self::RECOVERY_PASSPHRASE_DISPLAYED_OPTION_NAME, "true", FALSE) === TRUE || (isset($_GET) === TRUE && array_key_exists(self::DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME, $_GET) === TRUE)) {
				
					// Display start of info
					echo "<div class=\"notice notice-info is-dismissible\"><p>" . esc_html__("Your MimbleWimble Coin donation wallet's recovery passphrase is: ", "mimblewimble-coin-donation-button") . "<strong>";
					
					// Display wallets passphrase
					self::getWallet()->displayPassphrase();
					
					// Display end of info
					echo "</strong></p></div>";
				}
			}
			
			// Check if user requested to display address
			if(isset($_GET) === TRUE && array_key_exists(self::DISPLAY_ADDRESS_QUERY_NAME, $_GET) === TRUE) {
			
				// Display info
				echo "<div class=\"notice notice-info is-dismissible\"><p>" . sprintf(esc_html__("Your MimbleWimble Coin donation wallet's address is: %s", "mimblewimble-coin-donation-button"), "<strong><a href=\"" . esc_url(get_rest_url(NULL, self::API_ROUTE_NAMESPACE)) . "\" aria-label=\"" . esc_attr__("Open your MimbleWimble Coin donation wallet's address", "mimblewimble-coin-donation-button") . "\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">" . esc_html(get_rest_url(NULL, self::API_ROUTE_NAMESPACE)) . "</a></strong>") . "</p></div>";
			}
		}
		
		// Add display recovery passphrase and address links
		public static function addDisplayRecoveryPassphraseAndAddressLinks(array $actions): array {
		
			// Create display recovery passphrase and address links
			$displayRecoveryPassphraseLinks = [
			
				// Display recovery passphrase
				"display_recovery_passphrase" => "<a href=\"" . esc_url(admin_url("plugins.php?" . self::DISPLAY_RECOVERY_PASSPHRASE_QUERY_NAME)) . "\" aria-label=\"" . esc_attr__("Display your MimbleWimble Coin donation wallet's recovery passphrase", "mimblewimble-coin-donation-button") . "\">" . esc_html__("Display recovery passphrase", "mimblewimble-coin-donation-button") . "</a>",
				
				// Display address
				"display_address" => "<a href=\"" . esc_url(admin_url("plugins.php?" . self::DISPLAY_ADDRESS_QUERY_NAME)) . "\" aria-label=\"" . esc_attr__("Display your MimbleWimble Coin donation wallet's address", "mimblewimble-coin-donation-button") . "\">" . esc_html__("Display address", "mimblewimble-coin-donation-button") . "</a>"
			];
			
			// Add display recovery passphrase and address links to actions and return it
			return array_merge($displayRecoveryPassphraseLinks, $actions);
		}
		
		// Add scripts
		public static function addScripts(): void {
		
			// Get plugin data
			$pluginData = get_plugin_data(__FILE__);
			
			// Add scripts
			wp_enqueue_script("MimbleWimbleCoinDonationButton_links_script", plugins_url("assets/js/links.min.js", __FILE__), ["jquery", "MimbleWimbleCoinDonationButton_blocks_script", "MimbleWimbleCoinDonationButton_qrcode-generator_script"], $pluginData["Version"], TRUE);
		}
		
		// Register API
		public static function registerApi(): void {
		
			// Register foreign API route
			register_rest_route(self::API_ROUTE_NAMESPACE . "/v" . self::API_ROUTE_VERSION, "/foreign", [
			
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
								$result = self::getWallet()->addOutputToSlate($identifierPath, $json["params"][0]);
								if($result === FALSE) {
								
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
								
									// Get slate and excess from the result
									list($slate, $excess) = $result;
									
									// Set response to slate response
									$response = [
									
										// JSON-RPC
										"jsonrpc" => "2.0",
										
										// ID
										"id" => $json["id"],
										
										// Result
										"result" => [
										
											// Ok
											"Ok" => $slate
										]
									];
									
									// Check if admin's email is valid
									if(is_email(get_site_option("admin_email")) !== FALSE) {
									
										// Use WordPress locale
										global $wp_locale;
										
										// Send email to admin
										wp_mail(get_site_option("admin_email"), __("MimbleWimble Coin Donation Received", "mimblewimble-coin-donation-button"), sprintf(esc_html__("You received a donation of %s MWC.", "mimblewimble-coin-donation-button"), esc_html(rtrim(rtrim(preg_replace('/(?=\d{' . self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . '}$)/u', (isset($wp_locale) === TRUE) ? $wp_locale->number_format["decimal_point"] : ".", str_pad($slate["amount"], self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS + 1, "0", STR_PAD_LEFT), 1), "0"), (isset($wp_locale) === TRUE) ? $wp_locale->number_format["decimal_point"] : "."))) . "<br><br>" . sprintf(esc_html__('You shouldn\'t consider this donation to be legitimate until it\'s been confirmed on the blockchain. %1$sView donation in a block explorer%2$s.', "mimblewimble-coin-donation-button"), "<a href=\"" . esc_url(self::MIMBLEWIMBLE_COIN_BLOCK_EXPLORER_URL . bin2hex($excess)) . "\">", "</a>"), [
										
											// Content type
											"Content-Type: text/html; charset=" . get_bloginfo("charset")
										]);
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
		
		// Display FFI requirement
		public static function displayFfiRequirement(): void {
		
			// Display warning
			echo "<div class=\"notice notice-warning is-dismissible\"><p>" . sprintf(esc_html__('MimbleWimble Coin Donation Button won\'t work unless you enable PHP\'s FFI API. Please %1$senable PHP\'s FFI API%2$s to resolve this issue.', "mimblewimble-coin-donation-button"), "<a href=\"" . esc_url("https://www.php.net/manual/ffi.configuration.php#ini.ffi.enable") . "\" aria-label=\"" . esc_attr__("Go to PHP's FFI settings documentation", "mimblewimble-coin-donation-button") . "\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">", "</a>") . "</p></div>";
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
