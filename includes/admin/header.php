<?php
/**
 * Plugin admin options header
 */

// Include the admin page CSS
include HELPDOCS_PLUGIN_ADMIN_PATH.'css/style.php';

// The title
$title = helpdocs_title();

// Multisite header
if ( get_option( HELPDOCS_GO_PF.'multisite_sfx' ) && get_option( HELPDOCS_GO_PF.'multisite_sfx' ) != '' ) {
    $sfx = get_option( HELPDOCS_GO_PF.'multisite_sfx' );
} else {
    $sfx = helpdocs_multisite_suffix();
}

// Get the tabs
$menu_items = helpdocs_plugin_menu_items();

// Get the active tab
global $current_screen;
if ( $current_screen->post_type == 'help-docs' ) {
    $tab = 'manage';
} elseif ( $current_screen->post_type == 'help-doc-imports' ) {
    $tab = 'imports';
}  else {
    $tab = helpdocs_get( 'tab' ) ?? 'documentation';
}

// Get the logo
$logo = helpdocs_logo();

// Get the colors
$HELPDOCS_COLORS = new HELPDOCS_COLORS();
$color_bg = $HELPDOCS_COLORS->get( 'bg' );
$color_fg = $HELPDOCS_COLORS->get( 'fg' );
$color_cl = $HELPDOCS_COLORS->get( 'cl' );
?>

<style>
html, body, #wpwrap, #wpcontent, #wpbody, #wpbody-content, .wrap {
    background-color: <?php echo esc_attr( $color_bg ); ?> !important;
    color: <?php echo esc_attr( $color_fg ); ?> !important;
}
.admin-title-cont h1, 
.tab-header, 
.wp-heading-inline, 
.form-table th,
.subsubsub .count, 
#footer-thankyou, 
#footer-upgrade {
    color: <?php echo esc_attr( $color_fg ); ?> !important;
}
#wpbody-content a, 
#footer-thankyou a, 
#footer-upgrade a {
    color: <?php echo esc_attr( $color_cl ); ?> !important;
}
.nav-tab-wrapper .nav-tab {
    background-color: <?php echo esc_attr( $color_bg ); ?> !important;
    filter: brightness(95%);
}
.nav-tab-wrapper .nav-tab.nav-tab-active {
    border-bottom: 1px solid <?php echo esc_attr( $color_bg ); ?>;
    background-color: transparent !important;
}
.tab-content {
    padding-top: 19px;
}
.tab-header {
    display: inline-block;
    margin: 0 5px 0 0 !important;
    padding-bottom: 4px;
    line-height: 1.3;
}
.tab-header-hr {
    margin-top: 8px;
}
</style>

<div class="wrap <?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>" style="background: <?php echo esc_attr( $color_bg ); ?> !important;">
    <div class="admin-title-cont">
        <img src="<?php echo esc_url( $logo ); ?>" width="32" height="32" alt="Admin Help Docs Logo">
        <h1><span id="plugin-page-title"><?php echo esc_attr( $title ); ?></span> <span id="plugin-multisite-suffix"><?php echo wp_kses_post( $sfx ); ?></span></h1>
    </div>
    <?php if ( get_option( HELPDOCS_GO_PF.'hide_version' ) != 1 ) { ?>
        <div id="plugin-version">Version <?php echo esc_attr( HELPDOCS_VERSION ); ?></div>
    <?php } ?>

    <?php if ( helpdocs_get( 'settings-updated' ) ) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e( 'Settings saved.', 'admin-help-docs' ) ?></strong></p>
        </div>
    <?php } ?>

    <br><br>
    <div class="tabs-wrapper">
        <nav class="nav-tab-wrapper">
            <?php
            // Skip if multisite
            $multisite_skip = [];

            // Iter the menu items
            foreach ( $menu_items as $key => $menu_item ) { 
                // Skip if multisite
                if ( is_network_admin() && in_array( $key, $multisite_skip ) ) {
                    continue;
                }

                // Skip if no access
                if ( !helpdocs_user_can_edit() && isset( $menu_item[2] ) && $menu_item[2] == true ) {
                    continue;
                }

                // Skip if hidden subpage
                if ( isset( $menu_item[3] ) && $menu_item[3] == true ) {
                    continue;
                }

                // Set the vars
                $slug = $key;
                $name = $menu_item[0];

                // Skip Changelog
                if ( $slug == 'changelog' ) {
                    continue;
                }

                // Sanitize name
                $allowed_html = [
                    'span' => [
                        'class' => []
                    ]
                ];

                //  The link
                if ( isset( $menu_item[4] ) && $menu_item[4] != '' ) {
                    $link = $menu_item[4];
                } else {
                    $link = helpdocs_plugin_options_path( $slug );
                }
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="nav-tab <?php if ( $tab === $slug || $tab === null ) : ?>nav-tab-active<?php endif; ?>"><?php echo wp_kses( $name, $allowed_html ); ?></a>
            <?php } ?>
        </nav>
    </div>
</div>