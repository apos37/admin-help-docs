<!-- Add CSS to this table only -->
<style>
.admin-large-table td {
    vertical-align: top;
}
code {
    padding: 0;
    margin: 0;
}
</style>

<?php include 'header-page.php'; ?>

<?php 
$hooks = [
    [ 
        'type' => 'Filter',
        'hook' => 'helpdocs_wpconfig_snippets',
        'desc' => 'Add, remove or modify a snippet from <a href="'.helpdocs_plugin_options_path( 'wpcnfg' ).'">WP-CONFIG</a>.<br><em><a href="https://developer.wordpress.org/apis/wp-config-php/" target="_blank">Find more snippets here.</a></em>',
    ],
    [
        'type' => 'Action',
        'hook' => 'helpdocs_on_update_post_meta',
        'desc' => 'Do something when you <a href="'.helpdocs_plugin_options_path( 'postmeta' ).'">update post meta</a>.'
    ]
];
?>

<p><strong>Where do I add these hooks?</strong></p>
<p>You can place them in your <code>functions.php</code> file, or if you feel uncomfortable doing so you can use the <a href="https://wordpress.org/plugins/code-snippets/" target="_blank">Code Snippets</a> <em>by Code Snippets Pro</em> plugin to add code safely.</p>

<br><br>
<div class="full_width_container">
    <table class="admin-large-table">
        <tr>
            <th style="width: 300px;">Description</th>
            <th style="width: auto;">Hook</th>
            <th style="width: auto;">Example Usage</th>
        </tr>
        <?php
        // Add the hooks
        foreach ( $hooks as $hook ) {

            // The short path to the file
            $filepath = HELPDOCS_PLUGIN_FILES_PATH . 'hooks/'.$hook[ 'hook' ].'.php';

            // Make sure it exists, and if so, get the full path
            if ( is_readable( rtrim( ABSPATH, '/' ) . $filepath ) ) {
                $file = rtrim( ABSPATH, '/' ) . $filepath;
            } elseif ( is_readable( dirname( ABSPATH ) . $filepath ) ) {
                $file = dirname( ABSPATH ) . $filepath;
            } else {
                $file = false;
            }

            // Add the snippet row
            if ( $file ) {
                ?>
                <tr>
                    <td><?php echo wp_kses_post( $hook[ 'desc' ] ); ?></td>
                    <td><code><strong><?php echo esc_attr( $hook[ 'hook' ] ); ?></strong></code><br><br><strong>TYPE &#8674;</strong> <?php echo esc_attr( $hook[ 'type' ] ); ?></td>
                    <td class="usage"><?php helpdocs_highlight_file2( $file, false ); ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
</div>