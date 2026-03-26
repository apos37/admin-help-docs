jQuery( document ).ready( function( $ ) {

    // Drag-and-drop sorting
    let draggedItem = null;
    let placeholder = $( '<li class="helpdocs-sorter-placeholder"></li>' );

    let scrollAnimationFrame = null;
    let pointerY = 0;

    $( document ).on( 'dragstart', '.helpdocs-sorter-item', function ( e ) {
        draggedItem = this;
        $( this ).addClass( 'is-dragging' );
        $( this ).after( placeholder );
        e.originalEvent.dataTransfer.effectAllowed = 'move';
    } );

    $( document ).on( 'dragend', '.helpdocs-sorter-item', function ( ) {
        $( this ).removeClass( 'is-dragging' );
        if ( draggedItem ) { placeholder.replaceWith( draggedItem ); }
        cancelAnimationFrame( scrollAnimationFrame );
        scrollAnimationFrame = null;
        markDirty();
    } );

    $( document ).on( 'dragover', '.helpdocs-sorter-item', function ( e ) {
        e.preventDefault();
        pointerY = e.originalEvent.clientY;
        if ( this === draggedItem ) { return; }
        const bounding = this.getBoundingClientRect();
        const offset = pointerY - bounding.top;
        const middle = bounding.height / 2;
        if ( offset > middle ) { $( this ).after( placeholder ); }
        else { $( this ).before( placeholder ); }
        startAutoScroll();
    } );

    function startAutoScroll() {
        if ( scrollAnimationFrame ) { return; }
        function step ( ) {
            const margin = 80;
            const speed = 12;
            if ( pointerY < margin ) { window.scrollBy( 0, -speed ); }
            else if ( pointerY > window.innerHeight - margin ) { window.scrollBy( 0, speed ); }
            scrollAnimationFrame = requestAnimationFrame( step );
        }
        step();
    }
    
    // Dirty state tracking
    const saveReminder = $( '#helpdocs-save-reminder' );
    let isDirty = false;

    function markDirty() {
        if ( ! isDirty ) {
            isDirty = true;
            saveReminder.fadeIn( 150 );
            $( '#helpdocs-save-status' ).remove();
        }
    }

    function clearDirty() {
        isDirty = false;
        saveReminder.hide();
    }

    $( window ).on( 'beforeunload', function( e ) {
        if( isDirty ){
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    $( document ).on( 'change input', '.helpdocs-settings-grid [name]', function() {
        markDirty();
    });

    // Saving
    const $saveButton = $( '#helpdocs-subheader .tab-button' );
    const originalText = $saveButton.text();
    let savingInterval;

    // Start animated "Saving..." in browser tab
    function startSavingTitle() {
        const originalTitle = document.title;
        let dots = 0;

        document.title = 'Saving'; // immediate first update

        savingInterval = setInterval( function() {
            dots = (dots + 1) % 4; // cycles 0 → 3
            document.title = 'Saving' + '.'.repeat(dots);
        }, 500 );

        return originalTitle;
    }

    // Stop animation and restore tab title
    function stopSavingTitle( originalTitle ) {
        clearInterval( savingInterval );
        document.title = originalTitle;
    }

    function showSaving() {
        $saveButton.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update spin"></span> Saving...' );
        $( '#helpdocs-save-status' ).remove();
    }

    function showResult( message, success = true ) {
        $saveButton.prop( 'disabled', false ).text( originalText );
        const $status = $( '<span id="helpdocs-save-status"></span>' ).text( message );
        $status.css({
            marginLeft: '10px',
            color: success ? 'green' : 'red',
            fontWeight: 'bold'
        });
        $saveButton.after( $status );
    }

    function gatherSettings() {
        const data = {};
        $( '.helpdocs-settings-grid [name]' ).each( function() {
            const $field = $( this );
            let val;

            if ( $field.attr( 'type' ) === 'checkbox' ) {
                if ( $field.is( ':checkbox' ) && $field.attr( 'name' ).endsWith( '[]' ) ) {
                    val = $( '[name="' + $field.attr( 'name' ) + '"]:checked' ).map( function() { return this.value; } ).get();
                } else {
                    val = $field.is( ':checked' ) ? 1 : 0;
                }
            } else {
                val = $field.val();
            }

            let key = $field.attr( 'name' ).replace( /\[\]$/, '' );
            data[ key ] = val;
        });
        return data;
    }

    function saveSettings() {
        const settings = gatherSettings();
        const menuOrder = [];
        $( '.helpdocs-sorter-item' ).each( function() {
            menuOrder.push( $( this ).attr( 'data-value' ) );
        } );
        const originalTitle = startSavingTitle();
        showSaving();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'helpdocs_save_menu_order',
                nonce: helpdocs_admin_menu.nonce,
                settings: settings,
                menu_order: menuOrder
            },
            success: function( response ) {
                stopSavingTitle( originalTitle );
                if ( response.success ) {
                    showResult( 'Settings saved successfully.' );
                    clearDirty();
                } else {
                    showResult( response.data || 'Error saving settings. Please try again.', false );
                }

                setTimeout( function() {
                    location.reload();
                }, 1000 );
            },
            error: function() {
                stopSavingTitle( originalTitle );
                showResult( 'Error saving settings. Please try again.', false );
            }
        });
    }

    $saveButton.on( 'click', saveSettings );

    $( document ).on( 'keydown', function( e ) {
        if ( ( e.ctrlKey || e.metaKey ) && e.key.toLowerCase() === 's' ) {
            e.preventDefault();
            saveSettings();
        }
    } );

    // Colorize Separators
    function updateSeparatorColor() {
        var enabled = $( '#helpdocs_field_colorize_separators input' ).is( ':checked' );
        var color = $( '#helpdocs_field_color_admin_menu_sep input' ).val();

        if ( enabled ) {
            $( 'body' ).addClass( 'helpdocs-separator-enabled' );
            $( '#adminmenu div.separator' ).css( 'border-top-color', color );
        } else {
            $( 'body' ).removeClass( 'helpdocs-separator-enabled' );
        }
    }

    // Listen for changes
    $( document ).on( 'change', '#helpdocs_field_colorize_separators input, #helpdocs_field_color_admin_menu_sep input', function() {
        updateSeparatorColor();
        console.log( 'Separator color updated.' );
    } );

    // Show the sublabels
    $( document ).on( 'change', '#helpdocs_field_show_menu_item_slugs input', function () {
        if ( $( this ).is( ':checked' ) ) {
            $( 'html' ).addClass( 'helpdocs-view-sublabels' );
        } else {
            $( 'html' ).removeClass( 'helpdocs-view-sublabels' );
        }
    } );

} );