<?php
/**
 * WooCommerce status page extension
 *
 * @class    Collector_Checkout_Status
 * @version  0.8.0
 * @package  Collector_Checkout/Classes
 * @category Class
 * @author   Krokedil
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Collector_Checkout_Status {
	public function __construct() {
		add_action( 'woocommerce_system_status_report', array( $this, 'add_status_page_box' ) );
	}
	public function add_status_page_box() {
		include_once( COLLECTOR_BANK_PLUGIN_DIR . '/includes/collector-status-report.php' );
	}
}
$wc_collector_checkout_status = new Collector_Checkout_Status();