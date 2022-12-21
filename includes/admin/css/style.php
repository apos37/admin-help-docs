<?php
/**
 * CSS for All of the Plugin Settings Pages
 */
// Check if we are on options pages
global $current_screen;
if ( !isset( $current_screen->id ) ) {
    return;
}

// Get the options page slug
$options_page = 'toplevel_page_'.HELPDOCS_TEXTDOMAIN;

// Allow for multisite
if ( is_network_admin() ) {
    $options_page .= '-network';
}

// Are we on the options page?
if ( ( $current_screen->id == $options_page ) || ( $current_screen->post_type == 'help-docs' ) ) {

    // Set default colors here
    $bg_primary             = '#FBFBFB'; // Background primary
    $bg_secondary           = '#FFFFFF'; // Background secondary
    $bg_secondary_hover     = 'inherit'; // Background secondary hover

    $text_primary           = 'inherit'; // Text primary
    $text_secondary         = 'inherit'; // Text secondary
    $text_secondary_hover   = 'inherit'; // Text secondary hover
    $links                  = '#2F76DB'; // Links

    $bg_accent              = '#2F76DB'; // Accent color background
    $text_accent            = '#FBFBFB'; // Accent color text
    $text_accent_hover      = 'inherit'; // Accent color text hover

    $bg_warnings            = 'inherit'; // Warnings background
    $text_warnings          = 'inherit'; // Warnings text

    $borders_main           = 'inherit'; // Form field and table borders
    ?>
    <style>

    /* ---------------------------------------------
                    ALL OPTION PAGES
    --------------------------------------------- */

    /* Header */
    .admin-title-cont {
        vertical-align: middle;
    }
    .admin-title-cont img {
        margin-right: 10px;
        float: left;
    }
    .admin-title-cont h1 {
        font-size: 1.73rem; 
        display: inline-block;
        padding: 0;
    }
    .wrap.<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> {
        margin: 20px 0 0 2px !important;
    }

    <?php if ( $current_screen->post_type == 'help-docs' ) { ?>

        /* ---------------------------------------------
                       HELP DOCUMENTATION
        --------------------------------------------- */

        .wrap.<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> {
            margin-left: 4px !important;
            background: <?php echo esc_attr( $bg_primary ); ?> !important;
        }
        .wp-heading-inline {
            font-size: 1.3em !important;
            font-weight: 600 !important;
        }
        .wp-header-end {
            visibility: visible;
            margin: 8px 0 10px 0;
            border-top: 1px solid #ccc !important;
            border-bottom: 0px !important;
        }

    <?php } else { ?>
        
        /* ---------------------------------------------
                       OTHER OPTION PAGES
        --------------------------------------------- */

        /* Headers */
        h2, 
        h3,
        .wrap h2,
        .wrap h3 {
            margin-top: 0 !important;
            border-top: 0 !important;
            padding-top: 0 !important;
        }
        .wrap {
            padding: 0 !important;
        }

        /* Main backgrounds */
        html,
        body,
        #wpwrap, 
        #wpcontent,
        #wpbody,
        #wpbody-content,
        .wrap {
            background: <?php echo esc_attr( $bg_primary ); ?> !important;
        }

        /* HR */
        .tab-content hr {
            border-top: 1px solid #ccc !important;
            border-bottom: 0px !important;
        }

        /* Containers */
        .full_width_container,
        .half_width_container,
        .snippet_container {
            background-color: <?php echo esc_attr( $bg_secondary ); ?>;
            padding: 15px;
            border-radius: 4px;
            height: auto;
        }
        .full_width_container {
            width: initial;
        }
        .half_width_container {
            width: 50%;
        }
        .snippet_container {
            width: initial;
        }

        /* Tables */
        .admin-large-table {
            width: 100%;
        }
        .admin-large-table {
            border-collapse: collapse;
        }
        .admin-large-table,
        .admin-large-table th,
        .admin-large-table td {
            border: 1px solid <?php echo esc_attr( $borders_main ); ?>;
        }
        .admin-large-table th,
        .admin-large-table td {
            color: <?php echo esc_attr( $text_primary ); ?> !important;
            padding: 10px;
        }
        .admin-large-table td {
            word-break:break-all;
        }
        .admin-large-table tr:nth-child(even) {
            background: <?php echo esc_attr( $bg_primary ); ?> !important;
        }
        table.alternate-row tr:nth-child(even) {
            background: <?php echo esc_attr( $bg_primary ); ?> !important;
        }
        .form-table tr td:last-child {
            padding-right: 0;
        }
        .admin-large-table pre {
            word-break: break-word;
            white-space: pre-wrap;
        }

        /* Notices */
        .notice {
            /* color: #000000; */
            font-weight: 500;
        }

        /* Hide Screen Options */
        #screen-meta,
        #screen-meta-links {
            display: none !important;
        }

        /* Click to copy */
        .click-to-copy {
            background: transparent;
            color: <?php echo esc_attr( $links ); ?>;
            padding: 0;
            border-radius: 0;
        }

        /* ---------------------------------------------
                            FORMS
        --------------------------------------------- */

        /* Checkboxes and Radios */
        input[type="checkbox"],
        input[type="radio"] {
            background-color: <?php echo esc_attr( $bg_secondary ); ?>;
            border: 1px solid <?php echo esc_attr( $bg_accent ); ?>;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            vertical-align: middle;
            -webkit-appearance: none;
            outline: none;
            cursor: pointer;
            transition: all 1s ease;
        }
        input[type="checkbox"]:checked:before {
            color: <?php echo esc_attr( $text_accent ); ?>;
            content: '\2713';
            margin: 15px 3px !important;
            font-size: 16px;
            font-weight: bold;
        }
        input[type="radio"]:checked:before {
            color: <?php echo esc_attr( $text_accent ); ?>;
            background-color: <?php echo esc_attr( $text_accent ); ?>;
            margin: 4px 4px !important;
            width: 20px;
            height: 20px;
        }
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background: <?php echo esc_attr( $bg_accent ); ?>
        }
        .gfield_radio div,
        .update_choice {
            height: 30px;
            margin-bottom: 2px;
        }
        .checkbox_cont {
            display: block;
            margin-bottom: 10px;
        }

        /* Input fields */
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php input[type=text],
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php input[type=number],
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php textarea,
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php select {
            background-color: <?php echo esc_attr( $bg_secondary ); ?> !important;
            color: <?php echo esc_attr( $text_primary ); ?> !important;
            padding: 8px 12px !important;
            width: 43.75rem;
            max-width: 43.75rem;
            min-height: 2.85rem !important;
            vertical-align: revert;
        }
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php textarea {
            width: 100%;
            height: 20rem;
            cursor: auto;
        }
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php select {
            background: none;
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important; 
            appearance: menulist !important;
        }
        .<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>-includes-admin-options-php input[type=color] {
            background-color: <?php echo esc_attr( $bg_secondary ); ?> !important;
            height: 4rem;
        }

        /* Color field sample */
        .options_color_sample {
            height: 30px;
            width: 50px;
            border-radius: 4px;
            display: inline-block;
            position: absolute;
            margin-left: 10px
        }

        /* Required text */
        .gfield_required_text,
        .required-text {
            font-style: italic;
            color: #FF99CC !important;
        }

    <?php } ?>

    </style>

<?php }