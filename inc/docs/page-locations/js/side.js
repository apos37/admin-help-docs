jQuery( document ).ready( function( $ ) {
    const data = typeof helpdocs_side !== 'undefined' ? helpdocs_side : {};
    const docs = data.docs || [];
    const template = data.template || '';

    if ( docs.length === 0 ) return;

    /**
     * Gutenberg Sidebar Injection
     */
    const renderGutenbergSideDocs = function() {
        // Targets the settings sidebar (Inspector)
        const $target = $( '.interface-complementary-area' );
        
        if ( $target.length && docs.length && template ) {
            // Prevent duplicate injections
            if ( $target.find( '.helpdocs-gutenberg-side' ).length ) return;

            let html = '<div class="helpdocs-side-wrapper helpdocs-gutenberg-side">';
            
            $.each( docs, function( index, doc ) {
                let docHtml = template
                    .replace( '{doc_title}', doc.title )
                    .replace( '{doc_content}', doc.content );
                
                html += docHtml;
            } );
            
            html += '</div>';
            
            // Prepend to the top of the sidebar
            $target.prepend( html );
        }
    };

    // Gutenberg sidebars unmount/remount when toggled, so we monitor it
    const checkGutenbergSide = setInterval( function() {
        if ( $( '.interface-complementary-area' ).length ) {
            renderGutenbergSideDocs();
        }
    }, 1000 );
} );