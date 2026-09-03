jQuery( function ( $ ) {

    var appEl = $( '#helpdocs-migrate-app' );
    if ( ! appEl.length ) {
        return;
    }

    var sourceSelect = $( '#helpdocs-migrate-source' );
    var listEl = $( '#helpdocs-migrate-list' );
    var actionsRow = $( '#helpdocs-migrate-actions-row' );
    var selectAllBtn = $( '#helpdocs-migrate-select-all' );
    var submitBtn = $( '#helpdocs-migrate-submit' );
    var statusEl = $( '#helpdocs-migrate-status' );
    var allSelected = false;
    var successTimeout = null;

    function loadPosts( sourcePostType, preserveStatus ) {
        listEl.html( '' );
        actionsRow.hide();
        submitBtn.prop( 'disabled', true );

        if ( ! preserveStatus ) {
            statusEl.html( '' );
        }

        if ( ! sourcePostType ) {
            sourceSelect.prop( 'disabled', false );
            return;
        }

        sourceSelect.prop( 'disabled', true );
        listEl.html( '<div class="spinner-row"><span class="spinner is-active" style="float:none;"></span> ' + helpdocs_migrate.i18n.loading + '</div>' );

        $.post( helpdocs_migrate.ajax_url, {
            action: 'helpdocs_migrate_list_posts',
            nonce: helpdocs_migrate.nonce,
            source_post_type: sourcePostType
        } ).done( function ( response ) {
            sourceSelect.prop( 'disabled', false );
            listEl.html( '' );

            if ( ! response.success || ! response.data.posts.length ) {
                listEl.html( '<p class="helpdocs-table-none">' + helpdocs_migrate.i18n.no_posts + '</p>' );
                return;
            }

            var table = $( '<table class="helpdocs-table"><tbody></tbody></table>' );
            var tbody = table.find( 'tbody' );

            response.data.posts.forEach( function ( post ) {
                var row = $( '<tr></tr>' ).toggleClass( 'is-imported', post.imported );
                var checkboxCell = $( '<td></td>' );
                var checkbox = $( '<input type="checkbox" class="helpdocs-migrate-checkbox">' )
                    .attr( 'value', post.id )
                    .prop( 'disabled', post.imported );

                checkboxCell.append( checkbox );

                var titleCell = $( '<td></td>' ).text( post.title );
                var statusCell = $( '<td class="helpdocs-migrate-status"></td>' );

                if ( post.imported ) {
                    statusCell.html( '<span class="helpdocs-migrate-badge">' + helpdocs_migrate.i18n.already_migrated + '</span>' );
                }

                row.append( checkboxCell ).append( titleCell ).append( statusCell );
                tbody.append( row );
            } );

            listEl.append( table );
            actionsRow.show();
            selectAllBtn.text( helpdocs_migrate.i18n.select_all );
            allSelected = false;
            updateSubmitState();
        } ).fail( function () {
            sourceSelect.prop( 'disabled', false );
            listEl.html( '<p class="helpdocs-error">' + helpdocs_migrate.i18n.error + '</p>' );
        } );
    } // End loadPosts()

    function updateSubmitState() {
        var checkedCount = listEl.find( '.helpdocs-migrate-checkbox:checked' ).length;
        submitBtn.prop( 'disabled', checkedCount === 0 );
    } // End updateSubmitState()

    sourceSelect.on( 'change', function () {
        loadPosts( $( this ).val(), false );
    } );

    listEl.on( 'change', '.helpdocs-migrate-checkbox', updateSubmitState );

    selectAllBtn.on( 'click', function () {
        allSelected = ! allSelected;
        listEl.find( '.helpdocs-migrate-checkbox:not(:disabled)' ).prop( 'checked', allSelected );
        selectAllBtn.text( allSelected ? helpdocs_migrate.i18n.deselect_all : helpdocs_migrate.i18n.select_all );
        updateSubmitState();
    } );

    submitBtn.on( 'click', function () {
        var sourcePostType = sourceSelect.val();
        var postIds = listEl.find( '.helpdocs-migrate-checkbox:checked' ).map( function () {
            return $( this ).val();
        } ).get();

        if ( ! sourcePostType || ! postIds.length ) {
            return;
        }

        submitBtn.prop( 'disabled', true );
        statusEl.html( '<span class="spinner is-active" style="float:none;"></span> ' + helpdocs_migrate.i18n.migrating );

        $.post( helpdocs_migrate.ajax_url, {
            action: 'helpdocs_migrate_import_posts',
            nonce: helpdocs_migrate.nonce,
            source_post_type: sourcePostType,
            post_ids: postIds
        } ).done( function ( response ) {
            if ( ! response.success ) {
                statusEl.html( '<span class="helpdocs-error">' + ( response.data && response.data.message ? response.data.message : 'Error' ) + '</span>' );
                return;
            }

            var message = helpdocs_migrate.i18n.done
                .replace( '%migrated%', response.data.imported )
                .replace( '%skipped%', response.data.skipped );

            var docsUrl = helpdocs_migrate.docs_url;
            if ( response.data.last_id ) {
                docsUrl += ( docsUrl.indexOf( '?' ) === -1 ? '?' : '&' ) + 'id=' + response.data.last_id;
            }

            statusEl.html( '<span class="helpdocs-migrate-success"><span class="dashicons dashicons-yes-alt"></span> ' + message + ' <a href="' + docsUrl + '">' + helpdocs_migrate.i18n.go_to_docs + '</a></span>' );

            loadPosts( sourcePostType, true );
        } );
    } );

    var preselect = appEl.data( 'preselect' );
    if ( preselect ) {
        sourceSelect.val( preselect );
        loadPosts( preselect, false );
    }

} );