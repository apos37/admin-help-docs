jQuery( $ => {
    console.log( '%c Editing Help Doc... ', 'background: #222; color: #bada55' );

    // Get the elements
    const siteLocationInput = document.getElementById( 'doc-site-location-select' );
    const siteLocationInputValue = atob( siteLocationInput.value );
    const order = document.getElementById( 'doc-order' );
    const toc = document.getElementById( 'doc-toc' );
    const pageLocation = document.getElementById( 'doc-page-location' );
    const pageLocationInput = document.getElementById( 'doc-page-location-select' );
    const custom = document.getElementById( 'doc-custom' );
    const addtParams = document.getElementById( 'doc-addt-params' );
    const optionSide = document.querySelector( 'option.lop-option-side' );
    const priority = document.getElementById( 'doc-priority' );
    const postType = document.getElementById( 'doc-post-types' );

    // Check if the site location is NOT main, admin_bar or dashboard
    if ( siteLocationInputValue != '' && siteLocationInputValue != 'main' && siteLocationInputValue != 'admin_bar' && siteLocationInputValue != 'index.php' ) {

        // Display the page location
        pageLocation.style.display = 'inline-block';
    }

    // Check if the site location is main or admin_bar
    if ( siteLocationInputValue != '' && ( siteLocationInputValue == 'main' || siteLocationInputValue == 'admin_bar' ) ) {

        // Display the page location
        order.style.display = 'inline-block';

        // Display the toc option
        toc.style.display = 'inline-block';
    }

    // Check if the site location is edit or post
    if ( siteLocationInputValue == 'edit.php' || siteLocationInputValue == 'post.php' ) {
        postType.style.display = 'block';
    } else {
        postType.style.display = 'none';
    }

    // Check if the page location is side
    if ( pageLocationInput.value == 'side' ) {
        priority.style.display = 'inline-block';
    } else {
        priority.style.display = 'none';
    }

    // Check if the site location is custom page url
    if ( siteLocationInputValue == 'custom' ) {
        custom.style.display = 'inline-block';
        addtParams.style.display = 'inline-block';
    } else {
        custom.style.display = 'none';
        addtParams.style.display = 'none';
    }

    // Also listen for changes
    siteLocationInput.addEventListener( 'change', function () {

        // Decode
        siteLocationValue = atob( this.value );

        // Page Location container
        if ( siteLocationValue != '' && siteLocationValue != 'main' && siteLocationValue != 'admin_bar' && siteLocationValue != 'index.php' ) {
            pageLocation.style.display = 'inline-block';
        } else {
            pageLocation.style.display = 'none';
        }

        // Order container
        if ( siteLocationValue != '' && ( siteLocationValue == 'main' || siteLocationValue == 'admin_bar' ) ) {
            order.style.display = 'inline-block';
        } else {
            order.style.display = 'none';
        }

        // TOC container
        if ( siteLocationValue != '' && siteLocationValue == 'main' ) {
            toc.style.display = 'inline-block';
        } else {
            toc.style.display = 'none';
        }

        // Post Type container
        if ( siteLocationValue == 'edit.php' || siteLocationValue == 'post.php' ) {
            postType.style.display = 'block';
        } else {
            postType.style.display = 'none';
        }

        // Change page location to top
        pageLocationInput.value = 'top';

        // 'side' option
        if ( siteLocationValue == 'post.php' ) {
            optionSide.style.display = 'block';
        } else {
            optionSide.style.display = 'none';
        }

        // Check if the page location is side
        if ( siteLocationValue != 'post.php' ) {
            priority.style.display = 'none';
        }

        if ( siteLocationValue == 'custom' ) {
            custom.style.display = 'inline-block';
            addtParams.style.display = 'inline-block';
        } else {
            custom.style.display = 'none';
            addtParams.style.display = 'none';
        }
    } );

    // Side => Priority
    pageLocationInput.addEventListener( 'change', function () {
        if ( this.value == 'side' ) {
            priority.style.display = 'inline-block';
        } else {
            priority.style.display = 'none';
        }
    } );

    // Folders on load
    convertFoldersToRadio();

    // Folders on making a new folder
    $( '#help-docs-folder-add-submit' ).on( 'click', function() {
        setTimeout( function() {
            convertFoldersToRadio();
        }, 1000 );
    } );

    // Convert folder checkboxes to radio buttons
    function convertFoldersToRadio() {
        const folderCheckBoxesId = 'help-docs-folderchecklist';
        $( `#${folderCheckBoxesId} input` ).each( function() {
            this.type = "radio";
            $( `#${folderCheckBoxesId} li` ).addClass( 'visible' );
        } );
    }
} )