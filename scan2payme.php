<?php
/*
scan2payme is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

scan2payme is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with scan2payme. If not, see {URI to Plugin License}.

 * Plugin Name: scan2payme
 * Plugin URI: 
 * Description: 
 * Version: 1.0.0
 * Author: Andreas Waldherr
 * Author URI: 
 * Text Domain: scan2payme
 * Domain Path: 
 * Requires at least: 6.0
 * Requires PHP: 7.3
 *
 * @package SCAN2PAYME
 */
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SCAN2PAYME_PLUGIN_FILE' ) ) {
	define( 'SCAN2PAYME_PLUGIN_FILE', __FILE__ );
}

use chillerlan\QRCode\QRCode;
require_once 'vendor/autoload.php';

 // Test to see if WooCommerce is active (including network activated).
$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

if (
    in_array( $plugin_path, wp_get_active_and_valid_plugins() )
    || in_array( $plugin_path, wp_get_active_network_plugins() )
) {
    // wocommerce active
    function scan2payme_extension_activate() {
        // Your activation logic goes here.
    }
    register_activation_hook( __FILE__, 'scan2payme_extension_activate' );

    function scan2payme_extension_deactivate() {
        // Your deactivation logic goes here.
    }
    register_deactivation_hook( __FILE__, 'scan2payme_extension_deactivate' );

    function scan2payme_extension_action1() {
        // (1) get current order
        // (2) check in barcode store if barcode for order already exists
        // (2a) if not, create barcode and store in barcode store
        // (3) display barcode

        $order_id = absint( get_query_var('view-order') );
        $order = new WC_Order($order_id);
        $oid = $order->get_order_number();

        $payment_method = $order->get_payment_method();
        $order_status = $order->get_status();
        $option_payment_method = get_option('scan2payme_option_showwhenmethod');
        $option_order_status = get_option('scan2payme_option_showwhenstatus');

        if($option_payment_method !== $payment_method)
        {
            return; // do not show for this payment method
        }

        if($option_order_status !== $order_status)
        {
            return; // do not show for this order status
        }

        $epc_version = "001";
        $epc_encoding = "1";
        $epc_identity = "SCT";
        $epc_bic = get_option( 'scan2payme_option_BIC' );
        $epc_name = get_option( 'scan2payme_option_Name' );;
        $epc_iban = get_option( 'scan2payme_option_IBAN' );
        $epc_total = "EUR".$order->get_total();
        $epc_use = "";
        $epc_ref = $order->get_order_number();
        $epc_textref = "";
        $epc_hint = "";

        // only one line can be filled, ref or textref!
        if(strlen($epc_ref) > 0 && strlen($epc_textref) > 0){
            $epc_textref = "";
        }

        $qrdata = "BCD".PHP_EOL.$epc_version.PHP_EOL.$epc_encoding.PHP_EOL.$epc_identity.PHP_EOL.$epc_bic.PHP_EOL.$epc_name.PHP_EOL.$epc_iban.PHP_EOL.$epc_total.PHP_EOL.$epc_use.PHP_EOL.$epc_ref.PHP_EOL.$epc_textref.PHP_EOL.$epc_hint;

        // quick and simple:
        echo '<img src="'.(new QRCode)->render($qrdata).'" alt="'.$qrdata.'" />';
    }
    
    add_action( 'woocommerce_after_order_details', 'scan2payme_extension_action1' ); 

    include_once dirname( SCAN2PAYME_PLUGIN_FILE ) . '/scan2payme-admin.php';
}

?>