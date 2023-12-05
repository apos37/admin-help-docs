jQuery( $ => {
    const HELPDOCS_GO_PF = "helpdocs_";
    const HELPDOCS_TEXTDOMAIN = "admin-help-docs";
    $( "#" + HELPDOCS_GO_PF + "dashicon" ).on( "change", function() {
        $( "#adminmenu #toplevel_page_" + HELPDOCS_TEXTDOMAIN + " .wp-menu-image" ).attr( "class", "wp-menu-image dashicons-before " + this.value );
        var dashiconName = this.value.replace( 'dashicons-', '' );
        $( "#view-dashicons-link" ).attr( "href", "<?php echo esc_url( $dashicons_url ); ?>#" + dashiconName );
        $( "#dashicon-preview" ).attr( "class", this.value );
        if ( $( "#wp-admin-bar-" + HELPDOCS_TEXTDOMAIN ) ) {
            $( "#wp-admin-bar-" + HELPDOCS_TEXTDOMAIN + " .dashicons-before" ).attr( "class", "dashicons-before " + this.value );
        }
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "logo" ).on( "keyup change", function() {
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "page_title" ).on( "keyup change", function() {
        $( "#plugin-page-title" ).html( this.value );
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "multisite_sfx" ).on( "keyup change", function() {
        $( "#plugin-multisite-suffix" ).html( this.value );
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "hide_version" ).change( function(e) {
        $( "#plugin-version" ).toggle();
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "menu_title" ).on( "keyup change", function() {
        $( "#toplevel_page_" + HELPDOCS_TEXTDOMAIN + " .wp-menu-name" ).html( this.value );
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "menu_position" ).on( "keyup change", function() {
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "footer_left" ).on( "keyup change", function() {
        $( "#footer-left" ).html( this.value );
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "footer_right" ).on( "keyup change", function() {
        $( "#footer-upgrade" ).html( this.value );
        saveReminder();
    } )
    $( "." + HELPDOCS_GO_PF + "role_checkbox" ).change( function() {
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "color_ac" ).on( "change", function() {
        const ac_elements = [ 'input[type="checkbox"]' ];
        for ( let ac = 0; ac < ac_elements.length; ac++ ) {
            $( ac_elements[ac] ).attr( 'style', 'border: 1px solid ' + this.value + ' !important;' );
        }
        $( '.field-desc' ).css( 'box-shadow', '0 1px 1px ' + this.value );
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "color_bg" ).on( "change", function() {
        const thisValue = this.value;
        const bg_elements = [ 'html', 'body', '#wpwrap', '#wpcontent', '#wpbody', '#wpbody-content', '.wrap', '.nav-tab-wrapper .nav-tab' ];
        const border_elements = [ '.nav-tab-wrapper .nav-tab.nav-tab-active' ];
        for ( let bg = 0; bg < bg_elements.length; bg++ ) {
            const currentStyle = $( bg_elements[bg] ).css( 'color' );
            $( bg_elements[bg] ).attr( 'style', 'color: ' + currentStyle + ' !important; background-color: ' + thisValue + ' !important;' );
        }
        for ( let b = 0; b < border_elements.length; b++ ) {
            $( border_elements[b] ).attr( 'style', 'border-color: ' + thisValue + ' !important' );
        }
        $( '.field-desc' ).css( 'background-color', thisValue ).css( 'border-color', thisValue );
        $( '.field-desc code' ).css( 'background-color', thisValue );
        const input_elements = [ 'input', 'textarea', 'select' ];
        for ( let input = 0; input < input_elements.length; input++ ) {
            $( input_elements[input] ).each( function() {
                const currentColor = $( this ).css( 'color' );
                const currentWidth = $( this ).css( 'width' );
                $( this ).attr( 'style', 'width: ' + currentWidth + ' !important; color: ' + currentColor + ' !important; background-color: ' + thisValue + ' !important;' );
            } );
        }
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "color_fg" ).on( "change", function() {
        const thisValue = this.value;
        const fg_elements = [ 'html', 'body', '#wpwrap', '#wpcontent', '#wpbody', '#wpbody-content', '.wrap', '.admin-title-cont h1', '.tab-header', '.wp-heading-inline', '.form-table th','.subsubsub .count', '#footer-thankyou', '#footer-upgrade' ];
        for ( let fg = 0; fg < fg_elements.length; fg++ ) {
            const currentStyle = $( fg_elements[fg] ).css( 'background-color' );
            $( fg_elements[fg] ).attr( 'style', 'background-color: ' + currentStyle + ' !important; color: ' + thisValue + ' !important;' );
        }
        $( '.field-desc' ).css( 'color', thisValue );
        const input_elements = [ 'input', 'textarea', 'select' ];
        for ( let input = 0; input < input_elements.length; input++ ) {
            $( input_elements[input] ).each( function() {
                const currentBgColor = $( this ).css( 'background-color' );
                const currentWidth = $( this ).css( 'width' );
                $( this ).attr( 'style', 'width: ' + currentWidth + ' !important; background-color: ' + currentBgColor + ' !important; color: ' + thisValue + ' !important;' );
            } );
        }
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "color_cl" ).on( "change", function() {
        const cl_elements = [ '#wpbody-content a', '#footer-thankyou a', '#footer-upgrade a' ];
        for ( let cl = 0; cl < cl_elements.length; cl++ ) {
            // const currentStyle = $( cl_elements[cl] ).css( 'background-color' );
            $( cl_elements[cl] ).attr( 'style', 'color: ' + this.value + ' !important;' );
        }
        saveReminder();
    } )
    $( "#" + HELPDOCS_GO_PF + "disable_user_prefs" ).change( function() {
        saveReminder();
    } )
    function saveReminder() {
        var div = jQuery( "#save-reminder" );
        if ( div.css( "display" ) == "none" ) {
            div.show( "slow" );
            console.log( "Don't forget to save your preferences!" );
        } else {
            console.log( "Ooh, you're getting change happy! I love it!" );
        }
    } // End saveReminder()
} )