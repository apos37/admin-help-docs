<?php 
include 'header-page.php';
$allowed_html = helpdocs_wp_kses_allowed_html(); 

// Update json
if ( helpdocs_get( 'settings-updated', '==', 'true' ) && get_option( HELPDOCS_GO_PF.'import_link' ) && get_option( HELPDOCS_GO_PF.'import_link' ) != '' ) {
    $import = helpdocs_import_settings_from_json( get_option( HELPDOCS_GO_PF.'import_link' ) );
    if ( $import === 'invalid_file' ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Sorry, the settings link you provided is not readable. Please try a different link.', 'admin-help-docs' ); ?></p>
        </div>
        <?php
    } else {
        wp_safe_redirect( helpdocs_plugin_options_path( 'settings' ) );
    }
}

// Json link
$upload_dir = wp_upload_dir();
$json_path = $upload_dir[ 'baseurl' ].'/'.HELPDOCS_TEXTDOMAIN.'/settings.json';
?>

<p><em><strong>Note:</strong> All settings will be imported except for the "Additional Roles That Can Add/Edit Help Sections." This is to ensure nobody gets premature access.</em></p>

<form method="post" action="options.php">
    <?php settings_fields( HELPDOCS_PF.'group_settingsie' ); ?>
    <?php do_settings_sections( HELPDOCS_PF.'group_settingsie' ); ?>
    <table class="form-table">

        <tr>
            <th>My Settings Link</th>
            <td><strong>Click to Copy:</strong> <?php echo helpdocs_click_to_copy( 'export_link', $json_path, null, $json_path, true ); ?></td>
        </tr>

        <?php echo wp_kses( helpdocs_options_tr( 'import_link', 'Paste Settings Link Here', 'text', '<br>Filename: <strong>settings.json</strong>.', [ 'pattern' => '^https?:\/\/.+settings\.json$' ] ), $allowed_html ); ?>

    </table>
    
    <?php submit_button( 'Import Settings' ); ?>
</form>
