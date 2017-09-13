<?php
/**
 * Collector Bank for WooCommerce
 *
 * @package WC_Dibs_Easy
 *
 * @wordpress-plugin
 * Plugin Name:     Collector Checkout for WooCommerce
 * Plugin URI:      https://krokedil.se/
 * Description:     Extends WooCommerce. Provides a <a href="https://www.collector.se/" target="_blank">Collector Checkout</a> checkout for WooCommerce.
 * Version:         0.2.9
 * Author:          Krokedil
 * Author URI:      https://woocommerce.com/
 * Developer:       Krokedil
 * Developer URI:   https://krokedil.se/
 * Text Domain:     collector-checkout-for-woocommerce
 * Domain Path:     /languages
 * Copyright:       © 2009-2017 WooCommerce.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'COLLECTOR_BANK_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'COLLECTOR_BANK_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'COLLECTOR_BANK_VERSION', '0.2.9' );

if ( ! class_exists( 'Collector_Checkout' ) ) {
	class Collector_Checkout {
		public function __construct() {
			
			// Initiate the gateway
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			// Load scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			
			// CSS for settings page
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ) );

			// Remove the storefront sticky checkout.
			add_action( 'wp_enqueue_scripts', array( $this, 'jk_remove_sticky_checkout' ), 99 );
		}

		public function init() {
			// Include the Classes
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/class-collector-checkout-ajax-calls.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/class-collector-checkout-post-checkout.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/class-collector-checkout-instant-checkout.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/class-collector-checkout-admin-notices.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/class-collector-checkout-order-emails.php' );
			
			// Include and add the Gateway
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/class-collector-checkout-gateway.php' );
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_collector_checkout_gateway' ) );
			}

			// Include the Request Classes
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests-initialize-checkout.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests-update-fees.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests-update-cart.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests-get-checkout-information.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests-update-reference.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/class-collector-checkout-requests-instant-checkout.php' );

			// Include the Request Helpers
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/helpers/class-collector-checkout-requests-cart.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/helpers/class-collector-checkout-requests-fees.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/helpers/class-collector-checkout-requests-header.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/helpers/class-collector-checkout-requests-calculate-auth.php' );

			// Include the Soap Request Classes
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/soap/class-collector-checkout-soap-requests-activate-invoice.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/soap/class-collector-checkout-soap-requests-cancel-invoice.php' );
			include_once( COLLECTOR_BANK_PLUGIN_DIR . '/classes/requests/soap/class-collector-checkout-soap-requests-credit-payment.php' );

			// Definitions
			define( 'COLLECTOR_BANK_REST_LIVE', 'https://checkout-api.collector.se' );
			define( 'COLLECTOR_BANK_REST_TEST', 'https://checkout-api-uat.collector.se' );
			define( 'COLLECTOR_BANK_SOAP_LIVE', 'https://ecommerce.collector.se/v3.0/InvoiceServiceV33.svc?wsdl' );
			define( 'COLLECTOR_BANK_SOAP_TEST', 'https://ecommercetest.collector.se/v3.0/InvoiceServiceV33.svc?wsdl' );
			
			// Translations
			load_plugin_textdomain( 'collector-checkout-for-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		public function load_scripts() {
			// Enqueue scripts
			wp_enqueue_script( 'jquery' );
			if ( is_checkout() ) {
				wp_register_script( 'checkout', plugins_url( '/assets/js/checkout.js', __FILE__ ), array( 'jquery' ), COLLECTOR_BANK_VERSION );
				
				if( 'NOK' == get_woocommerce_currency() ) {
					$locale = 'nb-NO';
				} else {
					$locale = 'sv';
				}
				wp_localize_script( 'checkout', 'wc_collector_checkout', array(
					'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
					'locale' 						=> $locale,
					'default_customer_type' 		=> wc_collector_get_default_customer_type(),
					'collector_nonce' 				=> wp_create_nonce( 'collector_nonce' ),
					'refresh_checkout_fragment_url'	=> WC_AJAX::get_endpoint( 'update_fragment' ),
					'get_public_token_url'   		=> WC_AJAX::get_endpoint( 'get_public_token' ),
					'add_customer_order_note_url'   => WC_AJAX::get_endpoint( 'add_customer_order_note' ),
					'get_checkout_thank_you_url'   	=> WC_AJAX::get_endpoint( 'get_checkout_thank_you' ),
					'get_customer_data_url'   		=> WC_AJAX::get_endpoint( 'get_customer_data' ),
					'customer_adress_updated_url'   => WC_AJAX::get_endpoint( 'customer_adress_updated' ),
					'update_checkout_url'   		=> WC_AJAX::get_endpoint( 'update_checkout' ),
					
					
				) );
				wp_enqueue_script( 'checkout' );
			}
			
			// Load js + style for Instant Buy
			$collector_settings = get_option( 'woocommerce_collector_checkout_settings' );
			$instant_checkout = $collector_settings['collector_instant_checkout'];
			if ( is_product() && 'no' !== $instant_checkout ) {
				wp_register_script( 'instantcheckout', plugins_url( '/assets/js/instant-checkout.js', __FILE__ ), array( 'jquery' ), COLLECTOR_BANK_VERSION );
				wp_localize_script( 'instantcheckout', 'wc_collector_checkout_instant_checkout', array(
					'ajaxurl' 				=> admin_url( 'admin-ajax.php' ),
					'instant_purchase_url'	=> WC_AJAX::get_endpoint( 'instant_purchase' ),
				) );
				wp_enqueue_script( 'instantcheckout' );
				
				// Load stylesheet for the checkout page
				wp_register_style(
					'instantcheckout',
					plugin_dir_url( __FILE__ ) . 'assets/css/instant-checkout.css',
					array(),
					COLLECTOR_BANK_VERSION
				);
				wp_enqueue_style( 'instantcheckout' );
				
				$button_color = $collector_settings['button_color'];
				$button_color_text = $collector_settings['button_color_text'];
				
				if( $button_color ) {
					$color = get_theme_mod( 'my-custom-color' ); //E.g. #FF0000
			        $custom_css = "
			                button#button-instant-checkout{
			                        background-color: {$button_color};
			                        color: {$button_color_text};
			                        opacity:0.9;
			                        
			                }
			                button#button-instant-checkout:hover{
			                        opacity:1.0;
			                        
			                }";
			                
			        wp_add_inline_style( 'instantcheckout', $custom_css );
				}
			}
			
			// Load stylesheet for the checkout page
			wp_register_style(
				'collector_checkout',
				plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
				array(),
				COLLECTOR_BANK_VERSION
			);
			wp_enqueue_style( 'collector_checkout' );
			
			// Hide the Order overview data on thankyou page if it's a Collector Checkout purchase
			if( is_wc_endpoint_url( 'order-received' ) ) {
				$order_id = wc_get_order_id_by_order_key( wc_clean( $_GET['key'] ) );
				$order = wc_get_order( $order_id );
				if( 'collector_checkout' == $order->get_payment_method() ) {
					$custom_css = "
	                .woocommerce-order-overview {
			                        display: none;
							}";
					wp_add_inline_style( 'collector_checkout', $custom_css );
				}
			}
			
		}
		
		/**
		 * Load Admin CSS
		 **/
		public function enqueue_admin_css( $hook ) {
			if ( 'woocommerce_page_wc-settings' == $hook && 'collector_checkout' == $_GET['section'] ) {
				wp_register_style( 'collector-checkout-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', false );
				wp_enqueue_style( 'collector-checkout-admin' );
			}
		}
	
		public function add_collector_checkout_gateway( $methods ) {
			$methods[] = 'Collector_Checkout_Gateway';

			return $methods;
		}

		public function jk_remove_sticky_checkout() {
			wp_dequeue_script( 'storefront-sticky-payment' );
		}
	}
}
$collector_checkout = new Collector_Checkout();

