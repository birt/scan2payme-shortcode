<?php
namespace scan2payme;
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
        'scan2payme\scan2payme_extension_options_page_html'
    );
}
add_action( 'admin_menu', 'scan2payme\scan2payme_extension_options_page' );

function scan2payme_option_sanitize_IBAN($input){
    $input = strtoupper($input);
    $old = get_option( 'scan2payme_option_IBAN' );
    $valid = true;
    if (strlen($input) > 34){
        $valid = false;
        add_settings_error('scan2payme_messages', 'scan2payme_message', __('IBAN invalid! Must be shorter than 34 symbols!', 'scan2payme'), 'error');
    }

    if(is_numeric(substr($input, 0, 1)) || is_numeric(substr($input, 1, 1))){
        $valid = false;
        add_settings_error('scan2payme_messages', 'scan2payme_message', __('IBAN invalid! First two symbol must be country identifier!', 'scan2payme'), 'error');
    }

    $bban = substr($input, 2);
    for($i = 0; $i < strlen($bban); $i++){
        if(!is_numeric(substr($bban, $i, 1))){
            $valid = false;
            add_settings_error('scan2payme_messages', 'scan2payme_message', __('IBAN invalid! Account part can only contain numbers!', 'scan2payme'), 'error');
            $i = strlen($bban); // abort, no need to check the rest
        }
    }

    if(!$valid){
        return $old;
    } else {
        return $input;
    }
}

function scan2payme_option_sanitize_BIC($input){
    $input = strtoupper($input);
    $old = get_option( 'scan2payme_option_BIC' );
    $valid = true;

    if(strlen($input) != 8 && strlen($input) != 11){
        $valid = false;
        add_settings_error('scan2payme_messages', 'scan2payme_message', __('BIC invalid! Must be 8 or 11 symbols long!', 'scan2payme'), 'error');
    }

    if(!$valid){
        return $old;
    } else {
        return $input;
    }
}

function scan2payme_option_sanitize_showwhenstatus($input){
    return $input;
}

function scan2payme_option_sanitize_showwhenmethod($input){
    return $input; // TODO 
}

/**
 * custom option and settings
 */
