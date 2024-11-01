<?php
/**
 * Plugin Name: VES Payment
 * Plugin URI: https://www.ves.com.my
 * Description: Enable online payments using credit or debit cards, online banking and eWallets. Currently VES Payment service is only available to businesses that reside in Malaysia.
 * Version: 3.1.3
 * Author: VESPlugin
 * WC requires at least: 4.3.0
 * WC tested up to: 7.1.0
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

# Include vespay Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'vespay_init', 0 );

function vespay_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	include_once( 'src/vespay.php' );

	add_filter( 'woocommerce_payment_gateways', 'add_vespay_to_woocommerce' );
	function add_vespay_to_woocommerce( $methods ) {
		$methods[] = 'vespay';

		return $methods;
	}
}

# Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'vespay_links' );

function vespay_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=vespay' ) . '">' . __( 'Settings', 'vespay' ) . '</a>',
	);

	# Merge our new link with the default ones
	return array_merge( $plugin_links, $links );
}

add_action( 'init', 'vespay_check_response', 15 );

function vespay_check_response() {
	# If the parent WC_Payment_Gateway class doesn't exist it means WooCommerce is not installed on the site, so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	include_once( 'src/vespay.php' );

	$vespay = new vespay();
	$vespay->check_vespay_response();
}

function vespay_hash_error_msg( $content ) {
	return '<div class="woocommerce-error">Invalid data entered. Please contact your merchant for more info.</div>' . $content;
}

function vespay_payment_declined_msg( $content ) {
	return '<div class="woocommerce-error">Fail transaction. Please check with your bank system.</div>' . $content;
}

function vespay_success_msg( $content ) {
	return '<div class="woocommerce-info">The payment was successful. Thank you.</div>' . $content;
}
