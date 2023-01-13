<?php 
/**
 * Plugin admin options page header (underneath tabs)
 */

// Get the active tab
$tab = helpdocs_get( 'tab' ) ?? 'topics';

// Are we on the documentation tab?
if ( $tab != 'documentation' ) {
    $incl_hr = '<hr class="tab-header-hr"><br>';
} else {
    $incl_hr = '';
}
?>
<br>
<h2 class="tab-header"><?php echo wp_kses_post( helpdocs_plugin_menu_items( $tab ) ); ?></h2>
<?php echo wp_kses_post( helpdocs_plugin_menu_items( $tab, true ) ); ?>
<?php echo wp_kses_post( $incl_hr ); ?>