function scan2payme_extension_settings_init() {
    // Register a new setting for page.
    $bic_args = array ('type' => 'string', 'sanitize_callback' => 'scan2payme\scan2payme_option_sanitize_BIC');
    register_setting( 'scan2payme', 'scan2payme_option_BIC', $bic_args );
    register_setting( 'scan2payme', 'scan2payme_option_Name' );
    $iban_args = array ('type' => 'string', 'sanitize_callback' => 'scan2payme\scan2payme_option_sanitize_IBAN');
    register_setting( 'scan2payme', 'scan2payme_option_IBAN', $iban_args );

    $showwhenstatus_args = array( 'type' => 'string', 'sanitize_callback' => 'scan2payme\scan2payme_option_sanitize_showwhenstatus', 'default' => 'on-hold' );
    //$showwhenstatus_args = array( 'type' => 'string', 'sanitize_callback' => 'scan2payme_option_sanitize_showwhenstatus', 'default' => 'on-hold' );
    register_setting( 'scan2payme', 'scan2payme_option_showwhenstatus', $showwhenstatus_args ); // default: on-hold

    $showwhenmethod_args = array( 'type' => 'string', 'sanitize_callback' => 'scan2payme\scan2payme_option_sanitize_showwhenmethod', 'default' => 'bacs' );
    register_setting( 'scan2payme', 'scan2payme_option_showwhenmethod', $showwhenmethod_args ); // default: bacs

    $textabove_args = array( 'type' => 'string', 'default' => 'Scan2Pay Me' );
    register_setting( 'scan2payme', 'scan2payme_option_textabove', $textabove_args);
    $textunder_args = array( 'type' => 'string', 'default' => 'Scan this QR code with your banking app to initiate a bank transfer.' );
    register_setting( 'scan2payme', 'scan2payme_option_textunder', $textunder_args);
    register_setting( 'scan2payme', 'scan2payme_option_logo');

    // Register a new section in the page.
    add_settings_section(
        'scan2payme_section_requiredfields',
        __( 'Scan2Pay Me required fields', 'scan2payme' ), 'scan2payme\scan2payme_section_requiredfields_callback',
        'scan2payme'
    );

    // BIC, Name and IBAN are required fields.
    // Register these fields in the section "required fields"
    add_settings_field(
        'scan2payme_option_BIC',
            __( 'BIC', 'scan2payme' ),
        'scan2payme\scan2payme_option_BIC_cb',
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
        'scan2payme\scan2payme_option_Name_cb',
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
        'scan2payme\scan2payme_option_IBAN_cb',
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
        'scan2payme\scan2payme_option_showwhenstatus_cb',
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
        'scan2payme\scan2payme_option_showwhenmethod_cb',
        'scan2payme',
        'scan2payme_section_requiredfields',
        array(
            'label_for'         => 'scan2payme_option_showwhenmethod',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    // Register a new section in the page.
    add_settings_section(
        'scan2payme_section_optionalfields',
        __( 'Scan2Pay Me optional fields', 'scan2payme' ), 'scan2payme\scan2payme_section_optionalfields_callback',
        'scan2payme'
    );


    add_settings_field(
        'scan2payme_option_textabove',
            __( 'textabove', 'scan2payme' ),
        'scan2payme\scan2payme_option_textabove_cb',
        'scan2payme',
        'scan2payme_section_optionalfields',
        array(
            'label_for'         => 'scan2payme_option_textabove',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'scan2payme_option_textunder',
            __( 'textunder', 'scan2payme' ),
        'scan2payme\scan2payme_option_textunder_cb',
        'scan2payme',
        'scan2payme_section_optionalfields',
        array(
            'label_for'         => 'scan2payme_option_textunder',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'scan2payme_option_logo',
            __( 'logo', 'scan2payme' ),
        'scan2payme\scan2payme_option_logo_cb',
        'scan2payme',
        'scan2payme_section_optionalfields',
        array(
            'label_for'         => 'scan2payme_option_logo',
            'class'             => 'scan2payme_row',
            'scan2payme_custom_data' => 'custom',
        )
    );
}
/**
 * Register our cec_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'scan2payme\scan2payme_extension_settings_init' );


function scan2payme_section_requiredfields_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'There is no validation implemented yet!', 'scan2payme' ); ?></p>
    <?php
}

function scan2payme_section_optionalfields_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Optional fields. Provide your users with information about how you want them to use the code.', 'scan2payme' ); ?></p>
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
    <select name="scan2payme_option_showwhenstatus">
        <option value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>"><?php echo isset( $options ) ? esc_attr( $options ) : ''; ?></option>
    </select>
    
    <?php
}

function scan2payme_option_showwhenmethod_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_showwhenmethod' );
    ?>
    <select name="scan2payme_option_showwhenmethod">
        <option value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>"><?php echo isset( $options ) ? esc_attr( $options ) : ''; ?></option>
    </select>
    
    <?php
}

function scan2payme_option_textunder_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_textunder' );
    ?>
    <input type="text" name="scan2payme_option_textunder" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

function scan2payme_option_textabove_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'scan2payme_option_textabove' );
    ?>
    <input type="text" name="scan2payme_option_textabove" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>">
    <?php
}

function scan2payme_option_logo_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $logos = scan2payme_get_available_logos();
    $option_logo_id = get_option( 'scan2payme_option_logo' );
    $selectedLogo = null;
    if(isset($option_logo_id)) {
        foreach ( $logos as $logo ) {
            if ( $option_logo_id == $logo->post_id ) {
                $selectedLogo = $logo;
            }
        }
    }

    // delete the option if it is not available anymore.
    if($selectedLogo == null){
        $updated = update_option('scan2payme_option_logo', null);
    }
    ?>
    <select name="scan2payme_option_logo">
        <?php if($selectedLogo !== null){ ?>
        <option value="<?php echo esc_attr( $selectedLogo->post_id ); ?>"><?php echo esc_attr( $selectedLogo->name ); ?></option>
        <?php } ?>

        <?php foreach($logos as $logo){ ?>
        <option value="<?php echo esc_attr( $logo->post_id ); ?>"><?php echo esc_attr( $logo->name ); ?></option>
        <?php } ?>
        
    </select>
    <?php
}

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

function scan2payme_get_available_logos(){
    $logo_query_args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image/png',
        'post_status'    => 'inherit',
        'orderby'        => 'post_date',
        'posts_per_page' =>  30,

    );
    $logo_query = new \WP_Query( $logo_query_args );
    $logos = array();
    if ( $logo_query->have_posts() ) {
        for($i = 0; $i < count($logo_query->posts); $i++){
            $logos[] = new Logo($logo_query->posts[$i]->ID, $logo_query->posts[$i]->post_name);
        }
    }
    return $logos;
}