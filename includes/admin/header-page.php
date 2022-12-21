<?php 
/**
 * Plugin admin options page header (underneath tabs)
 */

// Get the active tab
$tab = helpdocs_get( 'tab' ) ?? 'topics';
?>
<br><br>
<h2 class="tab-header"><?php echo wp_kses_post( helpdocs_plugin_menu_items( $tab ) ); ?></h2>
<p><?php echo wp_kses_post( helpdocs_plugin_menu_items( $tab, true ) ); ?></p>
<hr />
<br>