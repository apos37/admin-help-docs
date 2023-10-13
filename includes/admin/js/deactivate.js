jQuery( $ => {
    // Get the Deactivate link
    $( '[data-slug="' + helpdocs_deactivate.plugin_slug + '"] .deactivate a' ).on( 'click', function( e ) {
        
        // Prevent default behavior
        e.preventDefault();

        // Store the link
        const redirectLink = $( this ).attr( 'href' );


        /**
         * Create the modal
         */

        // Create the modal
        var modal = $( '<div id="helpdocs-deactivate-modal-cont"><div id="helpdocs-deactivate-modal" role="dialog" aria-labelledby="helpdocs-deactivate-modal-header"><h2 id="helpdocs-deactivate-modal-header">Quick Feedback</h2><p>If you have a moment, please let me know why you are deactivating:</p><form><div id="helpdocs-dialog-cont"><ul id="helpdocs-deactivate-reasons"></ul></div><div id="helpdocs-deactivate-footer"></div></form></div></div>' );

        // Reasons
        const options = {
            'short': 'I only needed the plugin for a short period',
            'noneed': 'I no longer need the plugin',
            'errors': 'Found errors on the plugin',
            'conflict': 'There is a conflict with another plugin',
            'temp': 'It\'s temporary; just debugging an issue',
            'better': 'I found a better plugin',
            'other': 'Other',
        };

        // Add the radio button options
        $.each( options, function( key, value ) {
            var option = $( '<li class="reason"><input type="radio" id="reason-' + key + '" class="helpdocs-reason" name="helpdocs-deactivate-reason" value="' + key + '"> <label for="reason-' + key + '">' + value + '</label></li>' );
            modal.find( '#helpdocs-deactivate-reasons' ).append( option );
        } );

        // Add comment section
        var comments = $( '<br><label for="helpdocs-deactivate-comments">Kindly explain your reason so I can improve:</label><br><br><textarea id="helpdocs-deactivate-comments" name="comments"></textarea><br><br>');
        modal.find( '#helpdocs-dialog-cont' ).append( comments );

        // Add Anonymous checkbox
        var anon = $( '<input type="checkbox" id="helpdocs-deactivate-anonymously" class="helpdocs-checkbox" name="anonymous" value="1"> <label for="helpdocs-deactivate-anonymously" class="helpdocs-checkbox-label">Anonymous feedback</label>' );
        modal.find( '#helpdocs-deactivate-footer' ).append( anon );

        // Add contact checkbox
        var contact = $( '<br><input type="checkbox" id="helpdocs-deactivate-contact" class="helpdocs-checkbox" name="contact" value="1"> <label for="helpdocs-deactivate-contact" id="helpdocs-deactivate-contact-label" class="helpdocs-checkbox-label">You may contact me for more information</label>' );
        modal.find( '#helpdocs-deactivate-footer' ).append( contact );

        // Add buttons
        var buttons = $( '<div id="helpdocs-deactivate-buttons"><input type="submit" id="helpdocs-submit" class="button button-primary" value="Deactivate" disabled> <input type="submit" id="helpdocs-cancel"class="button button-secondary" value="Cancel"></div>' );
        modal.find( '#helpdocs-deactivate-footer' ).append( buttons );

        // Add support server
        var server = $( '<p id="helpdocs-footer-links"><a href="' + helpdocs_deactivate.support_url + '">Discord Support Server</a> | <a href="http://apos37.com/">Apos37.com</a></p>' );
        modal.find( '#helpdocs-deactivate-footer' ).append( server );

        // Add the modal
        $( 'body' ).append( modal );


        /**
         * Listen for selection
         */

        // Enable submit button only after a selection has been checked
        $( '.helpdocs-reason' ).on( 'click', function( e ) {
            $( '#helpdocs-submit' ).attr( 'disabled', false );
        } );


        /**
         * Listen for anonymous check
         */

        // Hide the contact checkbox if anonmyous is selected
        $( '#helpdocs-deactivate-anonymously' ).on( 'click', function( e ) {
            if ( $( this ).is( ':checked' ) ) {
                $( '#helpdocs-deactivate-contact' ).hide();
                $( '#helpdocs-deactivate-contact-label' ).hide();
            } else {
                $( '#helpdocs-deactivate-contact' ).show();
                $( '#helpdocs-deactivate-contact-label' ).show();
            }
        } );


        /**
         * Close the modal
         */
        
        // Listen for escape key
        $( document ).keyup( function( e ) {

            // First check if it's escape key
            if ( e.key === "Escape" || e.keyCode === 27 ) {

                // Remove the modal complete
                $( "#helpdocs-deactivate-modal-cont" ).remove();
            }
        } );

        // Now listen for cancel button
        $( '#helpdocs-cancel' ).on( 'click', function( e ) {

            // Prevent default behavior
            e.preventDefault();

            // Remove the modal complete
            $( "#helpdocs-deactivate-modal-cont" ).remove();
        } );


        /**
         * Send feedback
         */
        
        // Now listen for submit button
        $( '#helpdocs-submit' ).on( 'click', function( e ) {

            // Prevent default behavior
            e.preventDefault();

            // Get the data from the link
            var nonce = helpdocs_deactivate.nonce;
            var reasonVal = $( '.helpdocs-reason:checked' ).val();
            var commentsVal = $( '#helpdocs-deactivate-comments' ).val();
            var anonVal = $( '#helpdocs-deactivate-anonymously' ).is( ':checked' );
            var canContact = $( '#helpdocs-deactivate-contact' ).is( ':checked' );

            // Validate
            if ( nonce !== '' && reasonVal !== '' ) {

                // Set up the args
                var args = {
                    type : 'post',
                    dataType : 'json',
                    url : helpdocs_deactivate.ajaxurl,
                    data : { 
                        action: 'helpdocs_send_feedback_on_deactivate',
                        nonce: nonce,
                        reason: reasonVal,
                        comments: commentsVal,
                        anonymous: anonVal,
                        contact: canContact
                    },
                    success: function( response ) {

                        // Close the modal
                        $( "#helpdocs-deactivate-modal-cont" ).remove();
                        
                        // If successful
                        if ( response.type == 'success' ) {
                            if ( response.method == 'discord' ) {
                                console.log( 'Your feedback has been sent to my Discord Support Server. Thank you!!' );
                            } else {
                                console.log( 'Your feedback has been sent to my email. Thank you!!' );
                            }
                        } else {
                            if ( response.method == 'discord' ) {
                                console.log( 'Uh oh! Something went wrong and your feedback was not sent to my Discord Support Server. Deactivating anyway...' );
                            } else {
                                console.log( 'Uh oh! Something went wrong and your feedback was not sent to my email. Deactivating anyway...' );
                            }
                        }

                        // Redirect
                        window.location.href = redirectLink;
                    }
                }
                // console.log( args );

                // Start the ajax
                $.ajax( args );
            }
        } );
    } );
} )