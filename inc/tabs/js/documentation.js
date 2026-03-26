jQuery( document ).ready( function( $ ) {

    const list = $( '#helpdocs-docs-list' );
    let draggedEl = null;
    let draggedChildren = $();
    let placeholder = $( '<li class="drag-placeholder"></li>' );

    list.on( 'dragstart', 'li[draggable="true"]', function( e ) {
        draggedEl = $( this );
        const isFolder = draggedEl.data( 'type' ) === 'folder';

        if ( isFolder ) {
            list.find( 'li[data-type="folder"]' ).removeClass( 'active-folder' );
            refreshFolderVisibility();

            draggedChildren = list.find( 'li[data-type="item"][data-folder-id="' + draggedEl.data( 'folder-id' ) + '"]' );
            
            let totalHeight = draggedEl.outerHeight();
            draggedChildren.each( function() { totalHeight += $( this ).outerHeight(); } );
            placeholder.css( 'height', totalHeight + 'px' );
            placeholder.addClass( 'folder-placeholder' );
        } else {
            draggedChildren = $();
            placeholder.css( 'height', draggedEl.outerHeight() + 'px' );
            placeholder.removeClass( 'folder-placeholder' );
        }

        draggedEl.addClass( 'dragging' );
        e.originalEvent.dataTransfer.effectAllowed = 'move';
    } );

    list.on( 'dragover', function( e ) {
        e.preventDefault();
        
        const target = $( e.target ).closest( 'li' );
        if ( ! target.length || target.is( draggedEl ) || draggedChildren.is( target ) || target.is( placeholder ) ) {
            return;
        }

        const draggedType = draggedEl.data( 'type' );
        const targetType = target.data( 'type' );
        const divider = list.find( '.invisible-folder' );

        if ( draggedType === 'folder' ) {
            if ( divider.length && target.index() > divider.index() ) {
                return; 
            }

            if ( targetType === 'folder' || target.hasClass( 'invisible-folder' ) ) {
                const relY = e.originalEvent.pageY - target.offset().top;
                if ( relY < target.outerHeight() / 2 ) {
                    target.before( placeholder );
                } else {
                    if ( ! target.hasClass( 'invisible-folder' ) ) {
                        target.after( placeholder );
                    }
                }
            }
            return;
        }

        const relY = e.originalEvent.pageY - target.offset().top;
        if ( relY < target.outerHeight() / 2 ) {
            target.before( placeholder );
        } else {
            target.after( placeholder );
        }
    } );

    list.on( 'dragend', 'li', function() {
        $( this ).removeClass( 'dragging' );
        setTimeout( () => {
            placeholder.detach();
        }, 100 );
    } );

    list.on( 'drop', function( e ) {
        e.preventDefault();
        e.stopPropagation();

        if ( ! placeholder.parent().length ) return;

        const isFolder = draggedEl.data( 'type' ) === 'folder';

        if ( isFolder ) {
            placeholder.replaceWith( draggedEl );
            
            list.find( 'li[data-type="folder"]' ).each( function() {
                const currentFolder = $( this );
                const folderId = currentFolder.data( 'folder-id' );
                
                const folderItems = list.find( 'li[data-type="item"][data-folder-id="' + folderId + '"]' );
                
                let lastElement = currentFolder;
                folderItems.each( function() {
                    $( this ).insertAfter( lastElement );
                    lastElement = $( this );
                } );
            } );

            const divider = list.find( '.invisible-folder' );
            if ( divider.length ) {
                const noFolderItems = list.find( 'li[data-type="item"][data-folder-id="0"]' );
                let lastEl = divider;
                noFolderItems.each( function() {
                    $( this ).insertAfter( lastEl );
                    lastEl = $( this );
                } );
            }
        } else {
            placeholder.replaceWith( draggedEl );

            const divider = list.find( '.invisible-folder' );
            const prevFolder = draggedEl.prevAll( 'li[data-type="folder"]' ).first();
            
            const isBelowDivider = divider.length && draggedEl.index() > divider.index();

            if ( isBelowDivider || ! prevFolder.length ) {
                draggedEl.data( 'folder-id', 0 ).attr( 'data-folder-id', 0 );
                draggedEl.removeClass( 'in-folder' ).addClass( 'not-in-folder' );
            } else {
                const newId = prevFolder.data( 'folder-id' );
                draggedEl.data( 'folder-id', newId ).attr( 'data-folder-id', newId );
                draggedEl.removeClass( 'not-in-folder' ).addClass( 'in-folder' );
            }
        }

        updateFolderCounts();
        refreshFolderVisibility();
        saveOrder();
    } );

    function saveOrder() {
        const folders = [];
        const items = [];
        
        list.find( 'li[data-type="folder"]' ).each( function() {
            const fId = $( this ).data( 'folder-id' );
            if ( fId !== undefined ) {
                folders.push( fId );
            }
        } );

        list.find( 'li[data-type="item"]' ).each( function() {
            const $el = $( this );
            items.push( { 
                'id': $el.data( 'item-id' ), 
                'folder': $el.data( 'folder-id' ),
                'import_id': $el.data( 'import-id' ) || ''
            } );
        } );

        console.log( 'Saving order...', { items } );

        $.ajax( {
            url: ajaxurl,
            method: 'POST',
            data: {
                'action': 'helpdocs_save_docs_order',
                'folders': folders,
                'items': items,
                'nonce': helpdocs_documentation.nonce
            },
            success: function( response ) {
                if ( ! response.success ) {
                    console.error( 'Save failed:', response.data );
                }
            }
        } );
    }

    function updateFolderCounts() {
        list.find( 'li[data-type="folder"]' ).each( function() {
            const folder = $( this );
            const folderId = folder.data( 'folder-id' );
            const count = list.find( 'li[data-type="item"]' ).filter( function() {
                return $( this ).data( 'folder-id' ) === folderId;
            } ).length;
            folder.find( '.folder-count' ).text( count );
        } );
    }

    function getFolderItems( folder ) {
        const folderId = folder.data( 'folder-id' );
        return list.find( 'li[data-type="item"]' ).filter( function() {
            return $( this ).data( 'folder-id' ) === folderId;
        } );
    }

    function refreshFolderVisibility() {
        list.find( 'li[data-type="folder"]' ).each( function() {
            const folder = $( this );
            const folderItems = getFolderItems( folder );
            if ( folder.hasClass( 'active-folder' ) ) {
                folderItems.show();
            } else {
                folderItems.hide();
            }
        } );
    }

    const activeItem = $( '#helpdocs-docs-list .helpdocs-sidebar-item.active' );
    if ( activeItem.length ) {
        const folderId = activeItem.data( 'folder-id' );
        if ( folderId && folderId !== 0 && folderId !== '0' ) {
            const folder = $( '#folder-' + folderId );
            $( '.helpdocs-folder' ).removeClass( 'active-folder' ).addClass( 'hide-in-folder' );
            folder.addClass( 'active-folder' ).removeClass( 'hide-in-folder' );
        } else {
            $( '.helpdocs-folder' ).removeClass( 'active-folder' ).addClass( 'hide-in-folder' );
        }
    }

    $( '#helpdocs-docs-list' ).on( 'click', '.helpdocs-folder > a', function( e ) {
        e.preventDefault();
        const folder = $( this ).parent();
        folder.toggleClass( 'active-folder' );
        refreshFolderVisibility();
    } );

    $( '#expand-all' ).on( 'click', function( e ) {
        e.preventDefault();
        list.find( 'li[data-type="folder"]' ).addClass( 'active-folder' );
        refreshFolderVisibility();
    } );

    $( '#collapse-all' ).on( 'click', function( e ) {
        e.preventDefault();
        list.find( 'li[data-type="folder"]' ).removeClass( 'active-folder' );
        refreshFolderVisibility();
    } );

    // Show expand/collapse links if there are folders
    if ( list.find( 'li[data-type="folder"]' ).length ) {
        $( '#helpdocs-header-action-links' ).fadeIn( 'slow' );
    }

} );