jQuery( document ).ready( function( $ ) {
    const data = typeof helpdocs_element !== 'undefined' ? helpdocs_element : {};
    const docs = data.docs || [];
    const isGutenberg = data.is_gutenberg || false;

    if ( docs.length === 0 ) return;

    /**
     * The Injection Logic
     */
    const injectElementDocs = function() {
        docs.forEach( function( doc ) {
            if ( ! doc.css_selector ) return;

            const $target = $( doc.css_selector );
            
            if ( $target.length ) {
                // Unique class to prevent the interval from double-injecting
                const docIdClass = 'helpdocs-el-' + doc.ID;
                
                if ( ! $target.next( '.' + docIdClass ).length ) {
                    const html = '<div class="helpdocs-element-item ' + docIdClass + '">' + doc.content + '</div>';
                    $target.after( html );
                }
            }
        } );
    };

    // Initial run for everyone
    injectElementDocs();

    // If we are in Gutenberg, we need to poll because elements 
    // like .editor-document-bar load after the DOM is "ready"
    if ( isGutenberg ) {
        setInterval( function() {
            injectElementDocs();
        }, 1000 );
    }
} );