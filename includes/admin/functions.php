<?php
/**
 * Option pages functions.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add selected to select field if option matches key
 *
 * @param string|int $option
 * @param string|int $the_key
 * @return string
 */
function helpdocs_is_qs_selected( $option, $the_key ) {
    if ( esc_attr( $option ) == esc_attr( $the_key ) ) {
        $results = ' selected';
    } else {
        $results = '';
    }
    return $results;
} // End helpdocs_is_qs_selected()


/**
 * Add checked to checkboxes and radio fields if option matches key
 *
 * @param string|int $option
 * @param string|int $the_key
 * @return string
 */
function helpdocs_is_qs_checked( $option, $the_key ) {
    if ( esc_attr( $option ) == esc_attr( $the_key ) ) {
        $results = ' checked="checked"';
    } else {
        $results = '';
    }
    return $results;
} // End helpdocs_is_qs_checked()


/**
 * Table row for form fields
 * 
 * $args = [ 'default' => 'Default Value', 'required' => true, 'submit_button' => true ]
 * 
 * Text $args = [ 'width' => '100%' 'pattern' => '^[a-zA-Z0-9_.-]*$' ]
 * 
 * Color $args = [ 'width' => '20rem' ]
 * 
 * Textarea $args = [ 'rows' => 6, 'cols' => 50 ]
 * 
 * Select $args = [ 
 *      'blank' => '-- Select One --',
 *      'options' => [
 *          [ 'value' => 'the_value', 'label' => 'Label Name' ], 
 *          [ 'value' => 'the_value', 'label' => 'Label Name' ]
 *      ]
 * ] 
 * OR if value and label are the same
 * $args = [
 *      'options' => [
 *          'Value/Label', 
 *          'Value/Label',
 *      ]
 * ]
 *
 * @param string $option_name
 * @param string $label
 * @param string $type
 * @param string $comments // Use 'get_option' for click to copy get_option()
 * @return string
 */
