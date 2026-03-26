jQuery( document ).ready( function( $ ) {

    $( document ).on( 'click', '.helpdocs-status-toggle', function() {
        const $btn = $( this );
        const post_id = $btn.data( 'id' );

        if ( $btn.hasClass( 'updating' ) ) {
            return;
        }

        $btn.addClass( 'updating' );

        $.post( ajaxurl, {
            action: 'helpdocs_toggle_import_status',
            nonce: helpdocs_help_doc_imports.nonce,
            id: post_id
        }, function( response ) {
            if ( response.success ) {
                if ( response.data.active ) {
                    $btn.removeClass( 'is-inactive' )
                        .addClass( 'is-active' )
                        .find( '.helpdocs-post-status' )
                        .text( helpdocs_help_doc_imports.active_text );

                    $btn.addClass( 'helpdocs-success-pulse' );
                    
                    setTimeout( function() {
                        $btn.removeClass( 'helpdocs-success-pulse' );
                    }, 500 );

                } else {
                    $btn.removeClass( 'is-active' )
                        .addClass( 'is-inactive' )
                        .find( '.helpdocs-post-status' )
                        .text( helpdocs_help_doc_imports.inactive_text );
                }
            } else {
                alert( response.data );
            }
            
            $btn.removeClass( 'updating' );
        } );
    } );

} );