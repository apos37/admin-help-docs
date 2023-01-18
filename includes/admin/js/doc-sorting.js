jQuery( $ => {
    $( "#draggable-items" ).sortable( {
        cursor: 'move',
        update: function() {
            var draggable = jQuery( '#draggable-items' );
            var order = draggable.sortable( 'serialize' );
            let nonce = draggable.data( "nonce" );
            if ( nonce ) {
                var args = {
                    type: "POST",
                    dataType: "json",
                    url: docSortingAjax.ajaxurl,
                    data: { action: "helpdocs_update_order", order : order, nonce: nonce },
                    success: function( response ) {
                        if ( response.type == "success" ) {
                            console.log( "The documentation order has been updated successfully!" );
                        } else {
                            console.log( "Oh no! The documentation order was not saved, sorry." );
                        }
                    },
                };
                jQuery.ajax( args );
            }
        }
    } );
    $( "#draggable-items" ).disableSelection();
} )