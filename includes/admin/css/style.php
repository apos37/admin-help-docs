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
if ( ( $current_screen->id == $options_page ) || ( $current_screen->id == 'edit-help-docs-folder' ) || ( $current_screen->post_type == 'help-docs' ) || ( $current_screen->post_type == 'help-doc-imports' ) ) {
    // Get the colors
    $HELPDOCS_COLORS = new HELPDOCS_COLORS();
    $color_ac = $HELPDOCS_COLORS->get( 'ac' );
    $color_bg = $HELPDOCS_COLORS->get( 'bg' );
    $color_ti = $HELPDOCS_COLORS->get( 'ti' );
    $color_fg = $HELPDOCS_COLORS->get( 'fg' );
    $color_cl = $HELPDOCS_COLORS->get( 'cl' );
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

    <?php if ( $current_screen->id == 'edit-help-docs-folder' || $current_screen->post_type == 'help-docs' || $current_screen->post_type == 'help-doc-imports' ) { ?>

        /* ---------------------------------------------
            POST TYPES ( HELP-DOCS/HELP-DOC-IMPORTS )
        --------------------------------------------- */

        .wrap.<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> {
            margin-left: 4px !important;
            background: <?php echo esc_attr( $color_bg ); ?> !important;
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
        .taxonomy-help-docs-folder .form-field.term-parent-wrap {
            display: none;
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

        #doc-viewer h3,
        #doc-viewer .wrap h3 {
            margin-top: revert !important;
            border-top: revert !important;
            padding-top: revert !important;
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
            background: <?php echo esc_attr( $color_bg ); ?> !important;
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
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            filter: brightness( 95% );
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
            border: 1px solid <?php echo esc_attr( $color_fg ); ?>;
        }
        .admin-large-table th,
        .admin-large-table td {
            color: <?php echo esc_attr( $color_fg ); ?> !important;
            padding: 10px;
        }
        .admin-large-table td {
            word-break:break-all;
        }
        .admin-large-table tr:nth-child(even) {
            background: <?php echo esc_attr( $color_bg ); ?> !important;
        }
        table.alternate-row tr:nth-child(even) {
            background: <?php echo esc_attr( $color_bg ); ?> !important;
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
            background: yellow;
            color: <?php echo esc_attr( $color_cl ); ?>;
            padding: 0 4px;
            margin-left: 5px;
            border-radius: 0;
            display: none;
        }
        .click-to-copy-link.shortcode {
            text-decoration: none;
        }

        /* ---------------------------------------------
                            FORMS
        --------------------------------------------- */

        /* Checkboxes and Radios */
        input[type="checkbox"],
        input[type="radio"] {
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            filter: brightness( 95% );
            border: 1px solid <?php echo esc_attr( $color_ac ); ?>;
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
            color: <?php echo esc_attr( $color_bg ); ?>;
            content: '\2713';
            margin: 15px 3px !important;
            font-size: 16px;
            font-weight: bold;
        }
        input[type="radio"]:checked:before {
            color: <?php echo esc_attr( $color_bg ); ?>;
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            margin: 4px 4px !important;
            width: 20px;
            height: 20px;
        }
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background: <?php echo esc_attr( $color_ac ); ?>
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
        /* Input fields */
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> input[type=text]:not( .normal-text-input ),
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> input[type=number],
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> textarea,
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> select {
            background-color: <?php echo esc_attr( $color_bg ); ?> !important;
            filter: brightness( 95% );
            color: <?php echo esc_attr( $color_fg ); ?> !important;
            padding: 8px 12px !important;
            min-height: 2.85rem !important;
            vertical-align: revert;
        }
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> textarea {
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            filter: brightness( 95% );
            max-width: 75%;
            height: 10rem;
            cursor: auto;
        }
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> select {
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            filter: brightness( 95% );
        }
        .toplevel_page_<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?> input[type=color] {
            background-color: <?php echo esc_attr( $color_bg ); ?> !important;
            height: 4rem;
            width: 10rem !important;
        }        

        /* Field descriptions/comments */
        .field-desc {
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            filter: brightness( 97% );
            color: <?php echo esc_attr( $color_fg ); ?>;
            display: inline-block;
            padding: 10px;
            border: 1px solid <?php echo esc_attr( $color_bg ); ?>;
            font-size: 12px !important;
            line-height: 1.5;
            -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.04);
            box-shadow: 0 1px 1px <?php echo esc_attr( $color_ac ); ?>;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            border-radius: 5px;
        }
        .field-desc.break {
            display: block !important;
            width: max-content;
        }
        .field-desc code {
            background-color: <?php echo esc_attr( $color_bg ); ?>;
            filter: brightness( 94% );
            border-radius: 2px;
            font-size: revert !important;
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

<?php } ?>