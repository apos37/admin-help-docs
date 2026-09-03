jQuery( function ( $ ) {

    $( document ).on( 'click', '.helpdocs-migrate-notice .notice-dismiss', function () {
        var key = $( this ).closest( '.helpdocs-migrate-notice' ).data( 'source-key' );

        $.post( ajaxurl, {
            action: 'helpdocs_dismiss_migrate_notice',
            nonce: helpdocs_migrate_notice.nonce,
            source_key: key
        } );
    } );

} );