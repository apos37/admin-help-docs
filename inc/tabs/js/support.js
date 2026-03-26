jQuery( document ).ready( function( $ ) {

    /**
     * Show/hide conditional fields
     */
    function updateConditionalFields() {
        $( '.helpdocs-field[data-condition-field]' ).each( function() {
            var $field         = $( this );
            var targetId       = $field.data( 'condition-field' );
            var expectedValue  = $field.data( 'condition-value' );
            var $target        = $( '#' + targetId );

            if ( $target.length ) {
                var isVisible = false;

                if ( $target.is( ':checkbox' ) ) {
                    var isChecked = $target.is( ':checked' ) ? '1' : '0';
                    isVisible = ( isChecked == expectedValue );
                } else {
                    isVisible = ( $target.val() == expectedValue );
                }

                if ( isVisible ) {
                    $field.removeClass( 'condition-hide' );
                } else {
                    $field.addClass( 'condition-hide' );
                }
            }
        } );
    }

    // Listen for changes on any field that has a condition tied to it
    $( document ).on( 'change', '.has-condition, .has-condition input, .has-condition select', function() {
        updateConditionalFields();
    } );


    /**
     * Submit Support Form via AJAX
     */
    $( '#helpdocs-support-form' ).on( 'submit', function( e ) {
        e.preventDefault();

        var $form     = $( this );
        var $response = $( '#helpdocs-support-response' );
        var $button   = $( '#helpdocs-submit-support' );
        var $spinner  = $form.find( '.spinner' );

        var isError   = false;
        var errorMsg  = helpdocs_support.required_fields;
        $form.find( '[required]:visible' ).each( function() {
            var $el = $( this );
            if ( ! $el.val() || ( $el.is( ':checkbox' ) && ! $el.is( ':checked' ) ) ) {
                isError = true;
                $el.addClass( 'field-error' );
            } else {
                $el.removeClass( 'field-error' );
            }
        } );

        var totalSize = 0;
        var maxSizeBytes = helpdocs_support.max_attachment_mb * 1024 * 1024; // 10MB default
        var $fileInput = $form.find( 'input[type="file"]' );

        if ( $fileInput.length && $fileInput[0].files.length > 0 ) {
            $.each( $fileInput[0].files, function( i, file ) {
                totalSize += file.size;
            } );
        }

        if ( totalSize > maxSizeBytes ) {
            isError = true;
            errorMsg = helpdocs_support.files_too_large;
        }

        if ( isError ) {
            $response.addClass( 'error' ).html( '<p>' + errorMsg + '</p>' ).fadeIn();
            return;
        }

        var formData = new FormData( this );
        formData.append( 'action', 'helpdocs_send_support_email' );

        $response.hide().removeClass( 'success error' );
        $button.prop( 'disabled', true );
        $spinner.addClass( 'is-active' );

        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function( res ) {
                $spinner.removeClass( 'is-active' );
                $button.prop( 'disabled', false );

                if ( res.success ) {
                    $response.addClass( 'success' ).html( '<p>' + res.data.message + '</p>' ).fadeIn();
                    
                    var now = new Date();
                    var dateStr = formatCurrentDate( helpdocs_support.log_date_format );
                    
                    // Fix: Use correct IDs (prepended with helpdocs_)
                    var subject = $( '#subject' ).val() || 'No Subject';
                    var reason  = $( '#contact_reason' ).val();
                    var message = $( '#message_body' ).val();

                    // Build Attachment HTML
                    var attachmentHtml = '';
                    if ( res.data.attachments && res.data.attachments.length > 0 ) {
                        attachmentHtml = '<div class="log-attachments-container">' +
                                        '<strong>Attachments:</strong> ' + res.data.attachments.join( ', ' ) +
                                        '</div>';
                    }

                    var newRows = '<tr>' +
                        '<td class="log-date">' + dateStr + '</td>' +
                        '<td class="log-subject"><strong>' + subject + '</strong><br><a href="#" class="helpdocs-toggle-message">View Message</a></td>' +
                        '<td class="log-reason"><span class="log-badge">' + reason + '</span></td>' +
                        '<td class="log-user">You</td>' +
                    '</tr>' +
                    '<tr class="log-message-row" style="display: none;">' +
                        '<td colspan="4">' +
                            '<div class="log-message">' + message + '</div>' + 
                            attachmentHtml + 
                        '</td>' +
                    '</tr>';

                    $( '#helpdocs-logs-tbody' ).prepend( newRows );
                    $( '#helpdocs-support-logs' ).show();
                    $form[0].reset();
                    updateConditionalFields();
                }
            }
        } );
    } );


    // Helper function to format current date in PHP-like format
    function formatCurrentDate(phpFormat) {
        const now = new Date();
        
        const months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
        const days = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];
        
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const day = now.getDate();
        const month = now.getMonth();
        const year = now.getFullYear();

        const map = {
            // Day
            'd': (day < 10 ? '0' : '') + day,           // Day, 2 digits with leading zeros (01-31)
            'j': day,                                   // Day, no leading zeros (1-31)
            'S': (day % 10 == 1 && day != 11 ? 'st' : (day % 10 == 2 && day != 12 ? 'nd' : (day % 10 == 3 && day != 13 ? 'rd' : 'th'))),
            'l': days[now.getDay()],                    // Full textual representation of day (Sunday)
            'D': days[now.getDay()].substring(0, 3),    // Short textual representation of day (Sun)

            // Month
            'm': (month + 1 < 10 ? '0' : '') + (month + 1), // Month, 2 digits with leading zeros (01-12)
            'M': months[month].substring(0, 3),         // Short month (Jan)
            'F': months[month],                         // Full month (January)
            'n': month + 1,                             // Month, no leading zeros (1-12)

            // Year
            'Y': year,                                  // Full year (2026)
            'y': year.toString().substring(2),          // 2-digit year (26)

            // Time
            'g': (hours % 12 || 12),                    // 12-hour format, no leading zeros (1-12)
            'G': hours,                                 // 24-hour format, no leading zeros (0-23)
            'h': ((hours % 12 || 12) < 10 ? '0' : '') + (hours % 12 || 12), // 12-hour, leading zeros
            'H': (hours < 10 ? '0' : '') + hours,       // 24-hour, leading zeros
            'i': (minutes < 10 ? '0' : '') + minutes,   // Minutes, leading zeros (00-59)
            's': (now.getSeconds() < 10 ? '0' : '') + now.getSeconds(), // Seconds
            'a': hours >= 12 ? 'pm' : 'am',             // Lowercase am/pm
            'A': hours >= 12 ? 'PM' : 'AM'              // Uppercase AM/PM
        };

        // Regex to find all characters in the map
        const regex = new RegExp(Object.keys(map).join('|'), 'g');

        return phpFormat.replace(regex, function(matched) {
            return map[matched];
        });
    }


    /**
     * Toggle Log Message Visibility
     */
    $( document ).on( 'click', '.helpdocs-toggle-message', function( e ) {
        e.preventDefault();
        var $link = $( this );
        var $messageRow = $link.closest( 'tr' ).next( '.log-message-row' );
        
        $messageRow.toggle();
        var label = $messageRow.is( ':visible' ) ? 'Hide Message' : 'View Message';
        $link.text( label );
    } );


    /**
     * Clear Support Logs
     */
    $( '#helpdocs-clear-logs' ).on( 'click', function( e ) {
        e.preventDefault();

        var $btn      = $( this );
        var $response = $( '#helpdocs-support-response' );

        if ( ! confirm( helpdocs_support.clear_logs_confirm ) ) {
            return;
        }

        // Clear previous responses and dim button
        $response.hide().removeClass( 'success error' );
        $btn.prop( 'disabled', true ).css( 'opacity', '0.5' );

        $.post( ajaxurl, {
            action: 'helpdocs_clear_support_logs',
            nonce: helpdocs_support.nonce,
        }, function( res ) {
            // Restore button state
            $btn.prop( 'disabled', false ).css( 'opacity', '1' );

            if ( res.success ) {
                // UI Cleanup: Clear the table and show the "No Logs" state
                $( '#helpdocs-logs-tbody' ).empty();
                $( '.helpdocs-logs-table' ).addClass( 'condition-hide' );
                $( '.helpdocs-no-logs' ).removeClass( 'condition-hide' );

                // Display Success Message
                $response.addClass( 'success' )
                        .html( '<p>' + res.data + '</p>' )
                        .fadeIn();
                
                // Optional: Hide success message after 3 seconds
                setTimeout( function() { $response.fadeOut(); }, 3000 );
            } else {
                // Display Error Message
                var errorMsg = res.data || 'Failed to clear support logs.';
                $response.addClass( 'error' )
                        .html( '<p>' + errorMsg + '</p>' )
                        .fadeIn();
            }
        } ).fail( function() {
            // Handle server/network errors
            $btn.prop( 'disabled', false ).css( 'opacity', '1' );
            $response.addClass( 'error' )
                    .html( '<p>' + 'A server error occurred while clearing logs.' + '</p>' )
                    .fadeIn();
        } );
    } );
} );