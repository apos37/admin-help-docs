jQuery( document ).ready( function( $ ) {

    $( '.helpdocs-click-to-copy' ).on( 'click', function() {
        var $this = $( this );
        var textToCopy = $this.text().trim();

        // Modern Clipboard API
        if ( navigator.clipboard && window.isSecureContext ) {
            navigator.clipboard.writeText( textToCopy ).then( function() {
                showCopiedFeedback( $this );
            } ).catch( function( err ) {
                console.error( 'Unable to copy', err );
            } );
        } else {
            // Fallback for non-HTTPS or older browsers
            var $temp = $( '<textarea>' );
            $( 'body' ).append( $temp );
            $temp.val( textToCopy ).select();
            try {
                document.execCommand( 'copy' );
                showCopiedFeedback( $this );
            } catch ( err ) {
                console.error( 'Fallback copy failed', err );
            }
            $temp.remove();
        }
    });

    /**
     * Helper to show the "Copied!" feedback
     */
    function showCopiedFeedback( $el ) {
        if ( $el.find( '.helpdocs-copied-tip' ).length > 0 ) return;

        var $tip = $( '<span class="helpdocs-copied-tip">' + helpdocs_click_to_copy.copied_text + '</span>' );
        $el.append( $tip );

        $tip.fadeIn( 200 ).delay( 1500 ).fadeOut( 400, function() {
            $( this ).remove();
        } );
    }

});