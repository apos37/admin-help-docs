jQuery( $ => {

    // Sortable Help Docs Admin Menu
    var sorter = $( '.helpdocs-sorter' );

    if ( ! sorter.length ) {
        return;
    }

    sorter.sortable( {
        handle: '.helpdocs-sort-handle',
        axis: 'y',
        update: function () {
            $( '#save-reminder' ).fadeIn();
        }
    } );

    // Colorize Separators
    function updateSeparatorColor() {
        var enabled = $( '#helpdocs_colorize_separators' ).is( ':checked' );
        var color = $( '#helpdocs_color_admin_menu_sep' ).val();

        if ( enabled ) {
            $( 'body' ).addClass( 'helpdocs-separator-enabled' );
            $( '#adminmenu div.separator' ).css( 'border-top-color', color );
        } else {
            $( 'body' ).removeClass( 'helpdocs-separator-enabled' );
        }
    }

    // Listen for changes
    $( '#helpdocs_colorize_separators, #helpdocs_color_admin_menu_sep' ).on( 'change input', function() {
        updateSeparatorColor();
        console.log( 'Separator color updated.' );
    } );

} )