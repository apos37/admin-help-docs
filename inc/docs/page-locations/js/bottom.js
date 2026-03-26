jQuery( document ).ready( function( $ ) {
    const data = typeof helpdocs_bottom !== 'undefined' ? helpdocs_bottom : {};
    const isGutenberg = data.is_gutenberg || false;
    const docs = data.docs || [];
    const template = data.template || '';
    
    // Target the class instead of ID
    const $wrappers = $( '.helpdocs-bottom-wrapper' );

    if ( isGutenberg ) {
        const renderGutenbergDocs = function() {
            const $target = $( '.interface-interface-skeleton__content' );
            
            if ( $target.length && docs.length && template ) {
                // Check if we've already injected to prevent duplicates
                if ( $target.find( '.helpdocs-gutenberg-bottom' ).length ) return;

                let html = '<div class="helpdocs-bottom-wrapper helpdocs-gutenberg-bottom">';
                
                docs.forEach( function( doc ) {
                    let docHtml = template
                        .replace( '{doc_title}', doc.title )
                        .replace( '{doc_content}', doc.content );
                    
                    html += docHtml;
                } );
                
                html += '</div>';
                $target.append( html );
            }
        };

        const checkGutenberg = setInterval( function() {
            if ( $( '.interface-interface-skeleton__content' ).length ) {
                renderGutenbergDocs();
                clearInterval( checkGutenberg );
            }
        }, 500 );

    } else {
        const $target = $( '#wpbody-content' );
        
        // Loop through all found wrappers and move them
        if ( $wrappers.length && $target.length ) {
            $wrappers.each( function() {
                $( this ).detach().appendTo( $target ).show();
            });
        }
    }
} );