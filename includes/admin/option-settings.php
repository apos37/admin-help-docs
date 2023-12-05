<style>
#dashicon-preview {
    display: inline-block;
    font-family: dashicons;
    font-size: 1.5rem;
    line-height: 1;
    height: 25px;
    vertical-align: middle;
}
.notice .button {
    margin: 0px 4px;
}
.notice-buttons {
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
        <p><?php _e( 'Are you absolutely sure you want to reset '.esc_html( $what ).'?', 'admin-help-docs' ); ?> <span class="notice-buttons"><a class="button button-secondary" href="<?php echo esc_url( $current_url ); ?>&reset=<?php echo esc_attr( $reset ); ?>&confirmed=true">Yes</a> <a class="button button-secondary" href="<?php echo esc_url( $current_url ); ?>">No</a></span></p>
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
            $reset_keys = HELPDOCS_GLOBAL_OPTIONS::$colors;

        } elseif ( $reset == 'all' ) {

            // Get all keys
            $reset_keys = HELPDOCS_GLOBAL_OPTIONS::$settings_general;
        }

        // Iter the options
        foreach ( $reset_keys as $reset_key ) {

            // Delete the option
            delete_option( HELPDOCS_GO_PF.$reset_key );
        }

        // Add a notice to confirm
        ?>
        <div class="notice notice-success is-dismissible">
        <p><?php _e( 'You have successfully reset '.esc_html( $what ).'. Take one last look at what you will be missing out on thanks to your bold decision. Refresh the page to see your new changes.', 'admin-help-docs' ); ?></p>
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

