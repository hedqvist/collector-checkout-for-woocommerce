<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Collector_Checkout_SOAP_Requests_Part_Credit_Invoice {

	static $log = '';

	public $endpoint = '';

	public $username     = '';
	public $password     = '';
	public $store_id     = '';
	public $country_code = '';

	public function __construct( $order_id ) {
		$collector_settings = get_option( 'woocommerce_collector_checkout_settings' );
		$this->username     = $collector_settings['collector_username'];
		$this->password     = $collector_settings['collector_password'];
		$order              = wc_get_order( $order_id );
		$currency           = $order->get_currency();
		$customer_type      = get_post_meta( $order_id, '_collector_customer_type', true );
		switch ( $currency ) {
			case 'SEK':
				$country_code   = 'SE';
				$this->store_id = $collector_settings[ 'collector_merchant_id_se_' . $customer_type ];
				break;
			case 'NOK':
				$country_code   = 'NO';
				$this->store_id = $collector_settings[ 'collector_merchant_id_no_' . $customer_type ];
				break;
			case 'DKK':
				$country_code   = 'DK';
				$this->store_id = $collector_settings[ 'collector_merchant_id_dk_' . $customer_type ];
				break;
			case 'EUR':
				$country_code   = 'FI';
				$this->store_id = $collector_settings[ 'collector_merchant_id_fi_' . $customer_type ];
				break;
			default:
				$country_code   = 'SE';
				$this->store_id = $collector_settings[ 'collector_merchant_id_se_' . $customer_type ];
				break;
		}
		$this->country_code = $country_code;
		$test_mode          = $collector_settings['test_mode'];
		if ( 'yes' === $test_mode ) {
			$this->endpoint = COLLECTOR_BANK_SOAP_TEST;
		} else {
			$this->endpoint = COLLECTOR_BANK_SOAP_LIVE;
		}
	}


	public function request( $order_id, $amount, $reason, $refunded_items ) {
		$order     = wc_get_order( $order_id );
		$soap      = new SoapClient( $this->endpoint );
		$args      = $this->get_request_args( $order_id, $amount, $reason, $refunded_items );
		$headers   = array();
		$headers[] = new SoapHeader( 'http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $this->username );
		$headers[] = new SoapHeader( 'http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $this->password );
		$soap->__setSoapHeaders( $headers );

		try {
			$request = $soap->PartCreditInvoice( $args );
		} catch ( SoapFault $e ) {
			$request = $e->getMessage();
			$this->log( 'Collector PartCreditInvoice request response ERROR: ' . $request              = $e->getMessage() . '. Request object: ' . stripslashes_deep( json_encode( $request ) ) );
			$order->add_order_note( sprintf( __( 'Collector credit invoice request ERROR: ' . $request = $e->getMessage(), 'collector-checkout-for-woocommerce' ) ) );
			return false;
		}
		if ( isset( $request->CorrelationId ) || $request->CorrelationId == null ) {
			$order->add_order_note( sprintf( __( 'Order credited with Collector Bank. CorrelationId ' . $request->CorrelationId, 'collector-checkout-for-woocommerce' ) ) );
			$this->log( 'Collector PartCreditInvoice request response: ' . stripslashes_deep( json_encode( $request ) ) );
			return true;
		} else {

			$order->add_order_note( sprintf( __( 'Order failed to be credited with Collector Bank - ' . var_export( $request, true ), 'collector-checkout-for-woocommerce' ) ) );
			$this->log( 'Collector PartCreditInvoice request response ERROR: ' . $request = $e->getMessage() . '. Request object: ' . stripslashes_deep( json_encode( $request ) ) );
			$this->log( 'Order failed to be credited with Collector Bank. Request response: ' . var_export( $e, true ) );
			$this->log( 'Credit Payment headers: ' . var_export( $headers, true ) );
			return false;
		}
	}

	public function get_request_args( $order_id, $amount, $reason, $refunded_items ) {

		$order          = wc_get_order( $order_id );
		$transaction_id = $order->get_transaction_id();
		$request_args   = array(
			'StoreId'       => $this->store_id,
			'CountryCode'   => $this->country_code,
			'InvoiceNo'     => $transaction_id,
			'ArticleList'   => $refunded_items,
			'CreditDate'    => date( 'Y-m-d\TH:i:s', strtotime( 'now' ) ),
			'CorrelationId' => Collector_Checkout_Create_Refund_Data::get_refunded_order( $order_id ),
		);

		$this->log( 'PartCreditInvoice request args: ' . stripslashes_deep( json_encode( $request_args ) ) );
		return $request_args;
	}

	public static function log( $message ) {
		$collector_settings = get_option( 'woocommerce_collector_checkout_settings' );
		if ( 'yes' === $collector_settings['debug_mode'] ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'collector_checkout', $message );
		}
	}
}