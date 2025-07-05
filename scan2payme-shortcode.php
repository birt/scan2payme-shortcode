<?php
namespace scan2payme;
/*
scan2payme is free software: you can redistribute it and/or modify
it under the terms of the MIT License.

scan2payme is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See LICENSE
for more details.

 * Plugin Name: scan2payme-shortcode
 * Plugin URI: https://github.com/birt/scan2payme-shortcode
 * Description: Show EPC payment qr code on the order confirmation page with a shortcode added
 * Version: 0.0.1
 * Author: Andreas Waldherr (a.waldherr@gmail.com)
 * Author URI: https://github.com/awaldherr
 * License: MIT
 * Text Domain: scan2payme
 * Domain Path: /languages/
 * Requires at least: 6.4
 * Requires PHP: 8.0
 *
 * @package SCAN2PAYME
 */
defined( 'ABSPATH' ) || exit;

class Logo{
    public $post_id;
    public $name;
    public function __construct($post_id, $name)
    {
        $this->post_id = $post_id;
        $this->name = $name;
    }

}

if ( ! defined( 'SCAN2PAYME_PLUGIN_FILE' ) ) {
	define( 'SCAN2PAYME_PLUGIN_FILE', __FILE__ );
}

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRDataInterface;
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
    register_activation_hook( __FILE__, 'scan2payme\scan2payme_extension_activate' );

    function scan2payme_extension_deactivate() {
        // Your deactivation logic goes here.
    }
    register_deactivation_hook( __FILE__, 'scan2payme\scan2payme_extension_deactivate' );

    add_action( 'plugins_loaded', 'scan2payme\scan2payme_plugin_load_text_domain' );
    function scan2payme_plugin_load_text_domain() {
        load_plugin_textdomain( 'scan2payme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function scan2payme_get_physical_logo_path(){
        $logo_id = get_option('scan2payme_option_logo');
        if(!isset($logo_id) || strlen($logo_id) === 0 || !is_numeric($logo_id)){
            return null;
        }
        
        // second argument circumvents filters.
        return wp_get_original_image_path($logo_id, true);
    }

    function calculate_qr_version_for_data($data, $eccLevel){
        // guestimate the needed level. a upper bound for the length of the encoded data is 16 + 8xstrlen($data).
        $neededBits = 16+(strlen($data)*8);
        for($i = 1; $i <= 40; $i++){
            $maxBits = QRDataInterface::MAX_BITS[$i][QRCode::ECC_MODES[$eccLevel]];
            if($maxBits > $neededBits){
                return $i;
            }
        }
        return 40; // 40 is highest possible version
    }
  /**
 * Shortcode to display Scan2PayMe QR code for the current order in loop context.
 */
function scan2payme_qr_shortcode( $atts ) {
    if ( ! function_exists('scan2payme_output_qr') ) {
        return '<!-- Scan2PayMe plugin not active -->';
    }

    $atts = shortcode_atts( [
        'order_id' => 0,
    ], $atts, 'scan2payme_qr' );

    $order_id = intval( $atts['order_id'] );
    if ( ! $order_id && is_wc_endpoint_url( 'order-received' ) ) {
        $order_id = absint( get_query_var( 'order-received' ) );
    }

    if ( ! $order_id ) {
        return '<!-- No order ID found -->';
    }

    return scan2payme_output_qr( $order_id );
}

    function scan2payme_extension_action_show_code() {
        $order_id = absint( get_query_var('view-order') );
        $order = new \WC_Order($order_id);
        $oid = $order->get_order_number();

        $payment_method = $order->get_payment_method();
        $order_status = $order->get_status();
        $option_payment_method = get_option('scan2payme_option_showwhenmethod');
        $option_order_status = get_option('scan2payme_option_showwhenstatus');
        $option_textabove = get_option('scan2payme_option_textabove');
        $option_textunder = get_option('scan2payme_option_textunder');

        
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

        generate_and_output_qr_code($option_textabove, $option_textunder, $epc_version, $epc_encoding, $epc_identity, $epc_bic, $epc_name, $epc_iban, $epc_total, $epc_use, $epc_ref, $epc_textref, $epc_hint);
    }
    
    // TODO does the default value work if this is a fresh installation?
    add_action( get_option('scan2payme_option_showhook'), 'scan2payme\scan2payme_extension_action_show_code' ); 

    include_once dirname( SCAN2PAYME_PLUGIN_FILE ) . '/LogoQRImage.php';
    include_once dirname( SCAN2PAYME_PLUGIN_FILE ) . '/scan2payme-func.php';
    include_once dirname( SCAN2PAYME_PLUGIN_FILE ) . '/scan2payme-admin.php';
}
add_shortcode( 'scan2payme_qr', 'scan2payme_qr_shortcode' );
?>
