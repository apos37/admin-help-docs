jQuery( $ => {
    // Store the placeholder index here
    var placeholderIndex = 0;

    // Sorting
    $( '#draggable-items' ).sortable( {
        delay: 150,
        axis: 'y',
        cursor: 'move',
        helper: 'clone',
        start: function ( event, ui ) {

            // Collapse the folder if we're moving one
            if ( ui.item.hasClass( 'toc-folder' ) ) {

                // Cancel folder 0
                $( '#draggable-items' ).sortable( 'option', 'cancel', '.invisible-folder' );

                // Close all folders
                $( `.toc-item` ).each( function() {
                    if ( $( this ).data( 'folder' ) > 0 ) {
                        $( this ).slideUp( 'fast' );
                    }
                } );
            }
        },
        change: function( event, ui ) {

            // Save the placeholder index since it gets deleted before stop event can capture it
            placeholderIndex = ui.placeholder.index();
        },
        update: function( event, ui ) {

            // Prevent folders from moving below the fold
            if ( ui.item.hasClass( 'toc-folder' ) && placeholderIndex > $( '#folder-0' ).index() ) {
                console.log( 'Attempt to move folders into non-folder area has failed. Please try again.' );
                $( this ).sortable( 'cancel' );

            // Otherwise we can continue updating
            } else {

                // Get the data
                const draggable = $( '#draggable-items' );
                const order = draggable.sortable( 'serialize' );
                const nonce = draggable.data( 'nonce' );
                const itemID = ui.item.attr( 'id' );

                // Validate nonce
                if ( nonce ) {

                    // Set the ajax args
                    const args = {
                        type: 'POST',
                        dataType: 'json',
                        url: docSortingAjax.ajaxurl,
                        data: { 
                            action: 'helpdocs_update_order', 
                            order : order, 
                            nonce: nonce, 
                            item_id: itemID 
                        },
                        success: function( response ) {
                            if ( response.type == 'success' ) {

                                // Get the folders
                                const newFolder = response.folder;
                                const oldFolder = $( ui.item ).data( 'folder' );

                                // Updating the docs
                                if ( response.updating == 'doc' ) {
                                    console.log( 'The documentation order has been updated successfully!' );

                                    // Update the folder count 
                                    if ( oldFolder != newFolder ) {

                                        // Update the old folder
                                        if ( oldFolder > 0 ) {
                                            var oldCount = $( `#folder-${oldFolder} .folder-count` ).text();
                                            oldCount--;
                                            $( `#folder-${oldFolder} .folder-count` ).text( oldCount );
                                        }
                                        
                                        // Update the new folder
                                        if ( newFolder > 0 ) {
                                            var newCount = $( `#folder-${newFolder} .folder-count` ).text();
                                            newCount++;
                                            $( `#folder-${newFolder} .folder-count` ).text( newCount );
                                        }
                                    }

                                    // Update the file
                                    if ( newFolder > 0 ) {
                                        $( ui.item )
                                            .removeClass( 'not-in-folder' )
                                            .addClass( 'in-folder' )
                                            .data( 'folder', newFolder )
                                            .attr( 'data-folder', newFolder );
                                    } else {
                                        $( ui.item )
                                            .removeClass( 'in-folder' )
                                            .addClass( 'not-in-folder' )
                                            .data( 'folder', 0 )
                                            .attr( 'data-folder', 0 );
                                    }         

                                // Updating folders
                                } else {
                                    console.log( 'The folder order has been updated successfully!' );

                                    // Move any other docs into the correct positions
                                    $( $( `.toc-item` ).get().reverse() ).each( function() {

                                        // Don't include items in folder 0
                                        if ( $( this ).data( 'folder' ) > 0 ) {

                                            // Get the folder it should be under
                                            const thisFolder = $( this ).data( 'folder' );

                                            // Move it
                                            $( this ).detach().insertAfter( $( `#folder-${thisFolder}` ) );
                                        }
                                    } );
                                }

                            // Failure
                            } else {
                                console.log( 'Oh no! The documentation order was not saved, sorry.' );
                            }
                        },
                    };

                    // Fire the ajax
                    $.ajax( args );
                }
            }
            
        }
    } );
    $( '#draggable-items' ).disableSelection();

    // Open and close folders
    $( '.toc-folder a' ).on( 'click', function( e ) {
        e.preventDefault();
        
        // The folder
        const folder = $( this ).parent().data( 'folder' );

        // Check if the folder is open
        if ( $( this ).parent().hasClass( 'hide-in-folder' ) ) {
            $( `.toc-item[data-folder="${folder}"]` ).slideDown( 'fast' );
            $( this ).parent().removeClass( 'hide-in-folder' ).addClass( 'active-folder' );
        } else {
            $( `.toc-item[data-folder="${folder}"]` ).slideUp( 'fast' );
            $( this ).parent().removeClass( 'active-folder' ).addClass( 'hide-in-folder' );
        }
    } );

    // Expand all folders
    $( '#expand-all' ).on( 'click', function( e ) {
        $( `.toc-item` ).each( function() {
            if ( $( this ).data( 'folder' ) > 0 ) {
                $( `.toc-item` ).slideDown( 'fast' );
                $( this ).parent().removeClass( 'hide-in-folder' ).addClass( 'active-folder' );
            }
        } );
    } );

    // Collapse all folders
    $( '#collapse-all' ).on( 'click', function( e ) {
        $( `.toc-item` ).each( function() {
            if ( $( this ).data( 'folder' ) > 0 ) {
                $( this ).slideUp( 'fast' );
                $( this ).parent().removeClass( 'active-folder' ).addClass( 'hide-in-folder' );
            }
        } );
    } );
} )