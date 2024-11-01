<?php

/**
 * VES Payment Gateway Class
 */
class vespay extends WC_Payment_Gateway {
	function __construct() {
		$this->id = "vespay";

		$this->method_title = __( "VES Payment", 'vespay' );

		$this->method_description = __( "Pay securely with credit or debit cards, internet banking (FPX) and eWallet.", 'vespay' );

		$this->title = __( "VES Payment - Pay securely with credit or debit cards, internet banking (FPX) and eWallet.", 'vespay' );

		$this->has_fields = true;

		$this->init_form_fields();

		$this->init_settings();

		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}

		add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'check_vespay_response' ) );

		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
		}

		add_action( 'woocommerce_receipt_' . $this->id, array(
        $this,
        'pay_for_order'
    ) );
	}

	# Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'        => array(
				'title'   => __( 'Enable / Disable', 'vespay' ),
				'label'   => __( 'Enable this payment gateway', 'vespay' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'environment_mode'         => array(
				'title'       => __( 'Environment Mode', 'vespay' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Choose whether you wish to use sandbox or production mode.', 'vespay' ),
				'default'     => 'sandbox',
				'desc_tip'    => true,
				'options'     => array(
					'live'	=> __( 'Live', 'vespay' ),
					'sandbox'	=> __( 'Sandbox', 'vespay' )
				),
			),
			'universal_form' => array(
				'title'    => __( 'Merchant ID', 'vespay' ),
				'type'     => 'text',
				'desc_tip' => __( 'This is the merchant ID that you can obtain from profile page in vespaypx', 'vespay' ),
			),
			'secretkey'      => array(
				'title'    => __( 'Secret Key', 'vespay' ),
				'type'     => 'text',
				'desc_tip' => __( 'This is the secret key that you can obtain from vespay profile page', 'vespay' ),
			),
			'title' => array(
				'title' => __( 'Title', 'vespay' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'vespay' ),
				'default' => __( 'VES Payment - Pay securely with internet banking (FPX), credit / debit cards and eWallet.', 'vespay' ),
				'desc_tip'      => true,
				),
			'description' => array(
			'title' => __( 'Payment Description', 'vespay' ),
			'type' => 'textarea',
			'default' => ''
			)
		);
	}

	
	# Submit payment
	public function process_payment( $order_id ) {
		# Get this order's information so that we know who to charge and how much


		$order = wc_get_order($order_id);
		$order->update_status('pending');

		return array(
			'result'   => 'success',
			'redirect' => './wc-api/vespay/?submit_data=true&order_id='.$order_id
		);
	}


	public function check_vespay_response() {
		$submit_data = sanitize_text_field($_GET['submit_data']);

		if(isset($submit_data) && $submit_data == 'true')
		{
			$order_id = sanitize_text_field($_GET['order_id']);

			$customer_order = wc_get_order( $order_id );

			$old_wc = version_compare( WC_VERSION, '3.0', '<' );

			# Prepare the data to send to VES Payment
			$description = "Payment_for_order_" . $order_id;

			$order_id = sanitize_text_field($old_wc ? $customer_order->id : $customer_order->get_id());
			$amount = number_format($old_wc ? $customer_order->order_total : $customer_order->get_total(), 2);
			$name = sanitize_text_field($old_wc ? $customer_order->billing_first_name . ' ' . $customer_order->billing_last_name : $customer_order->get_billing_first_name() . ' ' . $customer_order->get_billing_last_name());
			$email = sanitize_email($old_wc ? $customer_order->billing_email : $customer_order->get_billing_email());
			$phone = sanitize_text_field($old_wc ? $customer_order->billing_phone : $customer_order->get_billing_phone());
			$secretkey = sanitize_text_field($this->secretkey);

			$hash_value = hash_hmac('sha256', sanitize_text_field($this->secretkey) . sanitize_text_field($order_id) . sanitize_text_field($amount) . sanitize_text_field($description), sanitize_text_field($this->secretkey) );

			$post_args = array(
			'order_id' => $order_id,
			'amount'   => $amount,
			'description'   => $description,			
			'name'     => $name,
			'email'    => $email,
			'phone'    => $phone,
			'return_url'    => site_url(),
			'callback_url'    => site_url(),
			'hash'     => $hash_value
		);

			if (sanitize_text_field($this->environment_mode) == 'sandbox') {
				$environment_mode_url = 'https://sandbox-app.ves.com.my/checkout_v1/'.sanitize_text_field($this->universal_form);
			}else{
				$environment_mode_url = 'https://portal.ves.my/checkout_v1/'.sanitize_text_field($this->universal_form);
			}

				echo "<form action='".esc_url(sanitize_text_field($environment_mode_url))."' id='submit_data' method='POST'>";	
				
				foreach ($post_args as $key => $value) {
					echo "<input type='hidden' name='".esc_attr($key)."' value='".esc_attr($value)."' >";
				}

			echo "</form>";
			echo "<script>document.getElementById('submit_data').submit();</script>";
		}
		elseif ( isset( $_REQUEST['status_id'] ) && isset( $_REQUEST['order_id'] ) && isset( $_REQUEST['msg'] ) && isset( $_REQUEST['transaction_id'] ) && isset( $_REQUEST['hash'] ) ) {
			global $woocommerce;
			$is_callback = isset( $_POST['order_id'] ) ? true : false;
			$order = wc_get_order( sanitize_text_field($_REQUEST['order_id']) );
			$old_wc = version_compare( WC_VERSION, '3.0', '<' );
			$order_id = sanitize_text_field($old_wc ? $order->id : $order->get_id());
			
			if ( $order && $order_id != 0 ) {
				# Check if the data sent is valid based on the hash value
				
				$hash_value = hash_hmac('sha256',  sanitize_text_field($this->secretkey) . sanitize_text_field($_REQUEST['status_id']) . sanitize_text_field($_REQUEST['order_id']) . sanitize_text_field($_REQUEST['transaction_id']) . sanitize_text_field($_REQUEST['msg']) , sanitize_text_field($this->secretkey));				

				if ( $hash_value == sanitize_text_field($_REQUEST['hash']) ) {
					if ( sanitize_text_field($_REQUEST['status_id']) == 1 || sanitize_text_field($_REQUEST['status_id']) == '1' ) {
						if ( strtolower( $order->get_status() ) == 'pending' || strtolower( $order->get_status() ) == 'processing' ) {
							# only update if order is pending
							if ( strtolower( $order->get_status() ) == 'pending' ) {
								$order->payment_complete();
								$order->add_order_note( 'Payment successfully made through VES Payment. Transaction reference is ' . sanitize_text_field($_REQUEST['transaction_id']) );
							}

							if ( $is_callback ) {
								echo 'OK';
							} else {
								# redirect to order receive page
								wp_redirect( $order->get_checkout_order_received_url() );
							}
							exit();
						}
					} else {
						if ( strtolower( $order->get_status() ) == 'pending' ) {
							if ( ! $is_callback ) {
								$order->add_order_note( 'Payment was unsuccessful. Transaction reference is ' . sanitize_text_field($_REQUEST['transaction_id']) );
								wc_add_notice( __( 'Payment failed. Please check with your bank.', 'gateway' ), 'error' );
								wp_redirect( wc_get_page_permalink( 'checkout' ) );
								exit();
							}
						}
					}
				} else {
					add_filter( 'the_content', 'vespay_hash_error_msg' );
				}
			}

			if ( $is_callback ) {
				echo 'OK';

				exit();
			}
		}
	}

	# Validate fields, do nothing for the moment
	public function validate_fields() {
		return true;
	}

	# Check if we are forcing SSL on checkout pages, Custom function not required by the Gateway for now
	public function do_ssl_check() {
		if ( $this->enabled == "yes" ) {
			if ( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
				echo "<div class=\"error\"><p>" . sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . "</p></div>";
			}
		}
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 * Note: Not used for the time being
	 * @return bool
	 */
	public function is_valid_for_use() {
		return in_array( get_woocommerce_currency(), array( 'MYR' ) );
	}
}
