=== Plugin Name ===
VES Payment
Contributors: VESPlugin
Tags: payment gateway, Malaysia, online banking
Requires at least: 4.3
Tested up to: 6.1
Stable tag: 3.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

VES payment gateway plugin for WooCommerce. 

== Description ==

VES payment gateway plugin for WooCommerce. This plugin enable online payment using credit or debit cards (Visa and Mastercard only), online banking (for Malaysian banks only) and eWallets. Currently VES Payment is only available for businesses that reside in Malaysia.

== Installation ==

1. Make sure that you already have WooCommerce plugin installed and activated.
2. From your Wordpress admin dashboard, go to menu 'Plugins' and 'Add New'.
3. Key in 'VES Payment' in the 'Search Plugins' field and press enter.
4. It will display the plugin and press intall.
5. Activate the plugin through the 'Plugins' screen in WordPress.
6. Go to menu WooCommerce, settings, Checkout, VES Payment and fill in your merchant id and secret key. 
7. Make sure the 'Enable this payment gateway' is ticked. Click on 'Save changes' button.
8. * In VES Payment Dashboard make sure you key in your return URL and callback URL as http://your_domain/checkout/order-received/ and choose 'Read response and send email if error' for callback response, finally press Save. Please leave the 'Return URL Parameters' field empty.

== Frequently Asked Questions ==

= Do I need to sign up with VES Payment in order to use this plugin? =

Yes, we require info such as merchant id and secret key that is only available after you sign up with VES Payment.

= Can I use this plugin without using WooCommerce? =

No.

= What currency does it support? =

Currently VES Payment only support Malaysian Ringgit (RM).

= What if I have some other question related to VES Payment? =

Please open a ticket by log in to VES Payment Dashboard and look for menu support.

== Changelog ==

= 3.1.3 =
* supports Wordpress 6.1.1
* supports Woocommerce 7.1.0

= 3.1.2 =
* supports Wordpress 5.5.0
* supports Woocommerce 4.4.1

= 3.1 =
* supports Wordpress 5.3.0
* supports Woocommerce 3.8.x

= 3.0.5 =
* update README

= 3.0.4 =
* fix versioning

= 3.0.3 =
* fix issue with plugin not initialized properly
* supports Wordpress 4.9.x
* supports Woocommerce 3.3.x

= 3.0.2 =
* Fix issue with orders with the same number

= 3.0.1 =
* Fix issue with failed payments

= 3.0.0 =
* initial version for Woocommerce 3.x full compatibility
* remove usage of WC deprecated functions
* wp_redirect doesn't always redirect. Add exit() to ensure redirection
* added proper callback response

= 2.1 =
* Send billing name, email and phone to VES Payment so that customer does not have to re-enter at VES Payment form

= 2.0 =
* Solve the issue where multiple emails were sent to both buyer and seller after payment is complete.
* Upon successful payment customer will see order complete page.
