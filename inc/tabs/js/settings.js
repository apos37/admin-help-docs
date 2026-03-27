jQuery( document ).ready( function( $ ) {
    

    // Dirty state tracking
    const saveReminder = $( '#helpdocs-save-reminder' );
    let isDirty = false;

    // Initialize Code Editor for Custom CSS
    if ( $( '#main_docs_css' ).length && typeof helpdocs_settings !== 'undefined' && helpdocs_settings.editor_settings ) {
        cssEditor = wp.codeEditor.initialize( 'main_docs_css', helpdocs_settings.editor_settings );
        
        // CodeMirror has its own change event
        cssEditor.codemirror.on( 'change', function() {
            markDirty();
        });
    }

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

    // Show/hide conditional fields based on their target field's value
    function updateConditionalFields() {
        $( '.helpdocs-field[data-condition-field]' ).each( function() {
        var $field         = $( this );
        var targetId       = $field.data( 'condition-field' );
        var expectedValue  = $field.data( 'condition-value' );
        var $target        = $( '#' + targetId );

        if ( $target.length ) {
            var isVisible = false;

            if ( $target.is( ':checkbox' ) ) {
                // If it's a checkbox, we check if it's checked
                // and if that state matches our expected value (1 for checked)
                var isChecked = $target.is( ':checked' ) ? '1' : '0';
                isVisible = ( isChecked == expectedValue );
            } else {
                // For select/text fields
                isVisible = ( $target.val() == expectedValue );
            }

            if ( isVisible ) {
                $field.removeClass( 'condition-hide' );
            } else {
                $field.addClass( 'condition-hide' );
            }
        }
    } );
    }

    $( document ).on( 'change', '.has-condition, .has-condition input, .has-condition select', function() {
        updateConditionalFields();
    } );

    // Menu Title
    $( document ).on( 'input', '#helpdocs_field_menu_title input', function() {
        var title = $( this ).val();
        const target = $( 'li#toplevel_page_admin-help-docs .wp-menu-name' );

        if ( title.length > 0 ) {
            target.text( title );
        } else {
            target.text( '...' );
        }
    } );

    // Dashicon
    $( document ).on( 'change', '#helpdocs_field_dashicon select', function() {
        var icon = $( this ).val();
        const target = $( 'li#toplevel_page_admin-help-docs .wp-menu-image' );
        target.removeClass().addClass( 'wp-menu-image dashicons-before dashicons-' + icon );
    } );

    // Page Title
    $( document ).on( 'input', '#helpdocs_field_page_title input', function() {
        var title = $( this ).val();
        const target = $( '#helpdocs-header h1' );
        if ( title.length > 0 ) {
            target.text( title );
        } else {
            target.text( '...' );
        }
    } );

    // Logo
    $( document ).on( 'focusout paste', '#helpdocs_field_logo input', function( e ) {
        const $input = $( this );
        const target = $( '#helpdocs-header .logo' );

        // For paste, delay slightly to get the pasted value
        const updateLogo = function() {
            const url = $input.val();
            if ( url.length > 0 ) {
                target.attr( 'src', url ).show();
            } else {
                target.hide();
            }
        };

        if ( e.type === 'paste' ) {
            setTimeout( updateLogo, 50 );
        } else {
            updateLogo();
        }
    } );

    // Utility: hex → RGB
    function hexToRgb( hex ) {
        hex = hex.replace( '#', '' );
        if ( hex.length === 3 ) hex = hex.split( '' ).map( c => c + c ).join( '' );
        const bigint = parseInt( hex, 16 );
        return [ (bigint >> 16) & 255, (bigint >> 8) & 255, bigint & 255 ];
    }

    // Utility: relative luminance
    function getLuminance( hex ) {
        const [ r, g, b ] = hexToRgb( hex ).map( c => {
            c /= 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow( (c + 0.055) / 1.055, 2.4 );
        } );
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    // Update logo based on header_bg
    function updateLogoContrast( bgColor, inputSelector, previewSelector = null, themeKey = null ) {
        const $input = $( inputSelector );
        const current = $input.val() || '';
        const defaultLogo = helpdocs_settings.default_logo;

        // Get the plugin's default image directory
        let defaultDir = defaultLogo.substring( 0, defaultLogo.lastIndexOf( '/' ) + 1 );

        const decodedCurrent = decodeURIComponent( current );
        const normalizedDefaultDir = decodeURIComponent( defaultDir );

        // Only proceed if the current logo is one of our default assets
        if ( decodedCurrent.startsWith( normalizedDefaultDir ) ) {

            if ( inputSelector === '#doc_logo' && themeKey === 'classic' ) {
                $input.val( defaultLogo );

            } else {
                let newFilename = 'logo.png'; // default blue
                const luminance = getLuminance( bgColor );
                if ( luminance < 0.4 ) {
                    newFilename = 'logo-white.png';
                } else if ( luminance > 0.75 ) {
                    newFilename = 'logo-black.png';
                }

                const newLogo = normalizedDefaultDir + newFilename;

                if ( newLogo !== decodedCurrent ) {
                    // Always update the input value
                    $input.val( newLogo );

                    // Only update an image preview if a selector was provided
                    if ( previewSelector ) {
                        $( previewSelector ).attr( 'src', newLogo );
                    }
                }
            }
        }
    }

    // Theme change handler
    $( document ).on( 'change', '#helpdocs_field_themes select', function() {
        const themeKey = $( this ).val();
        const theme = helpdocs_settings.themes[ themeKey ];
        if ( theme && theme.colors ) {
            for ( const [ key, color ] of Object.entries( theme.colors ) ) {
                const variable = '--helpdocs-color-' + key.replaceAll( '_', '-' );
                document.documentElement.style.setProperty( variable, color );
                $( '#color_' + key ).val( color );
            }

            // Update logo based on header_bg (updates preview + input)
            if ( theme.colors.header_bg ) {
                updateLogoContrast( theme.colors.header_bg, '#logo', '#helpdocs-header .logo' );
            }

            // Update doc logo based on doc_bg (updates input ONLY)
            if ( theme.colors.doc_bg ) {
                updateLogoContrast( theme.colors.doc_bg, '#doc_logo', null, themeKey );
            }
        }
    });

    // Color picker live update
    $( document ).on( 'input', 'input[type="color"]', function() {
        const input = $( this );
        const fieldId = input.attr( 'id' ); 
        const variable = '--helpdocs-' + fieldId.replaceAll( '_', '-' );
        const color = input.val();
        document.documentElement.style.setProperty( variable, color );
        $( '#helpdocs_field_themes select' ).val( 'custom' );

        // If header_bg changes, update both preview and input
        if ( fieldId === 'color_header_bg' ) {
            updateLogoContrast( color, '#logo', '#helpdocs-header .logo' );
        }

        // If doc_bg changes, update input only
        if ( fieldId === 'color_doc_bg' ) {
            updateLogoContrast( color, '#doc_logo' );
        }
    });

    // Footer Text
    $( document ).on( 'input', '#helpdocs_field_footer_left textarea', function() {
        let text = $( this ).val();
        text = text.replace( /{version}/g, helpdocs_settings.wp_version );
        const target = $( '#footer-left' );
        if ( text.length > 0 ) {
            target.html( text );
        } else {
            target.text( '...' );
        }
    } );

    $( document ).on( 'input', '#helpdocs_field_footer_right textarea', function() {
        let text = $( this ).val();
        text = text.replace( /{version}/g, helpdocs_settings.wp_version );
        const target = $( '#footer-upgrade' );
        if ( text.length > 0 ) {
            target.html( text );
        } else {
            target.text( '...' );
        }
    } );

    // Saving
    const $saveButton = $( '#helpdocs-subheader .tab-button' );
    const originalText = $saveButton.text();
    let savingInterval;

    // Start animated "Saving..." in browser tab
    function startSavingTitle() {
        const originalTitle = document.title;
        let dots = 0;

        document.title = helpdocs_settings.saving_text; // immediate first update

        savingInterval = setInterval( function() {
            dots = (dots + 1) % 4; // cycles 0 → 3
            document.title = helpdocs_settings.saving_text + '.'.repeat(dots);
        }, 500 );

        return originalTitle;
    }

    // Stop animation and restore tab title
    function stopSavingTitle(originalTitle) {
        clearInterval(savingInterval);
        document.title = originalTitle;
    }

    function showSaving() {
        $saveButton.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update spin"></span> ' + helpdocs_settings.saving_text + '...' );
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
        if ( cssEditor ) {
            cssEditor.codemirror.save();
        }
        
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
        const originalTitle = startSavingTitle();
        showSaving();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'helpdocs_save_settings',
                nonce: helpdocs_settings.nonce,
                settings: settings
            },
            success: function( response ) {
                stopSavingTitle( originalTitle );
                if ( response.success ) {
                    showResult( helpdocs_settings.saved_text );
                    clearDirty();
                } else {
                    showResult( response.data || helpdocs_settings.error_text, false );
                }
            },
            error: function() {
                stopSavingTitle( originalTitle );
                showResult( helpdocs_settings.error_text, false );
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

    // Apply live updates for a specific field
    function applyLiveUpdates( $field ) {
        const type = $field.attr( 'type' );
        const id = $field.attr( 'id' );

        if ( type === 'color' ) {
            const variable = '--helpdocs-' + id.replaceAll( '_', '-' );
            document.documentElement.style.setProperty( variable, $field.val() );
        } else if ( id === 'menu_title' ) {
            $( 'li#toplevel_page_admin-help-docs .wp-menu-name' ).text( $field.val() || '...' );
        } else if ( id === 'page_title' ) {
            $( '#helpdocs-header h1' ).text( $field.val() || '...' );
        } else if ( id === 'dashicon' ) {
            $( 'li#toplevel_page_admin-help-docs .wp-menu-image' )
                .removeClass()
                .addClass( 'wp-menu-image dashicons-before dashicons-' + $field.val() );
        } else if ( id === 'logo' ) {
            const target = $( '#helpdocs-header .logo' );
            const url = $field.val();
            url ? target.attr( 'src', url ).show() : target.hide();
        } else if ( id === 'left_footer' || id === 'right_footer' ) {
            const target = id === 'left_footer' ? $( '#footer-left' ) : $( '#footer-upgrade' );
            let value = $field.val();
            if ( id === 'right_footer' ) value = value.replace( /{version}/g, helpdocs_settings.wp_version );
            target.html( value.length ? value : '...' );
        }

        updateConditionalFields();
    }

    // Call this after any bulk operation
    function updateAllLive() {
        $( '.helpdocs-settings-grid [name]' ).each( function() {
            console.log( 'Updating live for', this );
            applyLiveUpdates( $( this ) );
        });
    }

    // Reset Settings
    $( '#helpdocs-reset-colors' ).on( 'click', function( e ) {
        e.preventDefault();

        if ( ! confirm( 'Are you sure you want to reset all colors to their defaults?' ) ) {
            return;
        }

        helpdocs_settings.settings.forEach( function( field ) {
            if ( field.type === 'color' && field.default ) {
                $( '#' + field.name )
                    .val( field.default );
            }
        } );

        updateAllLive();
    } );

    $( '#helpdocs-reset-settings' ).on( 'click', function( e ) {
        e.preventDefault();

        if ( ! confirm( 'Are you sure you want to reset ALL settings to their defaults?' ) ) {
            return;
        }

        helpdocs_settings.settings.forEach( function( field ) {
            if ( field.type === 'html' ) {
                return;
            }

            const $field = $( '#' + field.name );

            switch ( field.type ) {

                case 'checkbox':
                    $field
                        .prop( 'checked', !! field.default );
                    break;

                case 'checkboxes':
                    // Uncheck all first
                    $( '[ name="helpdocs_' + field.name + '[]" ]' )
                        .prop( 'checked', false );

                    // Check defaults if array exists
                    if ( Array.isArray( field.default ) ) {

                        field.default.forEach( function( value ) {

                            $( '[ name="helpdocs_' + field.name + '[]" ][ value="' + value + '" ]' )
                                .prop( 'checked', true );

                        } );

                    }

                    break;

                case 'select':
                case 'text':
                case 'textarea':
                case 'number':
                case 'color':
                    $field
                        .val( field.default ?? '' );

                    break;
            }
        } );

        updateAllLive();

        if ( field.name === 'main_docs_css' && cssEditor ) {
            cssEditor.codemirror.setValue( field.default ?? '' );
        }
    } );

    // --- DOWNLOAD COLORS ---
    $( '#helpdocs-download-colors-btn' ).on( 'click', function( e ) {
        e.preventDefault();

        const data = {};
        helpdocs_settings.settings.forEach( function( field ) {
            if ( field.type !== 'color' ) return;
            const $field = $( '#' + field.name );
            data[ field.name ] = $field.val();
        });

        const blob = new Blob( [ JSON.stringify( data, null, 4 ) ], { type: 'application/json' } );
        const url = URL.createObjectURL( blob );
        const a = document.createElement( 'a' );
        a.href = url;
        a.download = 'helpdocs-colors.json';
        a.click();
        URL.revokeObjectURL( url );
    });

    // --- UPLOAD COLORS ---
    $( '#helpdocs-upload-colors' ).on( 'change', function( e ) {
        const file = e.target.files[0];
        if ( ! file ) return;

        const reader = new FileReader();
        reader.onload = function( event ) {
            try {
                const uploadedColors = JSON.parse( event.target.result );

                helpdocs_settings.settings.forEach( function( field ) {
                    if ( field.type !== 'color' ) return;
                    const $field = $( '#' + field.name );
                    if ( ! uploadedColors.hasOwnProperty( field.name ) ) return;
                    $field.val( uploadedColors[ field.name ] );
                });

                updateAllLive();

                $( '#helpdocs-upload-colors-filename' ).text( file.name ).show();

            } catch ( err ) {
                alert( 'Invalid JSON file. Please check the file and try again.' );
                $( '#helpdocs-upload-colors' ).val( '' );
            }
        };

        reader.readAsText( file );
    });

    // --- DOWNLOAD SETTINGS ---
    $( '#helpdocs-download-settings-btn' ).on( 'click', function( e ) {
        e.preventDefault();

        if ( cssEditor ) {
            cssEditor.codemirror.save();
        }

        const data = {};
        helpdocs_settings.settings.forEach( function( field ) {

            if ( field.type === 'html' || field.name === 'default_doc' ) return;

            const $field = $( '#' + field.name );

            let value;
            switch ( field.type ) {
                case 'checkbox':
                    value = $field.is( ':checked' ) ? 1 : 0;
                    break;

                case 'checkboxes':
                    value = $( '[name="helpdocs_' + field.name + '[]"]:checked' ).map( function() {
                        return this.value;
                    }).get();
                    break;

                default:
                    value = $field.val();
            }

            data[ field.name ] = value;
        });

        const blob = new Blob( [ JSON.stringify( data, null, 4 ) ], { type: 'application/json' } );
        const url = URL.createObjectURL( blob );
        const a = document.createElement( 'a' );
        a.href = url;
        a.download = 'helpdocs-settings.json';
        a.click();
        URL.revokeObjectURL( url );
    });

    // --- UPLOAD SETTINGS ---
    $( '#helpdocs-upload-settings' ).on( 'change', function( e ) {
        const file = e.target.files[ 0 ];
        if ( ! file ) return;

        const reader = new FileReader();
        reader.onload = function( event ) {
            try {
                const uploadedSettings = JSON.parse( event.target.result );

                helpdocs_settings.settings.forEach( function( field ) {

                    if ( field.type === 'html' || field.name === 'default_doc' ) return;

                    const $field = $( '#' + field.name );

                    if ( ! uploadedSettings.hasOwnProperty( field.name ) ) return;

                    const value = uploadedSettings[ field.name ];

                    switch ( field.type ) {

                        case 'checkbox':
                            $field.prop( 'checked', !! value );
                            break;

                        case 'checkboxes':
                            $( '[name="helpdocs_' + field.name + '[]"]' ).prop( 'checked', false );
                            if ( Array.isArray( value ) ) {
                                value.forEach( function( val ) {
                                    $( '[name="helpdocs_' + field.name + '[]"][value="' + val + '"]' )
                                        .prop( 'checked', true );
                                });
                            }
                            break;

                        default:
                            if ( field.name === 'main_docs_css' && cssEditor ) {
                                cssEditor.codemirror.setValue( value || '' );
                            }
                            
                            $field.val( value );
                    }

                });

                updateAllLive();

                // Enable upload button after reading
                $( '#helpdocs-upload-settings-btn' ).prop( 'disabled', false );
                $( '#helpdocs-upload-filename' ).text( file.name ).show();

            } catch ( err ) {
                alert( 'Invalid JSON file. Please check the file and try again.' );
                $( '#helpdocs-upload-settings' ).val( '' );
                $( '#helpdocs-upload-settings-btn' ).prop( 'disabled', true );
            }
        };

        reader.readAsText( file );
    });

    // --- TRIGGER UPLOAD BUTTON ---
    $( '#helpdocs-upload-settings-btn' ).on( 'click', function( e ) {
        e.preventDefault();

        if ( ! confirm( 'Are you sure you want to overwrite all settings with this file?' ) ) return;

        // Optionally trigger save after populating fields
        $( '#helpdocs-subheader .tab-button' ).click();
    });

    // --- GENERATE API KEY ---
    $( '.helpdocs-generate-api-key' ).on( 'click', function() {
        const array = new Uint8Array( 32 );
        window.crypto.getRandomValues( array );
        const key = Array.from( array, byte => byte.toString( 16 ).padStart( 2, '0' ) ).join( '' );
        
        $( '#api_key' ).val( key );
        $( '#helpdocs_api_key_display' ).removeClass( 'no-key' ).addClass( 'has-key' ).text( key );
        $( '.helpdocs-copy-api-key, .helpdocs-clear-api-key' ).prop( 'disabled', false );
    } );

    $( '.helpdocs-copy-api-key' ).on( 'click', function() {
        const key = $( '#api_key' ).val();
        if ( key ) {
            navigator.clipboard.writeText( key );
            const $btn = $( this );
            const originalText = $btn.text();
            $btn.text( 'Copied!' );
            setTimeout( () => $btn.text( originalText ), 2000 );
        }
    } );

    $( '.helpdocs-clear-api-key' ).on( 'click', function() {
        if ( confirm( 'Are you sure you want to clear the API key? This may break existing imports on other sites.' ) ) {
            $( '#api_key' ).val( '' );
            $( '#helpdocs_api_key_display' ).removeClass( 'has-key' ).addClass( 'no-key' ).html( '<em>No API Key Generated</em>' );
            $( this ).prop( 'disabled', true );
            $( '.helpdocs-copy-api-key' ).prop( 'disabled', true );
        }
    } );

    // --- FLUSH CACHE ---
    jQuery( document ).on( 'click', '#helpdocs-flush-cache', function( e ) {
        e.preventDefault();

        const $button = jQuery( this );
        const originalText = $button.text();

        $button.text( helpdocs_settings.flushing_text ).addClass( 'updating-message thinking' ).prop( 'disabled', true );

        jQuery.post( ajaxurl, {
            action: 'helpdocs_flush_cache',
            nonce: helpdocs_settings.nonce
        }, function( response ) {
            if ( response.success ) {
                alert( response.data );
            } else {
                alert( 'Error: ' + response.data );
            }
            $button.text( originalText ).removeClass( 'updating-message thinking' ).prop( 'disabled', false );
        } );
    } );

} );