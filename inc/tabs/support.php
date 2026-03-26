<?php
/**
 * Support Tab Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Support {

    /**
     * Form fields for the tab
     *
     * @return array
     */
    private function form_fields() : array {
        $user = wp_get_current_user();
        $first_name = $user->first_name ?? '';
        $last_name = $user->last_name ?? '';
        $full_name = $first_name && $last_name ? "$first_name $last_name" : $user->display_name ?? '';

        $fields = [
            [
                'name'     => 'from_name',
                'type'     => 'text',
                'label'    => __( 'Your Name', 'admin-help-docs' ),
                'desc'     => __( 'Enter your name to include in the support request.', 'admin-help-docs' ),
                'sanitize' => 'sanitize_text_field',
                'default'  => $full_name,
                'required' => true,
            ],
            [
                'name'     => 'reply_to',
                'type'     => 'text',
                'label'    => __( 'Reply-To Email', 'admin-help-docs' ),
                'desc'     => __( 'The email address where we should send our response.', 'admin-help-docs' ),
                'sanitize' => 'sanitize_email',
                'default'  => $user->user_email,
                'required' => true,
            ],
            [
                'name'     => 'contact_reason',
                'type'     => 'select',
                'label'    => __( 'Reason for Contact', 'admin-help-docs' ),
                'desc'     => __( 'Select the category that best fits your inquiry.', 'admin-help-docs' ),
                'choices'  => [
                    [
                        'label' => __( '-- Please One ---', 'admin-help-docs' ),
                        'value' => '',
                    ],
                    [
                        'label' => __( 'Technical Support', 'admin-help-docs' ),
                        'value' => __( 'Technical Support', 'admin-help-docs' ),
                    ],
                    [
                        'label' => __( 'Billing Inquiry', 'admin-help-docs' ),
                        'value' => __( 'Billing Inquiry', 'admin-help-docs' ),
                    ],
                    [
                        'label' => __( 'Feature Request', 'admin-help-docs' ),
                        'value' => __( 'Feature Request', 'admin-help-docs' ),
                    ],
                    [
                        'label' => __( 'Other', 'admin-help-docs' ),
                        'value' => __( 'Other', 'admin-help-docs' ),
                    ],
                ],
                'sanitize' => 'sanitize_text_field',
                'required' => true,
                'has_condition' => true,
            ],
            [
                'name'     => 'page_url',
                'type'     => 'text',
                'label'    => __( 'URL of Page with Issue', 'admin-help-docs' ),
                'desc'     => __( 'The URL of the page where you encountered the issue.', 'admin-help-docs' ),
                'sanitize' => 'sanitize_text_field',
                'condition'   => [ 'contact_reason' => __( 'Technical Support', 'admin-help-docs' ) ],
            ],
            [
                'name'     => 'subject',
                'type'     => 'text',
                'label'    => __( 'Subject', 'admin-help-docs' ),
                'desc'     => __( 'A brief summary of your request.', 'admin-help-docs' ),
                'sanitize' => 'sanitize_text_field',
                'required' => true,
            ],
            [
                'name'     => 'message_body',
                'type'     => 'textarea',
                'label'    => __( 'How can we help?', 'admin-help-docs' ),
                'desc'     => __( 'Please provide as much detail as possible.', 'admin-help-docs' ),
                'sanitize' => 'sanitize_textarea_field',
                'required' => true,
            ],
            [
                'name'     => 'attachments',
                'type'     => 'file',
                'label'    => __( 'Upload Screenshots or Documents', 'admin-help-docs' ),
                'desc'     => sprintf( __( 'Attach relevant files (JPG, PNG, PDF). Max %dMB.', 'admin-help-docs' ), self::$max_attachment_mb ),
                'sanitize' => 'sanitize_files',
            ],
        ];

        return apply_filters( 'helpdocs_support_form_fields', $fields, $user );
    } // End form_fields()


    /**
     * Contact info
     *
     * @var array
     */
    private static $contact_info = [];


    /**
     * Enable or disable logging of support requests
     *
     * @var bool
     */
    private static $logging_enabled = true;


    /**
     * Quantity of support requests to log
     *
     * @var int
     */
    private static $logging_qty = 20;


    /**
     * Max attachment size in MB
     *
     * @var int
     */
    public static $max_attachment_mb = 10;


    /**
     * Date format for logs
     *
     * @var string
     */
    public static $log_date_format = 'M j, Y g:i a';


    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?Support $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * Constructor
     */
    private function __construct() {

        // Store the contact info
        self::get_support_contact_info();

        // Logging
        self::$logging_enabled = apply_filters( 'helpdocs_support_logging_enabled', self::$logging_enabled );
        self::$logging_qty = apply_filters( 'helpdocs_support_log_quantity', self::$logging_qty );

        // Max attachment size
        self::$max_attachment_mb = apply_filters( 'helpdocs_support_max_attachment_size', self::$max_attachment_mb );

        // Date format for logs
        self::$log_date_format = apply_filters( 'helpdocs_support_log_date_format', self::$log_date_format );

        // Add the contact info to the header
        add_action( 'helpdocs_subheader_right', [ $this, 'render_details_box' ] );

        // Handle the AJAX requests
        add_action( 'wp_ajax_helpdocs_send_support_email', [ $this, 'ajax_process_support_form' ] );
        add_action( 'wp_ajax_helpdocs_clear_support_logs', [ $this, 'ajax_clear_support_logs' ] );

    } // End __construct()


    /**
     * Get support contact info
     *
     * @return array
     */
    public function get_support_contact_info() {
        self::$contact_info = apply_filters(
            'helpdocs_support_contact_info',
            [
                'name'   => sanitize_text_field( get_option( 'helpdocs_contact_name', '' ) ),
                'emails' => array_map( 'trim', explode( ',', sanitize_text_field( get_option( 'helpdocs_contact_emails', implode( ', ', Helpers::get_all_admin_emails() ) ) ) ) ),
                'phone'  => sanitize_text_field( get_option( 'helpdocs_contact_phone', '' ) ),
            ]
        );
    } // End get_support_contact_info()


    /**
     * Add a details box to the subheader on the support page
     *
     * @param string $current_tab The current admin tab
     * @return void
     */
    public function render_details_box( string $current_tab ) {
        if ( $current_tab !== 'support' ) {
            return;
        }

        $results = '<div id="helpdocs-header-support-info">';
            
            $details = [];
            foreach ( self::$contact_info as $i => $info ) {
                if ( empty( $info ) ) {
                    continue;
                }

                if ( $i === 'emails' ) {
                    continue;
                }

                $details[] = '<span class="helpdocs-support-' . esc_attr( $i ) . '">' . esc_html( $info ) . '</span>';
            }

            $results .= implode( ' | ', $details );

        $results .= '</div>';

        $results = apply_filters( 'helpdocs_support_details_box_content', $results, self::$contact_info );
        echo wp_kses_post( $results );
    } // End render_details_box()


    /**
     * Render the tab with AJAX support
     */
    public function render_tab() {
        $emails = self::$contact_info[ 'emails' ];
        if ( empty( $emails ) ) {
            echo '<p>' . esc_html__( 'No contact email addresses configured. Please add at least one email address in the settings to contact support.', 'admin-help-docs' ) . '</p>';
            return;
        }

        $fields = $this->form_fields();
        $box_label = apply_filters( 'helpdocs_support_form_box_label', __( 'Complete the form below to send a message to support.', 'admin-help-docs' ) );
        $submit_label = apply_filters( 'helpdocs_support_form_submit_label', __( 'Send Support Request', 'admin-help-docs' ) );
        ?>
        <div class="helpdocs-settings-grid">
            <div class="helpdocs-settings-box">
                <div class="helpdocs-settings-header">
                    <h2><?php echo esc_html( $box_label ); ?></h2>
                </div>
                <div class="helpdocs-settings-body">
                    <?php do_action( 'helpdocs_before_support_form', self::$contact_info ); ?>
                    <form id="helpdocs-support-form" method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'helpdocs_send_support', 'helpdocs_support_nonce' ); ?>
                        
                        <div class="helpdocs-form-fields">
                            <?php
                            foreach ( $fields as $index => $field ) {
                                $render_fn = "render_field_{$field[ 'type' ]}";

                                if ( method_exists( $this, $render_fn ) ) {
                                    $this->{$render_fn}( $field );
                                } elseif ( method_exists( Settings::class, $render_fn ) ) {
                                    Settings::instance()->{$render_fn}( $field );
                                }
                            }
                            ?>
                        </div>

                        <div class="helpdocs-form-footer">
                            <button type="submit" id="helpdocs-submit-support" class="button button-primary">
                                <?php echo esc_html( $submit_label ); ?>
                            </button>
                            <span class="spinner"></span>
                        </div>

                        <div id="helpdocs-support-response" style="margin-top: 15px;"></div>
                    </form>
                    <?php do_action( 'helpdocs_after_support_form', self::$contact_info ); ?>
                </div>
            </div>
            <?php if ( self::$logging_enabled ) : ?>
                <?php 
                $logs = get_option( 'helpdocs_support_log', [] );
                $display_class = empty( $logs ) ? ' style="display: none;"' : '';
                ?>
                <div id="helpdocs-support-logs" class="helpdocs-settings-box"<?php echo esc_attr( $display_class ); ?>>
                    <div class="helpdocs-settings-header">
                        <h2><?php echo esc_html( __( 'Support Logs', 'admin-help-docs' ) ); ?></h2>
                        <?php if ( Helpers::user_can_edit() ) : ?>
                            <button type="button" id="helpdocs-clear-logs" class="helpdocs-button">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e( 'Clear Logs', 'admin-help-docs' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="helpdocs-settings-body">
                        <p><?php echo esc_html( __( 'Here you can view your most recent support requests.', 'admin-help-docs' ) ); ?></p>
                        
                        <table class="helpdocs-logs-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Date', 'admin-help-docs' ); ?></th>
                                    <th><?php esc_html_e( 'Subject', 'admin-help-docs' ); ?></th>
                                    <th><?php esc_html_e( 'Reason', 'admin-help-docs' ); ?></th>
                                    <th><?php esc_html_e( 'Sent By', 'admin-help-docs' ); ?></th>
                                </tr>
                            </thead>
                            <tbody id="helpdocs-logs-tbody">
                                <?php foreach ( array_slice( $logs, 0, self::$logging_qty ) as $log ) : ?>
                                    <tr>
                                        <td class="log-date"><?php echo esc_html( date_i18n( self::$log_date_format, strtotime( $log[ 'date' ] ) ) ); ?></td>
                                        <td class="log-subject">
                                            <strong><?php echo esc_html( $log[ 'subject' ] ); ?></strong>
                                            <?php if ( ! empty( $log[ 'message' ] ) ) : ?>
                                                <br><a href="#" class="helpdocs-toggle-message" style="font-size: 11px; text-decoration: none;"><?php esc_html_e( 'View Message', 'admin-help-docs' ); ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="log-reason"><span class="log-badge"><?php echo esc_html( $log[ 'reason' ] ); ?></span></td>
                                        <td class="log-user"><?php echo esc_html( $log[ 'user' ] ); ?></td>
                                    </tr>
                                    <?php if ( ! empty( $log[ 'message' ] ) ) : ?>
                                        <tr class="log-message-row" style="display: none;">
                                            <td colspan="4">
                                                <div class="log-message"><?php echo esc_html( $log[ 'message' ] ); ?></div>
                                                <?php if ( ! empty( $log[ 'attachments' ] ) ) : ?>
                                                    <div class="log-attachments-container">
                                                        <strong><?php esc_html_e( 'Attachments:', 'admin-help-docs' ); ?></strong> <span class="log-attachments"><?php echo esc_html( implode( ', ', $log[ 'attachments' ] ) ); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    } // End render_tab()


    /**
     * Process the support form submission via AJAX
     */
    public function ajax_process_support_form() {
        check_ajax_referer( 'helpdocs_send_support', 'helpdocs_support_nonce' );
        if ( ! Helpers::user_can_view() ) {
            wp_send_json_error( __( 'Unauthorized', 'admin-help-docs' ), 403 );
        }

        $fields = $this->form_fields();
        $data_to_send = [];
        $attachments = [];
        $attachment_names = [];
        $email_body = "";

        foreach ( $fields as $field ) {
            $name = $field[ 'name' ];
            $post_key = 'helpdocs_' . $name;

            $label = $field[ 'label' ] ?? strtoupper( str_replace( '_', ' ', $name ) );

            if ( $field[ 'type' ] === 'file' ) {
                $file_paths = Settings::instance()->sanitize_files( $name, self::$max_attachment_mb );
                if ( ! empty( $file_paths ) ) {
                    $attachments = array_merge( $attachments, $file_paths );
                    foreach ( $file_paths as $path ) {
                        $attachment_names[] = basename( $path );
                    }
                }
                continue;
            }

            if ( isset( $_POST[ $post_key ] ) ) {
                $raw_value = wp_unslash( $_POST[ $post_key ] ); // phpcs:ignore

                if ( $field[ 'type' ] === 'checkbox' ) {
                    $value = Settings::instance()->sanitize_checkbox( $raw_value );
                } else {
                    $value = Settings::instance()->sanitize_field( $field, $raw_value );

                    // If text field is empty, fall back to default
                    if ( $field[ 'type' ] === 'text' && $value === '' && isset( $field[ 'default' ] ) ) {
                        $value = $field[ 'default' ];
                    }
                }
            } else {
                $value = $field[ 'default' ] ?? '';
            }

            $data_to_send[ $name ] = $value;
            $trimmed_label = rtrim( (string) $label );

            if ( substr( $trimmed_label, -1 ) === '?' ) {
                $email_body .= $trimmed_label . "\n" . $value . "\n\n";
            } else {
                $email_body .= $trimmed_label . ': ' . $value . "\n\n";
            }
        }

        // Add Site Context for better debugging
        $email_body .= "--- SITE INFO ---\n";
        $email_body .= "Site URL: " . get_site_url() . "\n";
        $email_body .= "WP Version: " . get_bloginfo( 'version' ) . "\n";

        $from_email = get_option( 'admin_email' );
        $from_name  = get_bloginfo( 'name' );
        $to      = self::$contact_info[ 'emails' ];
        $subject = sprintf( '[%s Support] %s', Bootstrap::name(), __( 'New Inquiry', 'admin-help-docs' ) );

        $headers = [
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $data_to_send[ 'from_name' ] . ' <' . $data_to_send[ 'reply_to' ] . '>',
            'Content-Type: text/plain; charset=UTF-8',
        ];
    
        $email_args = [
            'to'          => $to,
            'subject'     => $subject,
            'message'     => $email_body,
            'headers'     => $headers,
            'attachments' => $attachments,
        ];
        $email_args = apply_filters( 'helpdocs_support_email_args', $email_args, $data_to_send );

        // dwl( $_POST );
        // dwl( $email_args );

        $sent = wp_mail( $email_args[ 'to' ], $email_args[ 'subject' ], $email_args[ 'message' ], $email_args[ 'headers' ], $email_args[ 'attachments' ] );

        if ( $sent ) {

            // Log the request if enabled
            if ( self::$logging_enabled ) {
                $this->log_support_request( $data_to_send, $attachment_names );
            }

            // Cleanup temp files
            foreach ( $attachments as $file ) {
                if ( file_exists( $file ) ) {
                    wp_delete_file( $file );
                }
            }

            wp_send_json_success( [
                'message'     => __( 'Your message has been sent successfully!', 'admin-help-docs' ),
                'attachments' => $attachment_names
            ] );
        } else {
            wp_send_json_error( __( 'Failed to send message. Please check your SMTP settings.', 'admin-help-docs' ) );
        }
    } // End ajax_process_support_form()


    /**
     * Log the request to a site option (keeps last 20)
     * * @param array $data
     * * @param array $attachments
     */
    private function log_support_request( $data, $attachments = [] ) {
        $logs = get_option( 'helpdocs_support_log', [] );
        
        $new_entry = [
            'date'        => current_time( 'mysql' ),
            'user'        => $data[ 'from_name' ] ?? wp_get_current_user()->display_name,
            'subject'     => $data[ 'subject' ] ?? 'No Subject',
            'reason'      => $data[ 'contact_reason' ] ?? 'N/A',
            'message'     => $data[ 'message_body' ] ?? '',
            'attachments' => $attachments,
        ];

        array_unshift( $logs, $new_entry );
        $logs = array_slice( $logs, 0, self::$logging_qty );

        update_option( 'helpdocs_support_log', $logs, false );
    } // End log_support_request()

    
    /**
     * Clear Logs (Outside the Form)
     */
    public function ajax_clear_support_logs() {
        check_ajax_referer( 'helpdocs_support_nonce', 'nonce' );
        if ( ! Helpers::user_can_edit() ) {
            wp_send_json_error(  __( 'You are not authorized to clear the logs.', 'admin-help-docs' ), 403 );
        }

        $deleted = delete_option( 'helpdocs_support_log' );

        if ( $deleted ) {
            wp_send_json_success( __( 'Support logs cleared successfully.', 'admin-help-docs' ) );
        } else {
            wp_send_json_error( __( 'Failed to clear support logs.', 'admin-help-docs' ) );
        }
    } // End ajax_clear_support_logs()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


Support::instance();