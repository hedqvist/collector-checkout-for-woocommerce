<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Collector_Bank_Requests_Calculate_Auth {
	public static function calculate_auth( $body, $path ) {
		error_log( 'Body: ' . var_export( $body, true ) );
		error_log( 'Path: ' . $path );
		return 'SharedKey ' . base64_encode( 'combuyit' . ':' . hash( 'sha256', $body . $path . '4bxpaFU;u?So7eI@QTQR*2btWKL1wS' ) );
	}
}
