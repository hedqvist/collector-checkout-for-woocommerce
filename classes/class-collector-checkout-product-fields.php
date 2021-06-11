<?php
/**
 * Collector Product Fields
 *
 * @class    Collector_Checkout_Status
 * @version  0.8.0
 * @package  Collector_Checkout/Classes
 * @category Class
 * @author   Krokedil
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Collector_Checkout_Product_Fields {
	public function __construct() {
		$collector_settings       = get_option( 'woocommerce_collector_checkout_settings' );
		$this->add_product_fields = ! empty( $collector_settings['requires_electronic_id_fields'] ) ? $collector_settings['requires_electronic_id_fields'] : 'no';

		if ( 'yes' === $this->add_product_fields ) {
			// Simple product fields.
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'create_product_fields' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_fields' ) );
		}
	}

	/**
	 * Add simple product meta fields to product general tab.
	 *
	 * @return void
	 */
	public function create_product_fields() {

		global $post;
		$post_id = $post->ID;
		$product = wc_get_product( $post_id );
		$args    = array(
			'id'          => '_collector_requires_electronic_id',
			'label'       => __( 'Collector Requires Electronic ID', 'kroconnect-extra-fields' ),
			'class'       => 'collector-requires-electronic-id',
			'desc_tip'    => false,
			'description' => __( 'If this product requires Electronic ID signing in Collector Checkout.', 'kroconnect-extra-fields' ),
		);
		woocommerce_wp_checkbox( $args );

	}

	public function save_product_fields( $post_id ) {
		$product                          = wc_get_product( $post_id );
		$collector_requires_electronic_id = isset( $_POST['_collector_requires_electronic_id'] ) ? 'yes' : 'no';
		$product->update_meta_data( '_collector_requires_electronic_id', sanitize_text_field( $collector_requires_electronic_id ) );
		$product->save();
	}


}
new Collector_Checkout_Product_Fields();
