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
</style>

<?php include 'header-page.php'; ?>

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
?>

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
    <button class="button button-secondary submit" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-name="<?php echo esc_attr( $display_name ); ?>" data-email="<?php echo esc_attr( $email ); ?>">Send Feedback</button>
    <div id="feedback-sending">Sending</div>
    <div id="feedback-result"></div>
</div>


<br><br><br>
<h3>Planned Updates</h3>
<p>The following items are currently planned, not necessarily in order. If you would like to request a feature or encourage priority of one them, please do so on Discord at the link above.</p>
<ul>
    <li>Site-specific feeds: add a field underneath "Allow Public" to specify site addresses so only those sites will auto-feed the document</li>
    <li>Add the WordPress Updates page to site locations. The dashboard was initially removed to add a meta box instead of a notice, but it also removed the updates page in the process.</li>
    <li>Disable profile settings by default?</li>
    <li>Folders: adding folders for the main documentation page. So far I haven't needed to add so much documentation on this page, but I can see how some people might want it.</li>
    <li>Themes: add a collection of themes that you can choose from. I just don't have any great ideas yet, so if you have an awesome look for your documents, please submit them.</li>
    <li>Scheduling: the ability to schedule a document to be displayed during a specified date range. This will be useful since many of the documents used are actually notices rather than permanent fixtures.</li>
    <li>More site locations: possibly add a fixed area at the top or bottom of all screens, or a floating help button on the bottom right?</li>
    <li>If Dev Debug Tools is also installed, add visibility option for devs only so devs can have their own docs separate from everyone else.</li>
</ul>

<br><br>
<h3>Try My Other Plugin</h3>
<?php echo wp_kses_post( helpdocs_plugin_card( 'dev-debug-tools' ) ); ?>