function wc_collector_get_available_customer_types() {
	$collector_settings = get_option( 'woocommerce_collector_checkout_settings' );
	$collector_b2c_se 	= $collector_settings['collector_merchant_id_se_b2c'];
	$collector_b2b_se 	= $collector_settings['collector_merchant_id_se_b2b'];
	$collector_b2c_no 	= $collector_settings['collector_merchant_id_no_b2c'];

	if( 'SEK' == get_woocommerce_currency() && $collector_b2c_se && $collector_b2b_se ) {
		return 'collector-b2c-b2b';
	} elseif( ( 'SEK' == get_woocommerce_currency() && $collector_b2c_se ) || ( 'NOK' == get_woocommerce_currency() && $collector_b2c_no ) ) {
		return 'collector-b2c';
	} elseif( $collector_b2b_se ) {
		return 'collector-b2b';
	}
}

function wc_collector_get_default_customer_type() {
	$collector_settings = get_option( 'woocommerce_collector_checkout_settings' );
	$collector_b2c_se 	= $collector_settings['collector_merchant_id_se_b2c'];
	$collector_b2b_se 	= $collector_settings['collector_merchant_id_se_b2b'];
	$collector_b2c_no 	= $collector_settings['collector_merchant_id_no_b2c'];

	if( $collector_b2c_no && 'NOK' == get_woocommerce_currency() ) {
		return 'b2c';
	} elseif( $collector_b2c_se && $collector_b2b_se ) {
		return 'b2c';
	} elseif( $collector_b2b_se && !$collector_b2c_se ) {
		return 'b2b';
	} else {
		return 'b2c';
	}
}

/**
 * Get localized and formatted payment method name.
 *
 * @param $payment_method
 *
 * @return string
 */
function wc_collector_get_payment_method_name( $payment_method ) {
	switch ( $payment_method ) {
		
		case 'Direct Invoice' :
			$payment_method = __( 'Collector Invoice', 'collector-checkout-for-woocommerce' );
			break;
		case 'Account' :
			$payment_method = __( 'Collector Account', 'collector-checkout-for-woocommerce' );
			break;
		case 'Part Payment' :
			$payment_method = __( 'Collector Part Payment', 'collector-checkout-for-woocommerce' );
			break;
		case 'Campaign' :
			$payment_method = __( 'Collector Campaign', 'collector-checkout-for-woocommerce' );
			break;
		case 'Card' :
			$payment_method = __( 'Collector Card', 'collector-checkout-for-woocommerce' );
			break;
		case 'Bank Transfer' :
			$payment_method = __( 'Collector Bank Transfer', 'collector-checkout-for-woocommerce' );
			break;
		default :
			break;
	}

	return $payment_method;
}