<style>
#dashicon-preview {
    display: inline-block;
    font-family: dashicons;
    font-size: 1.5rem;
    line-height: 1;
    height: 25px;
    vertical-align: middle;
}
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
</style>

<?php 
include 'header-page.php';
$allowed_html = helpdocs_wp_kses_allowed_html(); 

// Build the current url
$page = helpdocs_plugin_options_short_path();
$tab = 'settings';
$current_url = helpdocs_plugin_options_path( $tab );

// Are we resetting options?
if ( $reset = helpdocs_get( 'reset' ) ) {

    // Check if confirmed
    if ( !helpdocs_get( 'confirmed', '==', 'true' ) ) {

        // Remove the query string
        helpdocs_remove_qs_without_refresh( 'reset' );

        // Get the suffix
        if ( $reset == 'colors' ) {
            $what = 'all of the colors you have set for your documents';
        } elseif ( $reset == 'all' ) {
            $what = 'all of the plugin settings below';
        } else {
            return 'Nice try buddy!';
        }

        // Add a notice to confirm
        ?>
        <div class="notice notice-warning is-dismissible">
        <p><?php /* translators: 1: What is being reset */
        echo esc_html( sprintf( __( 'Are you absolutely sure you want to reset %s?', 'admin-help-docs' ), $what ) ); ?> <span class="notice-buttons"><a class="button button-secondary" href="<?php echo esc_url( $current_url ); ?>&reset=<?php echo esc_attr( $reset ); ?>&confirmed=true">Yes</a> <a class="button button-secondary" href="<?php echo esc_url( $current_url ); ?>">No</a></span></p>
        </div>
        <?php

    } else {

        // Get the suffix
        if ( $reset == 'colors' ) {
            $what = 'all of the colors';
        } elseif ( $reset == 'all' ) {
            $what = 'all of the plugin settings';
        } else {
            return 'Nice try buddy!';
        }

        // Remove the query string
        helpdocs_remove_qs_without_refresh( [ 'reset', 'confirmed' ] );

        // Get the global options
        $HELPDOCS_GLOBAL_OPTIONS = new HELPDOCS_GLOBAL_OPTIONS();

        // If colors
        if ( $reset == 'colors' ) {

            // Get the color keys
            $reset_keys = (new HELPDOCS_GLOBAL_OPTIONS)->colors;

        } elseif ( $reset == 'all' ) {

            // Get all keys
            $reset_keys = (new HELPDOCS_GLOBAL_OPTIONS)->settings_general;
        }

        // Iter the options
        foreach ( $reset_keys as $reset_key ) {

            // Delete the option
            delete_option( HELPDOCS_GO_PF.$reset_key );
        }

        // Add a notice to confirm
        ?>
        <div class="notice notice-success is-dismissible">
        <p><?php /* translators: 1: What was reset */
        echo esc_html( sprintf( __( 'You have successfully reset %s. Take one last look at what you will be missing out on thanks to your bold decision. Refresh the page to see your new changes.', 'admin-help-docs' ), $what ) ); ?></p>
        </div>
        <?php
    }
}

// Update json
if ( helpdocs_get( 'settings-updated', '==', 'true' ) ) {
    helpdocs_create_json_from_settings();
}

// Get the colors
$HELPDOCS_COLORS = new HELPDOCS_COLORS();
$color_ac = $HELPDOCS_COLORS->get( 'ac' );
$color_bg = $HELPDOCS_COLORS->get( 'bg' );
$color_ti = $HELPDOCS_COLORS->get( 'ti' );
$color_fg = $HELPDOCS_COLORS->get( 'fg' );
$color_cl = $HELPDOCS_COLORS->get( 'cl' );
?>

