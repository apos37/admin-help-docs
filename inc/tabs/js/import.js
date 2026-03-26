jQuery( document ).ready( function( $ ) {
    
    // Dirty state tracking
    const saveReminder = $( '#helpdocs-save-reminder' );
    let isDirty = false;

    function markDirty() {
        if ( ! isDirty ) {
            isDirty = true;
            saveReminder.fadeIn( 150 );
        }
    }

    $( window ).on( 'beforeunload', function( e ) {
        if( isDirty ){
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    $( document ).on( 'change input', '#helpdocs_import_active, #helpdocs_import_title, #helpdocs_website_url, #helpdocs_imports_table input[type="checkbox"]', function() {
        markDirty();
    });

    // Hide the "Don't forget to activate" notice when the toggle is changed
    $( '#helpdocs_import_active' ).on( 'change', function() {
        const isActive = $( this ).is( ':checked' );
        $( '#helpdocs-inactive-notice' ).toggle( ! isActive );
    } );

    // Saving
    const saveButton = $( '#header_btn_save_import_settings' );
    const urlInput = $( '#helpdocs_website_url' );

    function validateSave() {
        if ( urlInput.val().trim() === '' ) {
            saveButton.prop( 'disabled', true );
        } else {
            saveButton.prop( 'disabled', false );
        }
    }

    validateSave();

    urlInput.on( 'input change', function() {
        validateSave();
    });

    function startSavingTitle() {
        let dots = 0;

        document.title = helpdocs_import.saving_text; // immediate first update

        setInterval( function() {
            dots = (dots + 1) % 4; // cycles 0 → 3
            document.title = helpdocs_import.saving_text + '.'.repeat(dots);
        }, 500 );
    }

    function showSaving() {
        saveButton.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update spin"></span> ' + helpdocs_import.saving_text + '...' );
    }

    function saveSettings() {
        if ( saveButton.prop( 'disabled' ) ) {
            return;
        }
        
        startSavingTitle();
        showSaving();
        isDirty = false;
        $( '#helpdocs_import_form' ).submit();
    }

    saveButton.on( 'click', saveSettings );

    $( document ).on( 'keydown', function( e ) {
        if ( ( e.ctrlKey || e.metaKey ) && e.key.toLowerCase() === 's' ) {
            e.preventDefault();
            saveSettings();
        }
    } );

    const successMsg = $( '#helpdocs-saved-success' );
    if ( successMsg.is( ':visible' ) ) {
        setTimeout( function() {
            successMsg.fadeOut( 500 );
        }, 5000 );

        const url = new URL( window.location.href );
        if ( url.searchParams.has( 'import-updated' ) ) {
            url.searchParams.delete( 'import-updated' );
            window.history.replaceState( {}, document.title, url.pathname + url.search + url.hash );
        }
    }

    // Select All Buttons
    $( '.helpdocs-select-all-toggle' ).on( 'click', function( e ) {
        e.preventDefault();
        const type = $( this ).data( 'type' ); // 'feed' or 'toc'
        const checkboxes = $( '.' + type + '-checkbox' );
        
        // Determine if we should check all or uncheck all
        const anyUnchecked = checkboxes.filter( ':not(:checked)' ).length > 0;
        
        checkboxes.prop( 'checked', anyUnchecked );
        markDirty();
    } );

    // "Feed All" and "TOC All" Logic
    const feedAll     = $( '#helpdocs_all' );
    const tocAll      = $( '#helpdocs_all_tocs' );
    const tocAllWrap  = $( '#helpdocs_all_tocs_container' );
    const table       = $( '#helpdocs_imports_table' );

    function syncTableState() {
        const isFeedAllChecked = feedAll.is( ':checked' );
        const isTocAllChecked  = tocAll.is( ':checked' );
        const feedCheckboxes   = table.find( '.feed-checkbox' );
        const tocCheckboxes    = table.find( '.toc-checkbox' );

        if ( isFeedAllChecked ) {
            feedCheckboxes.each( function() {
                const cb = $( this );
                if ( undefined === cb.data( 'prev-state' ) ) {
                    cb.data( 'prev-state', cb.is( ':checked' ) );
                }
            } );

            tocAllWrap.css( 'display', 'inline-flex' );
            feedCheckboxes.prop( 'checked', true ).prop( 'disabled', true );
            $( '.helpdocs-select-all-toggle' ).prop( 'disabled', true ).css( 'opacity', '0.5' );

            if ( isTocAllChecked ) {
                tocCheckboxes.prop( 'checked', true ).prop( 'disabled', true );
            } else {
                tocCheckboxes.prop( 'disabled', true );
            }
        } else {
            tocAllWrap.hide();
            $( '.helpdocs-select-all-toggle' ).prop( 'disabled', false ).css( 'opacity', '1' );

            feedCheckboxes.each( function() {
                const cb = $( this );
                const prev = cb.data( 'prev-state' );
                if ( undefined !== prev ) {
                    cb.prop( 'checked', prev );
                }
                cb.prop( 'disabled', false );
            } );

            feedCheckboxes.removeData( 'prev-state' );
            tocCheckboxes.prop( 'disabled', false );
        }
    }

    syncTableState();

    feedAll.on( 'change', function() {
        syncTableState();
    } );

    tocAll.on( 'change', function() {
        if ( ! $( this ).is( ':checked' ) && feedAll.is( ':checked' ) ) {
            table.find( '.toc-checkbox' ).prop( 'checked', false );
        }
        syncTableState();
    } );

    // Trigger "Fetch" on Enter key in the URL field
    $( '#helpdocs_website_url' ).on( 'keydown', function( e ) {
        if ( e.key === 'Enter' ) {
            e.preventDefault(); // Stop the form from saving
            $( '#helpdocs_fetch_remote_docs' ).trigger( 'click' );
        }
    } );

    // Toggle API Key Visibility
    $( document ).on( 'click', '.helpdocs-toggle-visibility', function() {
        const $input = $( '#helpdocs_api_key' );
        const type = $input.attr( 'type' ) === 'password' ? 'text' : 'password';
        $input.attr( 'type', type );
        $( this ).toggleClass( 'dashicons-visibility dashicons-hidden' );
    } );

    // Fetch Remote Docs
    $( '#helpdocs_fetch_remote_docs' ).on( 'click', function( e ) {
        e.preventDefault();
        
        const $btn = $( this );
        const originalText = $btn.text();
        let url = $( '#helpdocs_website_url' ).val().trim();
        const apiKey = $( '#helpdocs_api_key' ).val().trim();

        if ( ! url ) {
            alert( 'Please enter a URL first.' );
            return;
        }

        if ( ! /^https?:\/\//i.test( url ) ) {
            url = 'https://' + url;
            $( '#helpdocs_website_url' ).val( url ); 
        }

        // Reset UI state before fetching
        $btn.addClass( 'disabled thinking' ).text( helpdocs_import.fetching_text );
        $( '#helpdocs_api_error, #helpdocs_connection_error, #helpdocs_no_docs_found' ).hide();

        $.post( ajaxurl, {
            action: 'helpdocs_fetch_remote_docs',
            nonce: helpdocs_import.fetch_nonce,
            url: url,
            api_key: apiKey // Pass the key to PHP
        }, function( response ) {
            if ( response.success ) {
                // Handle specific error codes returned in response.data
                if ( response.data.error === 'unauthorized' ) {
                    $( '#helpdocs_api_error' ).fadeIn();
                    $( '#helpdocs_remote_docs_wrapper' ).hide();
                } else if ( response.data.error === 'connection_failed' ) {
                    $( '#helpdocs_connection_error' ).fadeIn();
                    $( '#helpdocs_remote_docs_wrapper' ).hide();
                } else {
                    // Success: Update the table
                    $( '#helpdocs_imports_table tbody' ).html( response.data.html );
                    
                    const count = response.data.count;
                    const itemText = count === 1 ? ' item' : ' items';
                    $( '.displaying-num' ).text( count + itemText );
                    
                    const $notice = $( '#helpdocs_version_notice' );
                    if ( response.data.version === 'v1' ) {
                        $notice.fadeIn();
                    } else {
                        $notice.hide();
                    }
                    
                    $( '#helpdocs_remote_docs_wrapper' ).fadeIn();
                }
            } else {
                // General failure (no docs or server error)
                $( '#helpdocs_remote_docs_wrapper' ).hide();
                $( '#helpdocs_no_docs_found' ).fadeIn();
                
                if ( response.data !== 'No documents found at this URL.' && ! response.data.error ) {
                    alert( response.data );
                }
            }
            $btn.removeClass( 'disabled thinking' ).text( originalText );
        } );
    } );

    // Import Individual Docs
    $( document ).on( 'click', '.helpdocs-clone-individual', function( e ) {
        e.preventDefault();
        const $btn = $( this );
        const originalText = $btn.text();
        const docId = $btn.data( 'id' );
        const importId = $( '#helpdocs_imports_table' ).data( 'import-id' );
        const websiteUrl = $( '#helpdocs_website_url' ).val();
        const apiKey = $( '#helpdocs_api_key' ).val().trim();

        if ( $btn.hasClass( 'updating-message' ) ) return;

        $btn.addClass( 'updating-message' ).addClass( 'thinking' ).text( helpdocs_import.importing_text );

        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'helpdocs_import_individual_doc',
                nonce: helpdocs_import.clone_nonce,
                doc_id: docId,
                import_id: importId,
                website_url: websiteUrl,
                api_key: apiKey
            },
            success: function( response ) {
                if ( response.success ) {
                    $btn.removeClass( 'button-secondary updating-message thinking' )
                        .addClass( 'button-disabled' )
                        .text( helpdocs_import.imported_text )
                        .prop( 'disabled', true );
                } else {
                    alert( response.data || helpdocs_import.error_text );
                    $btn.removeClass( 'updating-message thinking' ).text( originalText );
                }
            },
            error: function( xhr, status, error ) {
                console.error( xhr.responseText );
                alert( helpdocs_import.error_text + ' (Status: ' + status + ')' );
                $btn.removeClass( 'updating-message thinking' ).text( originalText );
            }
        } );
    } );

} );