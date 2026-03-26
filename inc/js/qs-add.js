jQuery( document ).ready( function( $ ) {
    if ( typeof helpdocs_add_qs !== 'undefined' && helpdocs_add_qs && helpdocs_add_qs.title !== '' ) {
        if ( history.pushState ) {
            let obj = { Title: helpdocs_add_qs.title, Url: helpdocs_add_qs.url };
            window.history.pushState( obj, obj.Title, obj.Url );
        }
    }
} );