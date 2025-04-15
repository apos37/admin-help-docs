<?php 
// Get the colors
$HELPDOCS_COLORS = new HELPDOCS_COLORS();
$color_bg = $HELPDOCS_COLORS->get( 'bg' );
$color_cl = $HELPDOCS_COLORS->get( 'cl' );
?>

<style>
.button {
    background-color: <?php echo esc_attr( $color_bg ); ?> !important;
    filter: brightness(95%);
    color: <?php echo esc_attr( $color_cl ); ?> !important;
}
ul {
    list-style: square;
    padding: revert;
    padding-top: 10px;
    padding-bottom: 5px;
}
ul li {
    padding-inline-start: 1ch;
}
#feedback-message {
    margin-bottom: 10px;
}
#feedback-sending {
    line-height: 2.25;
    font-style: italic;
    margin-left: 10px;
    display: none;
}
#feedback-sending:after {
    display: inline-block;
    animation: dotty steps(1,end) 1s infinite;
    content: '';
}
@keyframes dotty {
    0%   { content: ''; }
    25%  { content: '.'; }
    50%  { content: '..'; }
    75%  { content: '...'; }
    100% { content: ''; }
}
#feedback-result {
    color: white;
    font-weight: 500;
    width: fit-content;
    border-radius: 4px;
    padding: 6px 10px;
}
#feedback-result.success {
    background-color: green;
    display: inline-block;
    margin-left: 10px;
}
#feedback-result.fail {
    background-color: red;
    margin-top: 10px;
}
#the-list {
    display: flex;
    flex-flow: wrap;
}
.plugin-card {
    display: flex;
    flex-direction: column;
    margin-left: 0 !important;
}
.plugin-card .plugin-card-top {
    flex: 1;
}
.plugin-card .plugin-card-bottom {
    margin-top: auto;
}
.plugin-card .ws_stars {
    display: inline-block;
}
.php-incompatible {
    padding: 12px 20px;
    background-color: #D1231B;
    color: #FFFFFF;
    border-top: 1px solid #dcdcde;
    overflow: hidden;
}
#wpbody-content .plugin-card .plugin-action-buttons a.install-now[aria-disabled="true"] {
    color: #CBB8AD !important;
    border-color: #CBB8AD !important;
}
.plugin-action-buttons {
    list-style: none !important;   
}
</style>

<?php include 'header-page.php'; ?>

<br><br>
<h3>Plugin Support</h3>
<br><img class="admin_helpbox_title" src="<?php echo esc_url( HELPDOCS_PLUGIN_IMG_PATH ); ?>discord.png" width="auto" height="100">
<p>If you need assistance with this plugin or have suggestions for improving it, please join the Discord server below.</p>
<?php /* translators: 1: Text for the button (default: Join Our Support Server) */
echo '<a class="button button-primary" href="'.esc_url( HELPDOCS_DISCORD_SUPPORT_URL ).'" target="_blank">'.esc_html( __( 'Join Our Support Server', 'admin-help-docs' ) ).' »</a><br>'; ?>

<br>
<p>Or if you would rather get support on WordPress.org, you can do so here:</p>
<?php /* translators: 1: Text for the button (default: WordPress.org Plugin Support Page) */
echo '<a class="button button-primary" href="https://wordpress.org/support/plugin/admin-help-docs/" target="_blank">'.esc_html( __( 'WordPress.org Plugin Support Page', 'admin-help-docs' ) ).' »</a><br>'; ?>

<br><br><br>
<h3>Like This Plugin?</h3>
<p>Please rate and review this plugin if you find it helpful. If you would give it fewer than 5 stars, please let me know how I can improve it.</p>
<?php /* translators: 1: Text for the button (default: Rate and Review on WordPress.org) */
echo '<a class="button button-primary" href="https://wordpress.org/support/plugin/admin-help-docs/reviews/" target="_blank">'.esc_html( __( 'Rate and Review on WordPress.org', 'admin-help-docs' ) ).' »</a><br>'; ?>

<?php if ( helpdocs_get_domain() != 'playground.wordpress.net' ) { ?>
    <br><br><br>
    <h3>How Can We Improve?</h3>
    <div id="feedback-form">
        <div class="form-group">
            <label for="message" style="display: block;">If there was one thing you would change about this plugin, what would it be?</label> 
            <br><textarea id="feedback-message" name="message" class="form-control input-message" rows="6" style="width: 600px;" placeholder="Your feedback..."></textarea><br>
        </div>
        <?php 
        $nonce = wp_create_nonce( HELPDOCS_GO_PF.'feedback' );
        $user = get_userdata( get_current_user_id() ); 
        $display_name = $user->display_name; 
        $email = $user->user_email; 
        ?>
        <button class="button button-secondary submit" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-name="<?php echo esc_attr( $display_name ); ?>" data-email="<?php echo esc_attr( $email ); ?>" disabled>Send Feedback</button>
        <div id="feedback-sending">Sending</div>
        <div id="feedback-result"></div>
    </div>
<?php } ?>

<?php if ( helpdocs_get_domain() != 'playground.wordpress.net' ) { ?>
    <br><br>
    <h2><?php echo esc_html__( 'Try My Other Plugins', 'admin-help-docs' ); ?></h2>
    <div class="wp-list-table widefat plugin-install">
        <div id="the-list">
            <?php helpdocs_plugin_card( 'broken-link-notifier' ); ?>
            <?php helpdocs_plugin_card( 'eri-file-library' ); ?>
            <?php helpdocs_plugin_card( 'clear-cache-everywhere' ); ?>
            <?php helpdocs_plugin_card( 'dev-debug-tools' ); ?>
            <?php if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) { ?>
                <?php helpdocs_plugin_card( 'gf-tools' ); ?>
                <?php helpdocs_plugin_card( 'gf-discord' ); ?>
                <?php helpdocs_plugin_card( 'gf-msteams' ); ?>
                <?php helpdocs_plugin_card( 'gravity-zwr' ); ?>
            <?php } ?>
        </div>
    </div>
<?php } ?>