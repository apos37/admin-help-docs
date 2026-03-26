jQuery( document ).ready( function( $ ) {
    const data = typeof helpdocs_top !== 'undefined' ? helpdocs_top : {};
    const docs = data.docs || [];
    const template = data.template || '';

    if ( docs.length === 0 ) return;

    /**
     * Gutenberg Injection logic
     */
    const renderGutenbergTopDocs = function() {
        const $target = $( '.interface-interface-skeleton__content' );
        
        if ( $target.length && docs.length && template ) {
            // Prevent duplicate injection
            if ( $target.find( '.helpdocs-gutenberg-top' ).length ) return;

            let html = '<div class="helpdocs-top-wrapper helpdocs-gutenberg-top wrap">';
            
            $.each( docs, function( index, doc ) {
                let docHtml = template
                    .replace( '{doc_title}', doc.title )
                    .replace( '{doc_content}', doc.content );
                
                html += docHtml;
            } );
            
            html += '</div>';
            
            $target.prepend( html );
        }
    };

    const checkGutenberg = setInterval( function() {
        if ( $( '.interface-interface-skeleton__content' ).length ) {
            renderGutenbergTopDocs();
            clearInterval( checkGutenberg );
        }
    }, 500 );
} );