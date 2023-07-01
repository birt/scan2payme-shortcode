<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add the top level menu page.
 */
function scan2payme_extension_options_page() {
    add_menu_page(
        'Scan2PayMe',
        'Scan2PayMe',
        'manage_options',
        'scan2payme',
        'scan2payme_extension_options_page_html'
    );
}
add_action( 'admin_menu', 'scan2payme_extension_options_page' );

function scan2payme_option_sanitize_showwhenstatus($input){
    return $input; // TODO
}

function scan2payme_option_sanitize_showwhenmethod($input){
    return $input; // TODO 
}

/**
 * custom option and settings
 */
function scan2payme_extension_settings_init() {
    // Register a new setting for page.
    register_setting( 'scan2payme', 'scan2payme_option_BIC' );
    register_setting( 'scan2payme', 'scan2payme_option_Name' );
    register_setting( 'scan2payme', 'scan2payme_option_IBAN' );

    $showwhenstatus_args = array( 'type' => 'string', 'sanitize_callback' => 'scan2payme_option_sanitize_showwhenstatus', 'default' => 'on-hold' );
    register_setting( 'scan2payme', 'scan2payme_option_showwhenstatus', $showwhenstatus_args ); // default: on-hold

    $showwhenmethod_args = array( 'type' => 'string', 'sanitize_callback' => 'scan2payme_option_sanitize_showwhenmethod', 'default' => 'bacs' );
    register_setting( 'scan2payme', 'scan2payme_option_showwhenmethod', $showwhenmethod_args ); // default: bacs

    // Register a new section in the page.
    add_settings_section(
        'scan2payme_section_requiredfields',
        __( 'Required fields', 'scan2payme' ), 'scan2payme_section_requiredfields_callback',
        'scan2payme'
    );

    // BIC, Name and IBAN are required fields.
    // Register these fields in the section "required fields"
    add_settings_field(
        'scan2payme_option_BIC',
            __( 'BIC', 'scan2payme' ),
        'scan2payme_option_BIC_cb',
        'scan2payme',
        'scan2payme_section_requiredfields',
        array(
            'label_for'         => 'scan2payme_option_BIC',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'scan2payme_option_Name',
            __( 'Name', 'scan2payme' ),
        'scan2payme_option_Name_cb',
        'scan2payme',
        'scan2payme_section_requiredfields',
        array(
            'label_for'         => 'scan2payme_option_Name',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'scan2payme_option_IBAN',
            __( 'IBAN', 'scan2payme' ),
        'scan2payme_option_IBAN_cb',
        'scan2payme',
        'scan2payme_section_requiredfields',
        array(
            'label_for'         => 'scan2payme_option_IBAN',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'scan2payme_option_showwhenstatus',
            __( 'Show when order is in status', 'scan2payme' ),
        'scan2payme_option_showwhenstatus_cb',
        'scan2payme',
        'scan2payme_section_requiredfields',
        array(
            'label_for'         => 'scan2payme_option_showwhenstatus',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'scan2payme_option_showwhenmethod',
            __( 'Show when method is', 'scan2payme' ),
        'scan2payme_option_showwhenmethod_cb',
        'scan2payme',
        'scan2payme_section_requiredfields',
        array(
            'label_for'         => 'scan2payme_option_showwhenmethod',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );
}
/**
 * Register our cec_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'scan2payme_extension_settings_init' );

/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function scan2payme_section_requiredfields_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Required fields', 'scan2payme' ); ?></p>
    <?php
}

function scan2payme_option_BIC_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_BIC' );
    ?>
    <input type="text" name="scan2payme_option_BIC" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

function scan2payme_option_Name_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_Name' );
    ?>
    <input type="text" name="scan2payme_option_Name" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

function scan2payme_option_IBAN_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_IBAN' );
    ?>
    <input type="text" name="scan2payme_option_IBAN" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

function scan2payme_option_showwhenstatus_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_showwhenstatus' );
    ?>
    <input type="text" name="scan2payme_option_showwhenstatus" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

function scan2payme_option_showwhenmethod_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_showwhenmethod' );
    ?>
    <input type="text" name="scan2payme_option_showwhenmethod" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

/**
 * Top level menu callback function
 */
function scan2payme_extension_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'scan2payme_messages', 'scan2payme_message', __( 'Settings Saved', 'scan2payme' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'scan2payme_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting 
            settings_fields( 'scan2payme' );
            // output setting sections and their fields
            do_settings_sections( 'scan2payme' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}