<?php if ( is_network_admin() ) { ?>
    <p>You are currently on the multisite network. Settings are only available on a per-site bases. Please navigate to the appropriate site to update settings.</p>

    <?php
    $current_domain = helpdocs_get_domain();

    // Get all of the subsites
    global $wpdb;
    $subsites = $wpdb->get_results( "SELECT blog_id, domain, path FROM $wpdb->blogs WHERE archived = '0' AND deleted = '0' AND spam = '0' ORDER BY blog_id" );

    // Iter the subsites
    if ( $subsites && !empty( $subsites ) ) {
        ?><ul><?php
        foreach( $subsites as $subsite ) {
            $subsite_id = $subsite->blog_id;
            $subsite_name = get_blog_details( $subsite_id )->blogname;
            $link = str_replace( $current_domain, $subsite->domain, $current_url );
            ?>
            <li><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_attr( $subsite_name ); ?></a> (ID: <?php echo absint( $subsite_id ); ?>)</li>
            <?php
        }
        ?></ul><?php
    }
    ?>

<?php } else { ?>

    <form method="post" action="options.php">
        <?php settings_fields( HELPDOCS_PF.'group_settings' ); ?>
        <?php do_settings_sections( HELPDOCS_PF.'group_settings' ); ?>
        <table class="form-table">

            <?php echo wp_kses( helpdocs_options_tr( 'admin_bar', 'Enable Admin Bar Menu Quick Link', 'checkbox', '' ), $allowed_html ); ?>

            <?php echo wp_kses( helpdocs_options_tr( 'dashboard_toc', 'Enable Dashboard TOC', 'checkbox', ' Adds a dashboard widget with a table of contents for the docs on the Main Documentation Page.' ), $allowed_html ); ?>

            <?php $di = 'dashicons-';
            $dashicons = [
                $di.'menu',
                $di.'admin-site',
                $di.'dashboard',
                $di.'admin-media',
                $di.'admin-page',
                $di.'admin-comments',
                $di.'admin-appearance',
                $di.'admin-plugins',
                $di.'admin-users',
                $di.'admin-tools',
                $di.'admin-settings',
                $di.'admin-network',
                $di.'admin-generic',
                $di.'admin-home',
                $di.'admin-collapse',
                $di.'filter',
                $di.'admin-customizer',
                $di.'admin-multisite',
                $di.'admin-links',
                $di.'format-links',
                $di.'admin-post',
                $di.'format-standard',
                $di.'format-image',
                $di.'format-gallery',
                $di.'format-audio',
                $di.'format-video',
                $di.'format-chat',
                $di.'format-status',
                $di.'format-aside',
                $di.'format-quote',
                $di.'welcome-write-blog',
                $di.'welcome-edit-page',
                $di.'welcome-add-page',
                $di.'welcome-view-site',
                $di.'welcome-widgets-menus',
                $di.'welcome-comments',
                $di.'welcome-learn-more',
                $di.'image-crop',
                $di.'image-rotate',
                $di.'image-rotate-left',
                $di.'image-rotate-right',
                $di.'image-flip-vertical',
                $di.'image-flip-horizontal',
                $di.'image-filter',
                $di.'undo',
                $di.'redo',
                $di.'editor-bold',
                $di.'editor-italic',
                $di.'editor-ul',
                $di.'editor-ol',
                $di.'editor-quote',
                $di.'editor-alignleft',
                $di.'editor-aligncenter',
                $di.'editor-alignright',
                $di.'editor-insertmore',
                $di.'editor-spellcheck',
                $di.'editor-distractionfree',
                $di.'editor-expand',
                $di.'editor-contract',
                $di.'editor-kitchensink',
                $di.'editor-underline',
                $di.'editor-justify',
                $di.'editor-textcolor',
                $di.'editor-paste-word',
                $di.'editor-paste-text',
                $di.'editor-removeformatting',
                $di.'editor-video',
                $di.'editor-customchar',
                $di.'editor-outdent',
                $di.'editor-indent',
                $di.'editor-help',
                $di.'editor-strikethrough',
                $di.'editor-unlink',
                $di.'editor-rtl',
                $di.'editor-break',
                $di.'editor-code',
                $di.'editor-paragraph',
                $di.'editor-table',
                $di.'align-left',
                $di.'align-right',
                $di.'align-center',
                $di.'align-none',
                $di.'lock',
                $di.'unlock',
                $di.'calendar',
                $di.'calendar-alt',
                $di.'visibility',
                $di.'hidden',
                $di.'post-status',
                $di.'edit',
                $di.'post-trash',
                $di.'trash',
                $di.'sticky',
                $di.'external',
                $di.'arrow-up',
                $di.'arrow-down',
                $di.'arrow-left',
                $di.'arrow-right',
                $di.'arrow-up-alt',
                $di.'arrow-down-alt',
                $di.'arrow-left-alt',
                $di.'arrow-right-alt',
                $di.'arrow-up-alt2',
                $di.'arrow-down-alt2',
                $di.'arrow-left-alt2',
                $di.'arrow-right-alt2',
                $di.'leftright',
                $di.'sort',
                $di.'randomize',
                $di.'list-view',
                $di.'excerpt-view',
                $di.'grid-view',
                $di.'hammer',
                $di.'art',
                $di.'migrate',
                $di.'performance',
                $di.'universal-access',
                $di.'universal-access-alt',
                $di.'tickets',
                $di.'nametag',
                $di.'clipboard',
                $di.'heart',
                $di.'megaphone',
                $di.'schedule',
                $di.'wordpress',
                $di.'wordpress-alt',
                $di.'pressthis',
                $di.'update',
                $di.'screenoptions',
                $di.'cart',
                $di.'feedback',
                $di.'cloud',
                $di.'translation',
                $di.'tag',
                $di.'category',
                $di.'archive',
                $di.'tagcloud',
                $di.'text',
                $di.'media-archive',
                $di.'media-audio',
                $di.'media-code',
                $di.'media-default',
                $di.'media-document',
                $di.'media-interactive',
                $di.'media-spreadsheet',
                $di.'media-text',
                $di.'media-video',
                $di.'playlist-audio',
                $di.'playlist-video',
                $di.'controls-play',
                $di.'controls-pause',
                $di.'controls-forward',
                $di.'controls-skipforward',
                $di.'controls-back',
                $di.'controls-skipback',
                $di.'controls-repeat',
                $di.'controls-volumeon',
                $di.'controls-volumeoff',
                $di.'yes',
                $di.'no',
                $di.'no-alt',
                $di.'plus',
                $di.'plus-alt',
                $di.'plus-alt2',
                $di.'minus',
                $di.'dismiss',
                $di.'marker',
                $di.'star-filled',
                $di.'star-half',
                $di.'star-empty',
                $di.'flag',
                $di.'info',
                $di.'warning',
                $di.'share',
                $di.'share1',
                $di.'share-alt',
                $di.'share-alt2',
                $di.'twitter',
                $di.'rss',
                $di.'email',
                $di.'email-alt',
                $di.'facebook',
                $di.'facebook-alt',
                $di.'networking',
                $di.'googleplus',
                $di.'location',
                $di.'location-alt',
                $di.'camera',
                $di.'images-alt',
                $di.'images-alt2',
                $di.'video-alt',
                $di.'video-alt2',
                $di.'video-alt3',
                $di.'vault',
                $di.'shield',
                $di.'shield-alt',
                $di.'sos',
                $di.'search',
                $di.'slides',
                $di.'analytics',
                $di.'chart-pie',
                $di.'chart-bar',
                $di.'chart-line',
                $di.'chart-area',
                $di.'groups',
                $di.'businessman',
                $di.'id',
                $di.'id-alt',
                $di.'products',
                $di.'awards',
                $di.'forms',
                $di.'testimonial',
                $di.'portfolio',
                $di.'book',
                $di.'book-alt',
                $di.'download',
                $di.'upload',
                $di.'backup',
                $di.'clock',
                $di.'lightbulb',
                $di.'microphone',
                $di.'desktop',
                $di.'tablet',
                $di.'smartphone',
                $di.'phone',
                $di.'smiley',
                $di.'index-card',
                $di.'carrot',
                $di.'building',
                $di.'store',
                $di.'album',
                $di.'palmtree',
                $di.'tickets-alt',
                $di.'money',
                $di.'thumbs-up',
                $di.'thumbs-down',
                $di.'layout',
                $di.'align-pull-left',
                $di.'align-pull-right',
                $di.'block-default',
                $di.'cloud-saved',
                $di.'cloud-upload',
                $di.'columns',
                $di.'cover-image',
                $di.'embed-audio',
                $di.'embed-generic',
                $di.'embed-photo',
                $di.'embed-post',
                $di.'embed-video',
                $di.'exit',
                $di.'html',
                $di.'info-outline',
                $di.'insert-after',
                $di.'insert-before',
                $di.'insert',
                $di.'remove',
                $di.'shortcode',
                $di.'table-col-after',
                $di.'table-col-before',
                $di.'table-col-delete',
                $di.'table-row-after',
                $di.'table-row-before',
                $di.'table-row-delete',
                $di.'saved',
                $di.'amazon',
                $di.'google',
                $di.'linkedin',
                $di.'pinterest',
                $di.'podio',
                $di.'reddit',
                $di.'spotify',
                $di.'twitch',
                $di.'whatsapp',
                $di.'xing',
                $di.'youtube',
                $di.'database-add',
                $di.'database-export',
                $di.'database-import',
                $di.'database-remove',
                $di.'database-view',
                $di.'database',
                $di.'bell',
                $di.'airplane',
                $di.'car',
                $di.'calculator',
                $di.'ames',
                $di.'printer',
                $di.'beer',
                $di.'coffee',
                $di.'drumstick',
                $di.'food',
                $di.'bank',
                $di.'hourglass',
                $di.'money-alt',
                $di.'open-folder',
                $di.'pdf',
                $di.'pets',
                $di.'privacy',
                $di.'superhero',
                $di.'superhero-alt',
                $di.'edit-page',
                $di.'fullscreen-alt',
                $di.'fullscreen-exit-alt'
            ];
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
                'meta_key'		    => HELPDOCS_GO_PF.'site_location',
                'meta_value'	    => base64_encode( 'main' ),
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
<?php } ?>

<div id="other-settings"><a href="<?php echo esc_url( $current_url ); ?>&reset=colors">Reset Colors to Default</a> | <a href="<?php echo esc_url( $current_url ); ?>&reset=all">Reset All Settings to Default</a> | <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settingsie' ) ); ?>">Copy Settings from Another Site</a></div>

<div id="save-reminder">Don't forget to save your changes!</div>