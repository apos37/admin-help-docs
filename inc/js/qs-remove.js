jQuery( document ).ready( function( $ ) {
    if ( typeof helpdocs_remove_qs !== 'undefined' && helpdocs_remove_qs && helpdocs_remove_qs.title !== '' ) {
        if ( history.pushState ) {
            let obj = { Title: helpdocs_remove_qs.title, Url: helpdocs_remove_qs.url };
            window.history.pushState( obj, obj.Title, obj.Url );
        }
    }
} );