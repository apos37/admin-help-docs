<?php
namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

// Logo
$logo_url = sanitize_url( get_option( 'helpdocs_logo', '' ) );
if ( strpos( $logo_url, '/wp-content/plugins/admin-help-docs/includes/admin/img/logo.png' ) !== false ) { // TODO: Remove this in a future version
    $logo_url = str_replace( '/includes/admin/img/logo.png', '/inc/img/logo.png', $logo_url );
    update_option( 'helpdocs_logo', $logo_url );
}
if ( ! $logo_url ) {
    $logo_url = Bootstrap::url( 'inc/img/logo.png' );
}

// Version
$version = esc_html__( 'Version', 'admin-help-docs' ) . ' ' . esc_attr( Bootstrap::version() );

// Get the active tab
global $current_screen;
$real_tab = Menu::get_current_tab();

if ( ! $real_tab ) {
    if ( isset( $current_screen->id ) && $current_screen->id == 'edit-' . Folders::$taxonomy ) {
        $real_tab = 'folders';
    } elseif ( isset( $current_screen->post_type ) && $current_screen->post_type == HelpDocs::$post_type ) {
        $real_tab = 'manage';
    } elseif ( isset( $current_screen->post_type ) && $current_screen->post_type == Imports::$post_type ) {
        $real_tab = 'imports';
    } else {
        $real_tab = 'documentation';
    }
}

// Highlight 'imports' when on the hidden 'import' editor
$active_menu_key = ( $real_tab === 'import' ) ? 'imports' : $real_tab;

$tabs          = Menu::tabs();
$current_data  = $tabs[ $real_tab ] ?? [];
$user_can_view = Helpers::user_can_view();
$user_can_edit = Helpers::user_can_edit();
?>
<div id="helpdocs-header">
    <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( Bootstrap::name() ); ?> Logo" title="<?php echo esc_html( $version ); ?>" class="logo">

    <div class="title-cont">
        <h1 title="<?php echo esc_html( $version ); ?>"><?php echo esc_attr( Helpers::page_title() ); ?></h1>
    </div>

    <div class="tabs-wrapper">
        <?php foreach ( $tabs as $key => $tab ) {
            // Skip if no access
            if ( ( $key == 'documentation' && ! $user_can_view ) || ( $key != 'documentation' && ! $user_can_edit ) || ( $key === 'support' && ! get_option( 'helpdocs_contact_form' ) ) ) {
                continue;
            }

            // Skip if hidden subpage
            if ( isset( $tab[ 'hidden' ] ) && $tab[ 'hidden' ] == true ) {
                continue;
            }

            //  The link
            if ( isset( $tab[ 'link' ] ) && $tab[ 'link' ] != '' ) {
                $link = $tab[ 'link' ];
            } else {
                $link = Bootstrap::tab_url( $key );
            }
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="helpdocs-tab <?php if ( $active_menu_key === $key ) : ?>helpdocs-tab-active<?php endif; ?>"><?php echo esc_html( $tab[ 'label' ] ?? 'Unknown' ); ?></a>
        <?php } ?>
    </div>
</div>
<div id="helpdocs-subheader">
    <div class="subheader-left">
        <h2 class="tab-title"><?php echo esc_html( $current_data[ 'title' ] ?? $current_data[ 'label' ] ?? 'Unknown Tab' ); ?></h2>
        <?php if ( Helpers::user_can_edit() && isset( $current_data[ 'buttons' ] ) ) { ?>
            <?php foreach ( $current_data[ 'buttons' ] as $button ) { ?>
                <?php if ( isset( $button[ 'form' ] ) ) : ?>
                    <?php $disabled = isset( $button[ 'disabled' ] ) && $button[ 'disabled' ] ? 'disabled' : ''; ?>
                    <button id="header_btn_<?php echo esc_attr( $button[ 'id' ] ?? '' ); ?>" type="submit" form="<?php echo esc_attr( $button[ 'form' ] ); ?>" class="tab-button helpdocs-button" <?php echo isset( $button[ 'target' ] ) ? 'target="' . esc_attr( $button[ 'target' ] ) . '"' : ''; ?> <?php echo esc_attr( $disabled ); ?>>
                        <?php echo wp_kses_post( $button[ 'text' ] ?? __( 'Button', 'admin-help-docs' ) ); ?>
                    </button>
                <?php else : ?>
                    <a href="<?php echo esc_url( $button[ 'link' ] ); ?>" class="tab-button helpdocs-button" <?php echo isset( $button[ 'target' ] ) ? 'target="' . esc_attr( $button[ 'target' ] ) . '"' : ''; ?>><?php echo wp_kses_post( $button[ 'text' ] ?? __( 'Button', 'admin-help-docs' ) ); ?></a>
                <?php endif; ?>
            <?php } ?>
        <?php } ?>
        <?php do_action( 'helpdocs_subheader_left', $real_tab ); ?>
    </div>
    <div class="subheader-right">
        <?php do_action( 'helpdocs_subheader_right', $real_tab ); ?>
    </div>
</div>

<?php if ( isset( $_GET[ 'settings-updated' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
    <div id="message" class="updated">
        <p><strong><?php esc_html_e( 'Settings saved.', 'admin-help-docs' ) ?></strong></p>
    </div>
<?php } ?>