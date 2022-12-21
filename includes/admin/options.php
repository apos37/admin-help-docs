<?php
/**
 * Admin options page.
 */

// Get the tabs
$menu_items = helpdocs_plugin_menu_items();

// Get the active tab
$tab = helpdocs_get( 'tab' ) ?? 'documentation';
?>

<div class="wrap <?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>" style="padding: 20px; background: #f6f9fc;">

    <?php include 'header.php'; ?>

    <div class="tab-content">
        <?php
        foreach ( $menu_items as $key => $menu_item ) {
            if ( $tab === $key ) { 
                include 'option-'.$key.'.php';
            }
        }

        // What to do if there is no tab?
        if ( !helpdocs_get( 'tab' ) || !array_key_exists( helpdocs_get( 'tab' ), $menu_items ) ) {
            ?>
            <br><br>
            <?php
            wp_safe_redirect( helpdocs_plugin_options_path( 'documentation' ) );
        }
        ?>
    </div>
</div>