jQuery( document ).ready( function( $ ) {

    const POST_PHP_ENCODED = 'cG9zdC5waHA=';
    const MAIN_ENCODED     = 'bWFpbg==';
    const ADMIN_BAR_ENCODED = 'YWRtaW5fYmFy';

    function checkExclusivity() {
        const $allSelects = $( 'select[id$="_site_location"]' );
        const selectedValues = [];

        $allSelects.each( function() {
            const val = $( this ).val();
            if ( val === MAIN_ENCODED || val === ADMIN_BAR_ENCODED ) {
                selectedValues.push( val );
            }
        } );

        $allSelects.each( function() {
            const $currentSelect = $( this );
            const currentVal = $currentSelect.val();

            $currentSelect.find( 'option' ).each( function() {
                const optVal = $( this ).val();
                
                if ( optVal === MAIN_ENCODED || optVal === ADMIN_BAR_ENCODED ) {
                    if ( selectedValues.includes( optVal ) && currentVal !== optVal ) {
                        $( this ).prop( 'disabled', true );
                    } else {
                        $( this ).prop( 'disabled', false );
                    }
                }
            } );
        } );
    }

    function updateRow( $row ) {
        const site_location_el = $row.find( 'select[id$="_site_location"]' );
        const page_location_el = $row.find( 'select[id$="_page_location"]' );
        const site_value_encoded = site_location_el.val();

        checkExclusivity();
        
        // 1. Specific logic for the "Side" option visibility
        const $sideOption = page_location_el.find( 'option[value="side"]' );
        if ( site_value_encoded === POST_PHP_ENCODED ) {
            $sideOption.show();
        } else {
            // If user previously selected 'side' and it's now hidden, reset to default
            if ( page_location_el.val() === 'side' ) {
                page_location_el.val( page_location_el.find( 'option:first' ).val() );
            }
            $sideOption.hide();
        }

        // 2. Process localized rules for showing/hiding entire fields
        const rules = helpdocs_help_docs.location_rules[ site_value_encoded ] || [];
        let fields_to_show = [];

        rules.forEach( rule => {
            if ( rule.controller ) {
                const $controller = $row.find( '[id$="_' + rule.controller + '"]' );
                if ( $controller.val() === rule.controller_value ) {
                    fields_to_show.push( rule.dependent );
                }
            } else if ( rule.field ) {
                fields_to_show.push( rule.field );
            }
        } );

        const allFields = [ 
            'page_location', 'custom', 'addt_params', 'post_types', 
            'order', 'toc', 'function_example', 'admin_bar_tips', 'dashboard_warning', 'css_selector' 
        ];

        allFields.forEach( field => {
            const shouldShow = fields_to_show.includes( field );
            const $el = $row.find( '[id$="_' + field + '"]' );
            
            if ( ! $el.length ) return;

            let $container;
            if ( field === 'post_types' ) {
                $container = $el.closest( '.helpdocs-post-types' );
            } else if ( field === 'addt_params' || field === 'toc' ) {
                $container = $el.closest( 'label' );
            } else if ( field === 'function_example' || field === 'admin_bar_tips' || field === 'dashboard_warning' ) {
                $container = $el;
            } else if ( field === 'custom' ) {
                $container = $el.closest( '.helpdocs-custom-url' );
            } else {
                $container = $el.add( $el.prev( 'label' ) );
            }

            $container.toggle( shouldShow );
            
            if ( shouldShow ) {
                if ( $el.is( 'input[type="text"], input[type="number"]' ) ) {
                    $el.css( 'display', 'inline-block' );
                } else if ( $el.is( 'select' ) || $el.is( 'span' ) ) {
                    $el.css( 'display', 'block' );
                }
            }
        } );
    }

    $( '#helpdocs-location-repeater' ).on( 'change', 'select', function() {
        updateRow( $( this ).closest( '.helpdocs-location-row' ) );
    } );

    $( '#add-location' ).on( 'click', function( e ) {
        e.preventDefault();
        const $repeater = $( '#helpdocs-location-repeater' );
        const $rows = $repeater.find( '.helpdocs-location-row' );
        const newIndex = $rows.length;
        
        const $newRow = $rows.first().clone();

        $newRow.find( 'input, select, label, span' ).each( function() {
            const $item = $( this );
            [ 'name', 'id', 'for' ].forEach( attr => {
                let val = $item.attr( attr );
                if ( val ) {
                    val = val.replace( /\[\s*\d+\s*\]/, '[ ' + newIndex + ' ]' )
                             .replace( /_\d+_/, '_' + newIndex + '_' );
                    $item.attr( attr, val );
                }
            } );
        } );

        $newRow.find( 'input[type="text"], input[type="number"], select' ).val( '' );
        $newRow.find( 'input[type="checkbox"]' ).prop( 'checked', false );

        if ( $newRow.find( '.remove-location' ).length === 0 ) {
            $newRow.append( '<button type="button" class="remove-location button" title="Remove Location"><span class="dashicons dashicons-trash"></span></button>' );
        }

        $repeater.append( $newRow );
        updateRow( $newRow ); 
    } );

    $( '#helpdocs-location-repeater' ).on( 'click', '.remove-location', function( e ) {
        e.preventDefault();
        $( this ).closest( '.helpdocs-location-row' ).remove();
        checkExclusivity();
    } );
} );