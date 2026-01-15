<style>
.wrap.admin-help-docs .notice {
    margin: 5px 25px 15px 0;
} 
.wrap.admin-help-docs .notice .button {
    margin: 0px 4px;
}
.wrap.admin-help-docs .notice-buttons {
    margin-left: 10px;
}
#save-reminder {
    display: none;
    position: fixed;
    bottom: 3rem;
    right: 2rem;
    background: yellow;
    color: black;
    padding: 20px;
    border-radius: 10px;
    border: 2px solid black;
    box-shadow: 4px 4px 16px;
    font-weight: 600;
    font-size: medium;
}
#other-settings {
    margin-top: 30px;
}
.helpdocs-sorter {
    max-width: 420px;
    margin: 0;
    padding: 0;
}
.helpdocs-sorter-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    margin-bottom: 6px;
    background: #fff;
    border: 1px solid #ccd0d4;
}
.helpdocs-sort-handle {
    cursor: grab;
}
.helpdocs-sorter-item.ui-sortable-helper {
    box-shadow: 0 4px 12px rgba( 0, 0, 0, 0.15 );
}
</style>

<?php 
include 'header-page.php';
$allowed_html = helpdocs_wp_kses_allowed_html(); 

// Build the current url
$page = helpdocs_plugin_options_short_path();
$tab = 'adminmenu';
$current_url = helpdocs_plugin_options_path( $tab );

// Are we resetting options?
$reset = helpdocs_get( 'reset' );
if ( $reset && $reset === 'adminmenu' ) {

    // Check if confirmed
    if ( !helpdocs_get( 'confirmed', '==', 'true' ) ) {

        // Remove the query string
        helpdocs_remove_qs_without_refresh( 'reset' );

        // Add a notice to confirm
        ?>
        <div class="notice notice-warning is-dismissible">
        <p><?php
        echo esc_html__( 'Are you absolutely sure you want to reset the admin menu settings you have set?', 'admin-help-docs' ); ?> <span class="notice-buttons"><a class="button button-secondary" href="<?php echo esc_url( $current_url ); ?>&reset=<?php echo esc_attr( $reset ); ?>&confirmed=true">Yes</a> <a class="button button-secondary" href="<?php echo esc_url( $current_url ); ?>">No</a></span></p>
        </div>
        <?php

    } else {

        // Remove the query string
        helpdocs_remove_qs_without_refresh( [ 'reset', 'confirmed' ] );

        // Get the global options
        $HELPDOCS_GLOBAL_OPTIONS = new HELPDOCS_GLOBAL_OPTIONS();
        $reset_keys = (new HELPDOCS_GLOBAL_OPTIONS)->settings_adminmenu;

        // Iter the options
        foreach ( $reset_keys as $reset_key ) {

            // Delete the option
            delete_option( HELPDOCS_GO_PF.$reset_key );
        }

        // Add a notice to confirm
        ?>
        <div class="notice notice-success is-dismissible">
        <p><?php
        echo esc_html__( 'You have successfully reset the admin menu settings. Take one last look at what you will be missing out on thanks to your bold decision. Refresh the page to see your new changes.', 'admin-help-docs' ); ?></p>
        </div>
        <?php
    }
}

// Update json
if ( helpdocs_get( 'settings-updated', '==', 'true' ) ) {
    helpdocs_create_json_from_settings();
}
?>

<form method="post" action="options.php">
    <?php settings_fields( HELPDOCS_PF.'group_adminmenu' ); ?>
    <?php do_settings_sections( HELPDOCS_PF.'group_adminmenu' ); ?>
    <table class="form-table">

        <?php echo wp_kses( helpdocs_options_tr( 'enable_admin_menu_sorting', 'Enable Admin Menu Sorting', 'checkbox' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'colorize_separators', 'Colorize Separators', 'checkbox' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'color_admin_menu_sep', 'Separator Color', 'color', null, [ 'default' => '#d1d1d1' ] ), $allowed_html ); ?>

        <?php 
        // Get the parent menu items
        global $menu;

        // Default WP separators
        $default_separators = [
            'separator1',
            'separator2',
            'separator-last'
        ];

        // Build current menu items array
        $admin_menu_items = [];
        foreach ( $menu as $menu_item ) {
            $slug = $menu_item[2] ?? '';

            if ( str_starts_with( (string) $slug, 'separator' ) ) {
                $label = '-- Separator -- (place at bottom to hide)';
            } else {
                $label = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';
                $label = preg_replace( '/\s+\d+.*$/', '', $label );
                $label = trim( $label );
            }

            $admin_menu_items[ $slug ] = [
                'label' => $label,
                'value' => $slug
            ];
        }

        // Ensure default WP separators are included
        foreach ( $default_separators as $slug ) {
            if ( ! isset( $admin_menu_items[ $slug ] ) ) {
                $admin_menu_items[ $slug ] = [
                    'label' => '-- Separator -- (place at bottom to hide)',
                    'value' => $slug
                ];
            }
        }

        // Add your extra separators
        for ( $i = 1; $i <= 3; $i++ ) {
            $extra_slug = "separator-helpdocs-extra{$i}";
            $admin_menu_items[ $extra_slug ] = [
                'label' => '-- Separator -- (place at bottom to hide)',
                'value' => $extra_slug
            ];
        }

        // Re-index to numeric array for your sorter
        $admin_menu_items = array_values( $admin_menu_items );

        // $saved_order = get_option( HELPDOCS_GO_PF . 'admin_menu_order' );

        // if ( ! empty( $saved_order ) && is_array( $saved_order ) ) {
        //     $saved_order = array_filter( $saved_order, function( $slug ) {
        //         return $slug !== 'separator-helpdocs-extra';
        //     });

        //     // Save the cleaned array back to the option
        //     update_option( HELPDOCS_GO_PF . 'admin_menu_order', $saved_order );
        // }
        ?>
        
        <?php echo wp_kses( helpdocs_options_tr( 'admin_menu_order', 'Parent Menu Items', 'sorter', '', $admin_menu_items ), $allowed_html ); ?>

    </table>
    
    <?php submit_button(); ?>
</form>

<div id="other-settings"><a href="<?php echo esc_url( $current_url ); ?>&reset=adminmenu">Reset Admin Menu Order to Default</a> | <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settingsie' ) ); ?>">Copy Settings from Another Site</a></div>

<div id="save-reminder">Don't forget to save your changes!</div>