function helpdocs_options_tr( $option_name, $label, $type, $comments = null, $args = null ) {
    // Add the prefix to the option name
    $option_name = HELPDOCS_GO_PF.$option_name;

    // Get default
    if ( get_option( $option_name ) ) {
        $value = get_option( $option_name );
    } elseif ( !is_null( $args ) && isset( $args[ 'default' ]) && $args[ 'default' ] != '' ) {
        $value = $args[ 'default' ];
    } else {
        $value = '';
    }

    // Make exception for Footer Right
    if ( $option_name == 'footer_right' && str_starts_with( $value, 'Version ' ) ) {
        $value = apply_filters( 'update_footer', '' );
    }

    // Mark required?
    if ( !is_null( $args ) && isset( $args[ 'required' ] ) && $args[ 'required' ] == true ) {
        $required = ' required';
    } else {
        $required = '';
    }

    // Checkbox
    if ($type == 'checkbox') {
        $input = '<input type="checkbox" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" value="1" '.checked( 1, $value, false ).''.$required.'/>';

    // Checkboxes
    } elseif ($type == 'checkboxes') {
        if ( !is_null( $args ) ) {
            $options = $args[ 'options' ];
            $class = isset( $args[ 'class' ] ) ? ' class="'.esc_attr( $args[ 'class' ] ).'"' : '';
        } else {
            return false;
        }

        // Sort by label
        usort( $options, function ( $item1, $item2 ) {
            return strtolower( $item1[ 'label' ] ) <=> strtolower( $item2[ 'label' ] );
        });

        // Iter the options
        $input = '';
        foreach ( $options as $option ) {
            if ( isset( $option[ 'value' ] ) && isset( $option[ 'label' ] ) ) {
                $option_value = $option[ 'value' ];
                $option_label = $option[ 'label' ];
            } elseif ( !is_array( $option ) ) {
                $option_value = $option;
                $option_label = $option;
            }
            if ( !empty( $value ) && array_key_exists( $option_value, $value ) ) {
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }

            $input .= '<div class="checkbox_cont"><input type="checkbox" id="'.esc_attr( $option_name.'_'.$option_value ).'"'.$class.' name="'.esc_attr( $option_name ).'['.$option_value.']" value="1"'.$checked.'/> <label for="'.esc_attr( $option_name.'_'.$option_value ).'">'.$option_label.'</label></div>';
        }

    // Text Field
    } elseif ( $type == 'text' ) {
        if ( !is_null( $args ) && isset( $args[ 'width' ] ) ) {
            $width = $args[ 'width' ];
        } else {
            $width = '43.75rem';
        }
        if ( !is_null( $args ) && isset( $args[ 'pattern' ] ) ) {
            $pattern = ' pattern="'.$args[ 'pattern' ].'"';
            $autocomplete = ' autocomplete="off"';
        } else {
            $pattern = '';
            $autocomplete = '';
        }
        
        $input = '<input type="text" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" value="'.esc_attr( $value ).'" style="width: '.esc_attr( $width ).'"'.$pattern.$autocomplete.$required.'/>';

    // Number Field
    } elseif ( $type == 'number' ) {
        if ( !is_null( $args ) && isset( $args[ 'width' ] ) ) {
            $width = $args[ 'width' ];
        } else {
            $width = '43.75rem';
        }
        if ( !is_null( $args ) && isset( $args[ 'pattern' ] ) ) {
            $pattern = ' pattern="'.$args[ 'pattern' ].'"';
            $autocomplete = ' autocomplete="off"';
        } else {
            $pattern = '';
            $autocomplete = '';
        }
        
        $input = '<input type="number" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" value="'.esc_attr( $value ).'" style="width: '.esc_attr( $width ).'"'.$pattern.$autocomplete.$required.'/>';


    // Text with Color Field
    } elseif ( $type == 'color' ) {
        if ( !is_null( $args ) && isset( $args[ 'width' ] ) ) {
            $width = $args[ 'width' ];
        } else {
            $width = '10rem';
        }
        $input = '<input type="color" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" value="'.esc_html( $value ).'" style="width: '.esc_attr( $width ).'"/>';

    // Textarea    
    } elseif ( $type == 'textarea' ) {
        if ( !is_null( $args ) && isset( $args[ 'rows' ] ) && isset( $args[ 'cols' ] ) ) {
            $rows = $args[ 'rows' ];
            $cols = $args[ 'cols' ];
        } else {
            $rows = 6;
            $cols = 50;
        }
        $input = '<textarea type="text" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" rows="'.esc_attr( $rows ).'" cols="'.esc_attr( $cols ).'"'.$required.'>'.esc_html( $value ).'</textarea>';

    // Select    
    } elseif ( $type == 'select' ) {
        if ( !is_null( $args ) ) {
            $options = $args[ 'options' ];
            if ( isset( $args[ 'blank' ] ) ) {
                $blank = '<option value="">'.esc_html( $args[ 'blank' ] ).'</option>';
            } else {
                $blank = '';
            }
            if ( isset( $args[ 'width' ] ) ) {
                $width = $args[ 'width' ];
            } else {
                $width = '43.75rem';
            }
        } else {
            return false;
        }
        $input = '<select id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" style="width: '.esc_attr( $width ).'"'.$required.'>'.$blank;

        foreach ( $options as $option ) {
            if ( isset( $option[ 'value' ] ) && isset( $option[ 'label' ] ) ) {
                $option_value = $option[ 'value' ];
                $option_label = $option[ 'label' ];
            } elseif ( !is_array( $option ) ) {
                $option_value = $option;
                $option_label = $option;
            }
            $input .= '<option value="'.esc_attr( $option_value ).'"'.helpdocs_is_qs_selected( $option_value, $value ).'>'.$option_label.'</option>';
        }

        $input .= '</select>';

    // Text+ Field
    } elseif ( $type == 'text+' ) {
        if ( !is_null( $args ) && isset( $args[ 'width' ] ) ) {
            $width = $args[ 'width' ];
        } else {
            $width = '43.75rem';
        }
        if ( !is_null( $args ) && isset( $args[ 'pattern' ] ) ) {
            $pattern = ' pattern="'.$args[ 'pattern' ].'"';
            $autocomplete = ' autocomplete="off"';
        } else {
            $pattern = '';
            $autocomplete = '';
        }

        if ( !is_array( $value ) ) {
            $value = [ $value ];
        }
        
        $input = '<div id="text_plus_'.esc_attr( $option_name ).'">
            <a href="#" class="add_form_field">Add New Field +</a>
            <div><input type="text" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'[]" value="'.esc_attr( $value[0] ).'" style="width: '.esc_attr( $width ).'"'.$pattern.$autocomplete.$required.'/></div>
        </div>';

        // Add jQuery
        $js_value = wp_json_encode( $value );
        $input .= '<script>
        jQuery( document ).ready( function( $ ) {
            var max_fields = 20;
            var wrapper = $( "#text_plus_'.esc_attr( $option_name ).'" );
            var add_link = $( "#text_plus_'.esc_attr( $option_name ).' .add_form_field" );
            var load_count = parseInt( "'.count( $value ).'" );
            var value = '.$js_value.';
            console.log( value );

            if ( load_count > 1 ) {
                value.slice( 1 ).forEach( function( v ) {
                    console.log( v );
                    $( wrapper ).append( &quot;<div><input type=\"text\" id=\"'.esc_attr( $option_name ).'\" name=\"'.esc_attr( $option_name ).'[]\" value=\"&quot; + v + &quot;\"/> <a href=\"#\" class=\"delete\">Delete</a></div>&quot; );
                } );
            }
        
            var x = 1;
            $( add_link ).click( function( e ) {
                e.preventDefault();
                if ( x < max_fields ) {
                    x++;
                    $( wrapper ).append( &quot;<div><input type=\"text\" id=\"'.esc_attr( $option_name ).'\" name=\"'.esc_attr( $option_name ).'[]\" value=\"\"/> <a href=\"#\" class=\"delete\">Delete</a></div>&quot; );
                } else {
                    alert( "You reached the limit." )
                }
            });
        
            $( wrapper ).on( "click", ".delete", function( e ) {
                e.preventDefault();
                $( this ).parent( "div" ).remove();
                x--;
            })
        });
        </script>';

    // Dashicon Picker Field
    } elseif ( $type == 'dashicon_picker' ) {
        
        $input = '<input class="regular-text" id="'.esc_attr( $option_name ).'" name="'.esc_attr( $option_name ).'" type="text" value="'.esc_attr( $value ).'"/>
        <input class="button dashicons-picker" type="button" value="Choose Icon" data-target="#'.esc_attr( $option_name ).'" />';

    // Otherwise return false
    } else {
        return false;
    }

    // If comments
    $incl_comments = '';
    if ( !is_null( $comments ) ) {
        if ( $comments == '' ) {
            $incl_comments = '';
        } elseif ( $comments == 'get_option' ) {
            $incl_comments = 'get_option( '.$option_name.' )';
        } elseif ( str_starts_with( $comments, '<br>' ) ) {
            $comments = ltrim( $comments, '<br>' );
            $incl_comments = '<p class="field-desc break">'.$comments.'</p>';
        } elseif ( str_starts_with( $comments, '<div' ) ) {
            $incl_comments = $comments;
        } else {
            $incl_comments = '<p class="field-desc">'.$comments.'</p>';
        }
    }

    // Submit button
    if ( !is_null( $args ) && isset( $args[ 'submit_button' ] ) && $args[ 'submit_button' ] == true ) {
        $submit_button = get_submit_button( 'Search', 'button button-primary button-large '.$option_name );
    } else {
        $submit_button = '';
    }

    // Build the row
    $row = '<tr valign="top">
        <th scope="row">'.$label.'</th>
        <td>'.$input.$submit_button.' '.$incl_comments.'</td>
    </tr>';
    
    // Return the row
    return $row;
} // End helpdocs_options_tr()


/**
 * Allowed html for helpdocs_options_tr() sanitation
 *
 * @return array
 */
function helpdocs_wp_kses_allowed_html() {
    $allowed_html = [
        'div' => [
            'id' => [],
            'class' => []
        ],
        'p' => [
            'id' => [],
            'class' => []
        ],
        'pre' => [
            'class' => []
        ],
        'code' => [
            'class' => []
        ],
        'span' => [
            'id' => [],
            'class' => [],
            'style' => []
        ],
        'a' => [
            'href' => [],
            'id' => [],
            'class' => [],
            'style' => [],
            'target' => [],
            'rel' => []
        ],
        'img' => [
            'border' => [],
            'id' => [],
            'class' => [],
            'style' => [],
            'src' => [],
            'alt' => []
        ],
        'table' => [
            'class' => []
        ],
        'tr' => [
            'valign' => [],
            'class' => []
        ],
        'th' => [
            'scope' => [],
            'class' => []
        ],
        'td' => [
            'class' => []
        ],
        'br' => [],
        'form' => [
            'method' => [],
            'id' => [],
            'action' => [],
        ],
        'label' => [
            'for' => [],
        ],
        'input' => [
            'type' => [],
            'id' => [],
            'class' => [],
            'name' => [],
            'value' => [],
            'checked' => [],
            'required' => [],
            'style' => [],
            'pattern' => [],
            'disabled' => [],
            'size' => [],
            'autocomplete' => [],
            'data-target' => [],
            'aria-label' => []
        ],
        'textarea' => [
            'type' => [],
            'id' => [],
            'class' => [],
            'name' => [],
            'rows' => [],
            'cols' => [],
            'required' => [],
            'style' => [],
        ],
        'select' => [
            'id' => [],
            'class' => [],
            'name' => [],
            'required' => [],
            'style' => [],
            'autocomplete' => [],
        ],
        'option' => [
            'value' => [],
            'selected' => [],
        ],
        'button' => [
            'class' => [],
            'selected' => [],
        ],
        'script' => [
            'id' => []
        ],
        'em' => [],
        'strong' => []
    ];
    return $allowed_html;
} // End helpdocs_options_tr_allowed_html()


/**
 * Allow <script> tags </script> etc in content on docs page only
 *
 * @param array $tags
 * @return array
 */
function helpdocs_allow_addt_tags( $tags ) {
    $tags = array_merge( $tags, [
        'script' => [
            'type'                      => true,
            'src'                       => true,
            'async'                     => true,
            'defer'                     => true,
            'crossorigin'               => true,
            'integrity'                 => true,
        ],
        'video' => [
            'src'                       => true,
            'controls'                  => true,
            'autoplay'                  => true,
            'loop'                      => true,
            'muted'                     => true,
            'poster'                    => true,
            'width'                     => true,
            'height'                    => true,
        ],
        'source' => [
            'src'                       => true,
            'type'                      => true,
        ],
        'iframe' => [
            'src'                       => true,
            'width'                     => true,
            'height'                    => true,
            'frameborder'               => true,
            'allow'                     => true,
            'allowfullscreen'           => true,
            'title'                     => true,
            'referrerpolicy'            => true,
            'webkitallowfullscreen'     => true,
            'mozallowfullscreen'        => true,
        ],
    ] );

    // Add support for filter
    $tags = apply_filters( 'helpdocs_allowed_html', $tags );

    return $tags;
} // End helpdocs_allow_addt_tags()





/**
 * Validate that a date is an actual date
 *
 * @param [type] $date
 * @return bool
 */
function helpdocs_is_date( $date ) {
    return (bool)strtotime( $date );
} // End helpdocs_validate_date()


/**
 * Highlighting syntax
 * 
 * @param string $fl
 * @param bool $ret
 * @return void|bool
 */
function helpdocs_highlight_file2( $filename, $return = false ) {
    $str = highlight_file( $filename, true );
    preg_match_all("/\<span style=\"color: #([\d|A|B|C|D|E|F]{6})\"\>.*?\<\/span\>/", $str, $mtch);
    $m = array_unique( $mtch[1] );

    $cls = '<style type="text/css">'."\n";
    $rpl = array("</a>");
    $mtc = array("</span>");
    $i = 0;
    foreach($m as $clr) {
        $cls .= "a.c".$i."{color: #".$clr.";}\n";
        $rpl[] = "<a class=\"c".$i++."\">";
        $mtc[] = "<span style=\"color: #".$clr."\">";
    }
    $cls .= "</style>";
    $str2 = str_replace($mtc,$rpl,$str);
    if ( $return ) return $str2;
    else echo wp_kses_post( $str2 );
} // End helpdocs_highlight_file2()


/**
 * Add a WP Plugin Info Card
 *
 * @param string $slug
 * @return string
 */
function helpdocs_plugin_card( $slug ) {
    // Set the args
    $args = [ 
        'slug' => $slug, 
        'fields' => [
            'last_updated' => true,
            'tested' => true,
            'active_installs' => true
        ]
    ];
    
    // Fetch the plugin info from the wp repository
    $response = wp_remote_post(
        'http://api.wordpress.org/plugins/info/1.0/',
        [
            'body' => [
                'action' => 'plugin_information',
                'request' => serialize( (object)$args )
            ]
        ]
    );

    // If there is no error, continue
    if ( !is_wp_error( $response ) ) {

        // Unserialize
        $returned_object = unserialize( wp_remote_retrieve_body( $response ) );   
        if ( $returned_object ) {
            
            // Last Updated
            $last_updated = $returned_object->last_updated;
            $last_updated = helpdocs_time_elapsed_string( $last_updated );

            // Compatibility
            $compatibility = $returned_object->tested;

            // Add incompatibility class
            global $wp_version;
            if ( $compatibility == $wp_version ) {
                $is_compatible = '<span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>';
            } else {
                $is_compatible = '<span class="compatibility-untested">Untested with your version of WordPress</span>';
            }

            // Get all the installed plugins
            $plugins = get_plugins();

            // Check if this plugin is installed
            $is_installed = false;
            foreach ( $plugins as $key => $plugin ) {
                if ( $plugin[ 'TextDomain' ] == $slug ) {
                    $is_installed = $key;
                }
            }

            // Check if it is also active
            $is_active = false;
            if ( $is_installed && is_plugin_active( $is_installed ) ) {
                $is_active = true;
            }

            // Check if the plugin is already active
            if ( $is_active ) {
                $install_link = 'role="link" aria-disabled="true"';
                $php_notice = '';
                $install_text = 'Active';

            // Check if the plugin is installed but not active
            } elseif ( $is_installed ) {
                $install_link = 'href="'.admin_url( 'plugins.php' ).'"';
                $php_notice = '';
                $install_text = 'Go to Activate';

            // Check for php requirement
            } elseif ( phpversion() < $returned_object->requires_php ) {
                $install_link = 'role="link" aria-disabled="true"';
                $php_notice = '<div class="php-incompatible"><em><strong>Requires PHP Version '.$returned_object->requires_php.'</strong> — You are currently on Version '.phpversion().'</em></div>';
                $install_text = 'Incompatible';

            // If we're good to go, add the link
            } else {

                // Get the admin url for the plugin install page
                if ( is_multisite() ) {
                    $admin_url = network_admin_url( 'plugin-install.php' );
                } else {
                    $admin_url = admin_url( 'plugin-install.php' );
                }

                // Vars
                $install_link = 'href="'.$admin_url.'?s='.esc_attr( $returned_object->name ).'&tab=search&type=term"';
                $php_notice = '';
                $install_text = 'Get Now';
            }
            
            // Short Description
            $pos = strpos( $returned_object->sections[ 'description' ], '.');
            $desc = substr( $returned_object->sections[ 'description' ], 0, $pos + 1 );

            // Rating
            $rating = helpdocs_get_five_point_rating( 
                $returned_object->ratings[1], 
                $returned_object->ratings[2], 
                $returned_object->ratings[3], 
                $returned_object->ratings[4], 
                $returned_object->ratings[5] 
            );

            // Link guts
            $link_guts = 'href="https://wordpress.org/plugins/'.esc_attr( $slug ).'/" target="_blank" aria-label="More information about '.$returned_object->name.' '.$returned_object->version.'" data-title="'.$returned_object->name.' '.$returned_object->version.'"';
            ?>
            <style>
            .plugin-card {
                float: none !important;
                margin-left: 0 !important;
            }
            .plugin-card .ws_stars {
                display: inline-block;
            }
            .php-incompatible {
                padding: 12px 20px;
                background-color: #D1231B;
                color: #FFFFFF;
                border-top: 1px solid #dcdcde;
                overflow: hidden;
            }
            #wpbody-content .plugin-card .plugin-action-buttons a.install-now[aria-disabled="true"] {
                color: #CBB8AD !important;
                border-color: #CBB8AD !important;
            }
            .plugin-action-buttons {
                list-style: none !important;   
            }
            </style>
            <div class="plugin-card plugin-card-<?php echo esc_attr( $slug ); ?>">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a <?php echo wp_kses_post( $link_guts ); ?>>
                                <?php echo esc_html( $returned_object->name ); ?> 
                                <img src="<?php echo esc_url( HELPDOCS_PLUGIN_IMG_PATH ).esc_attr( $slug  ); ?>.png" class="plugin-icon" alt="<?php echo esc_html( $returned_object->name ); ?> Thumbnail">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a class="install-now button" data-slug="<?php echo esc_attr( $slug ); ?>" <?php echo wp_kses_post( $install_link ); ?> aria-label="<?php echo esc_attr( $install_text );?>" data-name="<?php echo esc_html( $returned_object->name ); ?> <?php echo esc_html( $returned_object->version ); ?>"><?php echo esc_attr( $install_text );?></a></li>
                            <li><a <?php echo wp_kses_post( $link_guts ); ?>>More Details</a></li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p><?php echo wp_kses_post( $desc ); ?></p>
                        <p class="authors"> <cite>By <?php echo wp_kses_post( $returned_object->author ); ?></cite></p>
                    </div>
                </div>
                <div class="plugin-card-bottom">
                    <div class="vers column-rating">
                        <div class="star-rating"><span class="screen-reader-text"><?php echo esc_attr( abs( $rating ) ); ?> star rating based on <?php echo absint( $returned_object->num_ratings ); ?> ratings</span>
                            <?php echo wp_kses_post( helpdocs_convert_to_stars( abs( $rating ) ) ); ?>
                        </div>					
                        <span class="num-ratings" aria-hidden="true">(<?php echo absint( $returned_object->num_ratings ); ?>)</span>
                    </div>
                    <div class="column-updated">
                        <strong>Last Updated:</strong> <?php echo esc_html( $last_updated ); ?>
                    </div>
                    <div class="column-downloaded" data-downloads="<?php echo esc_html( number_format( $returned_object->downloaded ) ); ?>">
                        <?php echo esc_html( number_format( $returned_object->active_installs ) ); ?>+ Active Installs
                    </div>
                    <div class="column-compatibility">
                        <?php echo wp_kses_post( $is_compatible ); ?>				
                    </div>
                </div>
                <?php echo wp_kses_post( $php_notice ); ?>
            </div>
            <?php
        }
    }
} // End helpdocs_plugin_card()


/**
 * Convert 5-point rating to plugin card stars
 *
 * @param int|float $r
 * @return string
 */
function helpdocs_convert_to_stars( $r ) {
    $f = '<div class="star star-full" aria-hidden="true"></div>';
    $h = '<div class="star star-half" aria-hidden="true"></div>';
    $e = '<div class="star star-empty" aria-hidden="true"></div>';
    
    $stars = $e.$e.$e.$e.$e;
    if ( $r > 4.74 ) {
        $stars = $f.$f.$f.$f.$f;
    } elseif ( $r > 4.24 && $r < 4.75 ) {
        $stars = $f.$f.$f.$f.$h;
    } elseif ( $r > 3.74 && $r < 4.25 ) {
        $stars = $f.$f.$f.$f.$e;
    } elseif ( $r > 3.24 && $r < 3.75 ) {
        $stars = $f.$f.$f.$h.$e;
    } elseif ( $r > 2.74 && $r < 3.25 ) {
        $stars = $f.$f.$f.$e.$e;
    } elseif ( $r > 2.24 && $r < 2.75 ) {
        $stars = $f.$f.$h.$e.$e;
    } elseif ( $r > 1.74 && $r < 2.25 ) {
        $stars = $f.$f.$e.$e.$e;
    } elseif ( $r > 1.24 && $r < 1.75 ) {
        $stars = $f.$h.$e.$e.$e;
    } elseif ( $r > 0.74 && $r < 1.25 ) {
        $stars = $f.$e.$e.$e.$e;
    } elseif ( $r > 0.24 && $r < 0.75 ) {
        $stars = $h.$e.$e.$e.$e;
    } else {
        $stars = $stars;
    }

    return '<div class="ws_stars">'.$stars.'</div>';
} // End helpdocs_convert_to_stars()


/**
 * Get 5-point rating from 5 values
 *
 * @param int|float $r1
 * @param int|float $r2
 * @param int|float $r3
 * @param int|float $r4
 * @param int|float $r5
 * @return float
 */
function helpdocs_get_five_point_rating ( $r1, $r2, $r3, $r4, $r5 ) {
    // Calculate them on a 5-point rating system
    $r5b = round( $r5 * 5, 0 );
    $r4b = round( $r4 * 4, 0 );
    $r3b = round( $r3 * 3, 0 );
    $r2b = round( $r2 * 2, 0 );
    $r1b = $r1;
    
    $total = round( $r1 + $r2 + $r3 + $r4 + $r5, 0 );
    if ( $total == 0 ) {
        $r = 0;
    } else {
        $r = round( ( $r1b + $r2b + $r3b + $r4b + $r5b ) / $total, 2 );
    }

    return $r;
} // End helpdocs_get_five_point_rating()


/**
 * Create a json file for the settings if updated
 *
 * @return void
 */
function helpdocs_create_json_from_settings() {
    // Get all of the settings
    $keys = (new HELPDOCS_GLOBAL_OPTIONS)->settings_general;

    // Store the values here
    $values = [];

    // Iter the keys
    foreach ( $keys as $key ) {

        // Get the option
        if ( $key != 'copy_from' && $value = get_option( HELPDOCS_GO_PF.$key ) ) {
            
            // Add the value
            $values[ $key ] = $value;
        }
    }

    // Add the date
    $values[ 'last_updated' ] = helpdocs_convert_timezone();

    // Convert to json
    $json = wp_json_encode( $values );

    // Initialize the filesystem
    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    if ( !$wp_filesystem ) {
        error_log( esc_html__( 'Admin Help Docs: Error creating JSON from settings. Unable to initialize the filesystem.', 'admin-help-docs' ) );
        return;
    }

    // Directories
    $upload_dir = wp_upload_dir();
    $folder = trailingslashit( $upload_dir[ 'basedir' ] . '/' . HELPDOCS_TEXTDOMAIN );

    // Check if the directory exists, and if not, create it
    if ( !$wp_filesystem->is_dir( $folder ) ) {
        $wp_filesystem->mkdir( $folder );
    }

    // Write to file
    $file = $folder.'settings.json';
    
    // Write to file
    if ( !$wp_filesystem->put_contents( $file, $json ) ) {
        error_log( esc_html__( 'Admin Help Docs: Error creating JSON from settings. Failed to write settings to file.', 'admin-help-docs' ) );
    }
} // End helpdocs_create_json_from_settings()


/**
 * Import settings from a json link
 *
 * @return void
 */
function helpdocs_import_settings_from_json( $file ) {
    // Fetch the file
    $request = wp_remote_get( $file.'?time=' . time() );

    // Is there an error?
    if ( is_wp_error( $request ) || $request[ 'response' ][ 'code' ] == 404 ) {
        return 'invalid_file';
    }

    // Open the file
    $json = wp_remote_retrieve_body( $request );

    // Check if it's in json format
    if ( !helpdocs_is_json( $json ) ) {
        return 'invalid_file';
    }  

    // Decode it
    $settings = json_decode( $json );

    // Get our setting meta keys for validation
    // Get all of the settings
    $keys = (new HELPDOCS_GLOBAL_OPTIONS)->settings_general;

    // Bools
    $bools = [
        'admin_bar',
        'hide_version',
        'disable_user_prefs'
    ];

    // Numbers
    $nums = [
        'menu_position'
    ];

    // URLs
    $urls = [
        'logo'
    ];

    // Iter the items
    foreach ( $settings as $setting => $value ) {

        // Don't import multisite setting if it's not a multisite
        if ( !is_multisite() && $setting == 'multisite_sfx' ) {
            delete_option( HELPDOCS_GO_PF.$setting );
            continue;
        }

        // validate the key
        if ( in_array( $setting, $keys ) && $setting !== 'edit_roles' ) {

            // Sanitize
            if ( in_array( $setting, $bools ) ) {
                $value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
            } elseif ( in_array( $setting, $nums ) ) {
                $value = absint( $value );
            } elseif ( in_array( $setting, $urls ) ) {
                $value = esc_url_raw( $value );
            } else {
                $value = sanitize_text_field( $value );
            }

            // Update
            update_option( HELPDOCS_GO_PF.$setting, $value );
        }
    }

    // Return true because it worked
    return true;
} // End helpdocs_import_settings_from_json()


/**
 * Check if a string is json format
 *
 * @param string $string
 * @return bool
 */
function helpdocs_is_json( $string ) {
    json_decode( $string );
    return json_last_error() === JSON_ERROR_NONE;
} // End helpdocs_is_json()


/**
 * Get the plugin title
 *
 * @return string
 */
function helpdocs_title() {
    if ( get_option( HELPDOCS_GO_PF.'page_title' ) && get_option( HELPDOCS_GO_PF.'page_title' ) != '' ) {
        return get_option( HELPDOCS_GO_PF.'page_title' );
    } else {
        return HELPDOCS_NAME;
    }
} // End helpdocs_title()


/**
 * Get the plugin menu title
 *
 * @return string
 */
function helpdocs_menu_title() {
    if ( get_option( HELPDOCS_GO_PF.'menu_title' ) && get_option( HELPDOCS_GO_PF.'menu_title' ) != '' ) {
        return get_option( HELPDOCS_GO_PF.'menu_title' );
    } else {
        return 'Help Docs';
    }
} // End helpdocs_menu_title()


/**
 * Get the logo
 *
 * @return string
 */
function helpdocs_logo() {
    if ( get_option( HELPDOCS_GO_PF.'logo' ) && get_option( HELPDOCS_GO_PF.'logo' ) != '' ) {
        return get_option( HELPDOCS_GO_PF.'logo' );
    } else {
        return HELPDOCS_PLUGIN_IMG_PATH.'logo.png';
    }
} // End helpdocs_logo()


/**
 * THE END
 */