<?php 
// Get the colors
$HELPDOCS_COLORS = new HELPDOCS_COLORS();
$color_bg = $HELPDOCS_COLORS->get( 'bg' );
$color_cl = $HELPDOCS_COLORS->get( 'cl' );
?>

<style>
a.button {
    background-color: <?php echo esc_attr( $color_bg ); ?> !important;
    filter: brightness(95%);
    color: <?php echo esc_attr( $color_cl ); ?> !important;
}
</style>

<?php include 'header-page.php'; ?>

<br><br>
<h3>Try Our Other Plugin</h3>
<?php echo wp_kses_post( helpdocs_plugin_card( 'dev-debug-tools' ) ); ?>

<br><br>
<h3>Plugin Support</h3>
<br><img class="admin_helpbox_title" src="<?php echo esc_url( HELPDOCS_PLUGIN_IMG_PATH ); ?>discord.png" width="auto" height="100">
<p>If you need assistance with this plugin or have suggestions for improving it, please join the Discord server below.</p>
<?php echo sprintf( __( '<a class="button button-primary" href="%s" target="_blank">Join Our Support Server »</a><br>', 'admin-help-docs' ), 'https://discord.gg/VeMTXRVkm5' ); ?>
<br>
<p>Or if you would rather get support on WordPress.org, you can do so here:</p>
<?php echo sprintf( __( '<a class="button button-primary" href="%s" target="_blank">WordPress.org Plugin Support Page »</a><br>', 'admin-help-docs' ), 'https://wordpress.org/support/plugin/admin-help-docs/' ); ?>

<br><br><br>
<h3>Like This Plugin?</h3>
<p>Please rate and review this plugin if you find it helpful. If you would give it fewer than 5 stars, please let me know how I can improve it.</p>
<?php echo sprintf( __( '<a class="button button-primary" href="%s" target="_blank">Rate and Review on WordPress.org »</a><br>', 'admin-help-docs' ), 'https://wordpress.org/support/plugin/admin-help-docs/reviews/' ); ?>

<?php
$buy_me_coffee = '<br><br><br><h3>'. __( 'Support This Plugin', 'admin-help-docs' ).'</h3>
<p>At this time, there are no premium add-ons so the only source of income I have to maintain this plugin is from donations.</p>';
$buy_me_coffee .= sprintf( __( '<a class="button button-primary" href="%s" target="_blank">Buy Me Coffee :)</a><br>', 'admin-help-docs' ), 'https://paypal.com/donate/?business=3XHJUEHGTMK3N' );
$coffee_filter = apply_filters( 'helpdocs_coffee', $buy_me_coffee );
$coffee_filter = false; /// REMOVE AFTER DEVELOPMENT
if ( $coffee_filter ) {
    echo wp_kses_post( $buy_me_coffee );
}