<form method="post" action="options.php">
    <?php settings_fields( HELPDOCS_PF.'group_settings' ); ?>
    <?php do_settings_sections( HELPDOCS_PF.'group_settings' ); ?>
    <table class="form-table">

        <?php echo wp_kses( helpdocs_options_tr( 'admin_bar', 'Enable Admin Bar Menu Quick Link', 'checkbox', '' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'dashboard_toc', 'Enable Dashboard TOC', 'checkbox', ' Adds a dashboard widget with a table of contents for the docs on the Main Documentation Page.' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'gutenberg_editor', 'Use Gutenberg Editor', 'checkbox', ' Adds support for the Gutenberg editor for the documentation. Default is the classic editor.' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'enqueue_frontend_styles', 'Use Frontend Styles', 'checkbox', ' Adds support for your frontend styles in the backend.' ), $allowed_html ); ?>

        <?php 
        $dashicons = [
            'menu', 'admin-site', 'dashboard', 'admin-media', 'admin-page', 'admin-comments', 'admin-appearance', 'admin-plugins', 'admin-users', 'admin-tools', 'admin-settings',
            'admin-network', 'admin-generic', 'admin-home', 'admin-collapse', 'filter', 'admin-customizer', 'admin-multisite', 'admin-links', 'format-links', 'admin-post',
            'format-standard', 'format-image', 'format-gallery', 'format-audio', 'format-video', 'format-chat', 'format-status', 'format-aside', 'format-quote', 'welcome-write-blog',
            'welcome-edit-page', 'welcome-add-page', 'welcome-view-site', 'welcome-widgets-menus', 'welcome-comments', 'welcome-learn-more', 'image-crop', 'image-rotate', 'image-rotate-left',
            'image-rotate-right', 'image-flip-vertical', 'image-flip-horizontal', 'image-filter', 'undo', 'redo', 'editor-bold', 'editor-italic', 'editor-ul', 'editor-ol', 'editor-quote',
            'editor-alignleft', 'editor-aligncenter', 'editor-alignright', 'editor-insertmore', 'editor-spellcheck', 'editor-distractionfree', 'editor-expand', 'editor-contract',
            'editor-kitchensink', 'editor-underline', 'editor-justify', 'editor-textcolor', 'editor-paste-word', 'editor-paste-text', 'editor-removeformatting', 'editor-video',
            'editor-customchar', 'editor-outdent', 'editor-indent', 'editor-help', 'editor-strikethrough', 'editor-unlink', 'editor-rtl', 'editor-break', 'editor-code', 'editor-paragraph',
            'editor-table', 'align-left', 'align-right', 'align-center', 'align-none', 'lock', 'unlock', 'calendar', 'calendar-alt', 'visibility', 'hidden', 'post-status', 'edit',
            'post-trash', 'trash', 'sticky', 'external', 'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up-alt', 'arrow-down-alt', 'arrow-left-alt', 'arrow-right-alt',
            'arrow-up-alt2', 'arrow-down-alt2', 'arrow-left-alt2', 'arrow-right-alt2', 'leftright', 'sort', 'randomize', 'list-view', 'excerpt-view', 'grid-view', 'hammer', 'art', 'migrate',
            'performance', 'universal-access', 'universal-access-alt', 'tickets', 'nametag', 'clipboard', 'heart', 'megaphone', 'schedule', 'wordpress', 'wordpress-alt', 'pressthis', 'update',
            'screenoptions', 'cart', 'feedback', 'cloud', 'translation', 'tag', 'category', 'archive', 'tagcloud', 'text', 'media-archive', 'media-audio', 'media-code', 'media-default',
            'media-document', 'media-interactive', 'media-spreadsheet', 'media-text', 'media-video', 'playlist-audio', 'playlist-video', 'controls-play', 'controls-pause', 'controls-forward',
            'controls-skipforward', 'controls-back', 'controls-skipback', 'controls-repeat', 'controls-volumeon', 'controls-volumeoff', 'yes', 'no', 'no-alt', 'plus', 'plus-alt',
            'plus-alt2', 'minus', 'dismiss', 'marker', 'star-filled', 'star-half', 'star-empty', 'flag', 'info', 'warning', 'share', 'share1', 'share-alt', 'share-alt2', 'twitter', 'rss',
            'email', 'email-alt', 'facebook', 'facebook-alt', 'networking', 'googleplus', 'location', 'location-alt', 'camera', 'images-alt', 'images-alt2', 'video-alt', 'video-alt2', 'video-alt3',
            'vault', 'shield', 'shield-alt', 'sos', 'search', 'slides', 'analytics', 'chart-pie', 'chart-bar', 'chart-line', 'chart-area', 'groups', 'businessman', 'id', 'id-alt', 'products',
            'awards', 'forms', 'testimonial', 'portfolio', 'book', 'book-alt', 'download', 'upload', 'backup', 'clock', 'lightbulb', 'microphone', 'desktop', 'tablet', 'smartphone', 'phone',
            'smiley', 'index-card', 'carrot', 'building', 'store', 'album', 'palmtree', 'tickets-alt', 'money', 'thumbs-up', 'thumbs-down', 'layout', 'align-pull-left', 'align-pull-right',
            'block-default', 'cloud-saved', 'cloud-upload', 'columns', 'cover-image', 'embed-audio', 'embed-generic', 'embed-photo', 'embed-post', 'embed-video', 'exit', 'html', 'info-outline',
            'insert-after', 'insert-before', 'insert', 'remove', 'shortcode', 'table-col-after', 'table-col-before', 'table-col-delete', 'table-row-after', 'table-row-before', 'table-row-delete',
            'saved', 'amazon', 'google', 'linkedin', 'pinterest', 'podio', 'reddit', 'spotify', 'twitch', 'whatsapp', 'xing', 'youtube', 'database-add', 'database-export', 'database-import',
            'database-remove', 'database-view', 'database', 'bell', 'airplane', 'car', 'calculator', 'ames', 'printer', 'beer', 'coffee', 'drumstick', 'food', 'bank', 'hourglass', 'money-alt',
            'open-folder', 'pdf', 'pets', 'privacy', 'superhero', 'superhero-alt', 'edit-page', 'fullscreen-alt', 'fullscreen-exit-alt'
        ];

        foreach ( $dashicons as $key => $icon ) {
            $dashicons[ $key ] = 'dashicons-' . $icon;
        }

        sort( $dashicons );
        $icons = [
            'options' => $dashicons,
            'width'   => '20rem',
            'default' => $di.'editor-help'
        ]; 
        $current_dashicon = get_option( HELPDOCS_GO_PF.'dashicon', 'dashicons-editor-help' );
        $current_dashicon = str_replace( 'dashicons-', '', $current_dashicon );
        $dashicons_url = 'https://developer.wordpress.org/resource/dashicons/'; ?>
        <?php echo wp_kses( helpdocs_options_tr( 'dashicon', 'Menu Icon', 'select', '<div id="dashicon-preview" class="dashicons-'.$current_dashicon.'"></div><br><a id="view-dashicons-link" href="'.$dashicons_url.'#'.$current_dashicon.'" target="_blank">View Dashicons</a>', $icons ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'logo', 'Logo<br><span style="font-style: italic; font-weight: normal;">(No Live Preview)</span>', 'text', '<br>Preferred size: 100x100 pixels. Accepted formats: jpg | jpeg | png | webp ', [ 'default' => HELPDOCS_PLUGIN_IMG_PATH.'logo.png', 'pattern' => '^https?:\/\/.+\.(jpg|jpeg|png|webp)$' ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'page_title', 'Page Title', 'text', '', [ 'default' => HELPDOCS_NAME, 'width' => '20rem' ] ), $allowed_html ); ?>

        <?php if ( is_multisite() ) { ?>
            <?php echo wp_kses( helpdocs_options_tr( 'multisite_sfx', 'Multisite Title Suffix', 'text', '', [ 'default' => trim( helpdocs_multisite_suffix() ), 'width' => '20rem' ] ), $allowed_html ); ?>
        <?php } ?>

        <?php echo wp_kses( helpdocs_options_tr( 'hide_version', 'Hide Version Number', 'checkbox', '' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'menu_title', 'Menu Title', 'text', '', [ 'default' => 'Help Docs', 'width' => '20rem' ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'menu_position', 'Menu Position<br><span style="font-style: italic; font-weight: normal;">(No Live Preview)</span>', 'number', '<br>1 = Above Dashboard, 2 = Under Dashboard, 999 = Bottom, etc.', [ 'width' => '5rem', 'default' => 2 ] ), $allowed_html ); ?>

        <?php $footer_text = sprintf(
            /* translators: %s: https://wordpress.org/ */
            __( 'Thank you for creating with <a href="%s">WordPress</a>.' ),
            'https://wordpress.org/'
        ); 
        if ( get_option( HELPDOCS_GO_PF.'menu_title' ) && get_option( HELPDOCS_GO_PF.'menu_title' ) != '' ) {
            $menu_title = get_option( HELPDOCS_GO_PF.'menu_title' );
        } else {
            $menu_title = 'Help Docs';
        }
        ob_start();
        $left_footer_default = apply_filters( 'admin_footer_text', '<span id="footer-thankyou">' . $footer_text . '</span>' );
        ob_clean();
        ?>
        <?php echo wp_kses( helpdocs_options_tr( 'footer_left', 'Left Footer Text', 'text', '<br>Example: <em>"For help, see the <a href="/'.HELPDOCS_ADMIN_URL.'/admin.php?page='.HELPDOCS_TEXTDOMAIN.'%2Fincludes%2Fadmin%2Foptions.php&tab=topics">'.$menu_title.'</a></em>"', [ 'default' => $left_footer_default ] ), $allowed_html ); ?>

        <?php $default_right_footer_text = sprintf(
            __( 'Version ' ).'{version}',
            'https://wordpress.org/'
        ); 
        ?>
        <?php echo wp_kses( helpdocs_options_tr( 'footer_right', 'Right Footer Text', 'text', '<br>Use <code>{version}</code> to display the current WordPress version', [ 'default' => $default_right_footer_text ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'user_view_cap', 'Capability Required to View Docs', 'text', '<br>Use <code>manage_options</code> for admins only. <a href="https://wordpress.org/documentation/article/roles-and-capabilities/" target="_blank">View a list of capabilities</a>', [ 'default' => 'manage_options' ] ), $allowed_html ); ?>

        <?php 
        // Get the role details
        $roles = get_editable_roles();

        // Store the roles here
        $role_options = [];

        // Iter the roles
        foreach ( $roles as $key => $role ) {

            // Do not include admin
            if ( $key != 'administrator' ) {

                // Add the option's label and value
                $role_options[] = [
                    'label' => $role[ 'name' ],
                    'value' => $key
                ];
            }
        }

        // Set the args
        $edit_roles_args = [
            'options' => $role_options,
            'class'   => HELPDOCS_GO_PF.'role_checkbox'
        ]; ?>
        <?php echo wp_kses( helpdocs_options_tr( 'edit_roles', 'Additional Roles That Can Add/Edit Help Sections', 'checkboxes', '', $edit_roles_args ), $allowed_html ); ?>

        <?php $api_choices = [
            'options' => [
                [ 
                    'label' => 'No',
                    'value' => 'no' 
                ],
                [ 
                    'label' => 'Yes',
                    'value' => 'yes' 
                ]
            ],
            'width' => '10rem',
        ];
        
        $api_url = help_get_api_path();
        ?>
        <?php echo wp_kses( helpdocs_options_tr( 'api', 'Allow Public by Default', 'select', '<br>Allowing documents to be public adds them to a <a href="'.$api_url.'" target="_blank">publicly accessible custom rest api end-point</a>, which can then be pulled in from other sites you manage.', $api_choices ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'color_ac', 'Accent Color', 'color', null, [ 'default' => $color_ac ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'color_bg', 'Background Color', 'color', null, [ 'default' => $color_bg ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'color_ti', 'Document Title Color', 'color', null, [ 'default' => $color_ti ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'color_fg', 'Text Color', 'color', null, [ 'default' => $color_fg ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'color_cl', 'Link Color', 'color', null, [ 'default' => $color_cl ] ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'curly_quotes', 'Disable Curly Quotes', 'checkbox', 'WP automatically converts straight quotes (") to curly quotes (”), which makes sharing code difficult.' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'auto_htoc', 'Auto-Generate TOC from Headings', 'checkbox', 'Automatically generate a table of contents from headings (H2–H6) on each documentation page.' ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'user_prefs', 'Enable User Preferences', 'checkbox', 'Adds options to user profiles for resetting preferences related to which columns are hidden in admin list tables, which meta boxes are hidden, and where meta boxes are positioned on edit pages.' ), $allowed_html ); ?>

        <?php if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) { ?>
            <?php echo wp_kses( helpdocs_options_tr( 'gf_merge_tags', 'Add Missing Gravity Form User Merge Tags', 'textarea', '<br>You can add additional user merge tags to Gravity Form field options, notifications, and confirmations.<br>Separate by commas using the following format: <strong>Label (user_meta_key)</strong>', [ 'default' => 'User First Name (first_name), User Last Name (last_name), User Date Registered (user_registered)', 'rows' => '6', 'cols' => '100' ] ), $allowed_html ); ?>
        <?php } ?>

        <?php 
        // Get the docs
        $default_doc_args = [
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'post_type'         => 'help-docs',
            'meta_key'		    => HELPDOCS_GO_PF.'site_location', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_value'	    => base64_encode( 'main' ),        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            'meta_compare'	    => '=',
            'orderby'           => 'post_title',
            'order'             => 'ASC'
        ];
        $docs = get_posts( $default_doc_args );
        $imports = helpdocs_get_imports( $default_doc_args );
        if ( !empty( $imports ) ) {
            $docs = array_merge( $docs, $imports );
        }

        // Store the choices here
        $main_doc_choices = [
            'options' => [
                [
                    'label' => '-- Select a Doc --',
                    'value' => '' 
                ]
            ],
            'width' => '10rem',
        ];
        if ( !empty( $docs ) ) {
            foreach ( $docs as $doc ) {
                $main_doc_choices[ 'options' ][] = [
                    'label' => $doc->post_title,
                    'value' => $doc->ID 
                ];
            }
            $default_doc_desc = 'You can select a default document to load on the <a href="'.helpdocs_plugin_options_path( 'documentation' ).'">main documentation page</a>. Otherwise it will load the first doc on the list.';
        } else {
            $default_doc_desc = 'Once you have added documents to the <a href="'.helpdocs_plugin_options_path( 'documentation' ).'">main documentation page</a>, you can select a default to load. Otherwise it will load the first doc on the list.';
        }
        ?>
        <?php echo wp_kses( helpdocs_options_tr( 'default_doc', 'Default Document on Main Docs Page', 'select', '<br>'.$default_doc_desc, $main_doc_choices ), $allowed_html ); ?>

        <?php echo wp_kses( helpdocs_options_tr( 'hide_doc_meta', 'Hide Document Meta on Main Docs Page', 'checkbox', 'Includes created and last modified dates and authors.' ), $allowed_html ); ?>

    </table>
    
    <?php submit_button(); ?>
</form>

<div id="other-settings"><a href="<?php echo esc_url( $current_url ); ?>&reset=colors">Reset Colors to Default</a> | <a href="<?php echo esc_url( $current_url ); ?>&reset=all">Reset All Settings to Default</a> | <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settingsie' ) ); ?>">Copy Settings from Another Site</a></div>

<div id="save-reminder">Don't forget to save your changes!</div>