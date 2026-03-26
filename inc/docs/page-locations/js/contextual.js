jQuery( document ).ready( function( $ ) {
    const data = typeof helpdocs_contextual !== 'undefined' ? helpdocs_contextual : {};
    const docs = data.docs || [];

    if ( docs.length === 0 ) return;

    // Use an interval to find the toolbar since Gutenberg header renders dynamically
    const findGutenbergHeader = setInterval( function() {
        const $toolbarSettings = $( '.edit-post-header__settings, .editor-header__settings' );

        if ( $toolbarSettings.length ) {
            clearInterval( findGutenbergHeader );
            initHelpPopover( $toolbarSettings );
        }
    }, 500 );

    function initHelpPopover( $toolbarSettings ) {
        // 1. Create the Single "Help" Button
        const $button = $( '<button>', {
            type: 'button',
            'aria-pressed': 'false',
            id: 'helpdocs_main_help_btn',
            class: 'components-button is-primary helpdocs-contextual-btn',
            html: 'Help',
            css: { marginRight: '8px' }
        } );

        $toolbarSettings.prepend( $button );

        // 2. Create the Popover Slot
        const $slot = $( '<div>', { class: 'popover-slot' } );
        $( '.edit-post-header, .editor-header' ).append( $slot );

        // 3. Create the Popover Container with Animation Styles
        const $container = $( '<div>', {
            id: 'helpdocs_main_popover',
            class: 'components-popover components-dropdown__content helpdocs_popover',
            css: {
                position: 'absolute',
                right: '300px',
                top: '60px',
                zIndex: '100000',
                opacity: '1',
                transform: 'translateY(0em) scale(0) translateZ(0px)',
                transformOrigin: 'top center 0px',
                transitionTimingFunction: 'ease-in',
                transition: '0.1s',
                display: 'none'
            }
        } );

        // 4. Create Inner Wrapper (for the multiple docs)
        const $innerContent = $( '<div>', {
            class: 'components-popover__content',
            css: {
                width: '600px',
                maxHeight: '80vh',
                overflow: 'auto',
                backgroundColor: '#fff',
                color: '#000',
                padding: '16px',
                border: '1px solid #ccc',
                boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
            }
        } );

        // Loop through docs and append to inner wrapper
        $.each( docs, function( index, doc ) {
            const $docWrapper = $( '<div id="helpdocs_doc_' + doc.id + '">' );
            if ( index > 0 ) {
                $docWrapper.css( {
                    marginTop: '20px',
                    paddingTop: '20px',
                    borderTop: '1px solid #eee'
                } );
            }

            const $titleEl = $( '<div>', {
                css: { fontSize: '1.2rem', fontWeight: 'bold', marginBottom: '10px' },
                html: doc.title
            } );

            const $contentEl = $( '<div>', { html: doc.content } );

            $docWrapper.append( $titleEl ).append( $contentEl );
            $innerContent.append( $docWrapper );
        } );

        $container.append( $innerContent );

        // 5. Toggle Logic with Animation
        $button.on( 'click', function( e ) {
            e.stopPropagation();

            if ( $button.attr( 'aria-pressed' ) === 'false' ) {
                // Show
                $container.css( 'display', 'block' );
                $slot.append( $container );

                // Trigger animation frame
                setTimeout( function() {
                    $container.css( 'transform', 'translateY(0em) scale(1) translateZ(0px)' );
                }, 10 );

                $button.attr( 'aria-pressed', 'true' );
            } else {
                // Hide
                $container.css( 'transform', 'translateY(0em) scale(0) translateZ(0px)' );
                setTimeout( function() {
                    $container.hide().detach();
                }, 100 );

                $button.attr( 'aria-pressed', 'false' );
            }
        } );

        // 6. Close on outside click
        $( document ).on( 'click', function( e ) {
            if ( $button.attr( 'aria-pressed' ) === 'true' ) {
                if ( ! $( e.target ).closest( '#helpdocs_main_popover' ).length && ! $( e.target ).is( $button ) ) {
                    $button.trigger( 'click' );
                }
            }
        } );
    }
} );