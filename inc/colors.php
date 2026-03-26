<?php
/**
 * Colors
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class Colors {

    /**
     * Predefined color themes
     */
    public static function themes() : array {
        $default_colors = [];
        foreach ( self::defaults() as $key => $data ) {
            $default_colors[ $key ] = $data[ 'color' ];
        }

        $themes = [
            'classic' => [
                'label'  => __( 'Classic', 'admin-help-docs' ),
                'colors' => $default_colors,
            ],
            'corporate' => [
                'label'  => __( 'Corporate', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#C99A2C',
                    'doc_bg'          => '#DCF3F6',
                    'doc_title'       => '#093B60',
                    'doc_font'        => '#023260',
                    'doc_link'        => '#00BAD2',
                    'header_bg'       => '#093B60',
                    'header_font'     => '#BEEEF3',
                    'header_tab'      => '#C99A2C',
                    'header_tab_link' => '#BEEEF3',
                    'subheader_bg'    => '#D8F5F8',
                    'subheader_font'  => '#093B60',
                    'button'          => '#093B60',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#006630',
                ],
            ],
            'sunrise'   => [
                'label'  => __( 'Sunrise', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#FF6B6B',
                    'doc_bg'          => '#FFF8F0',
                    'doc_title'       => '#4E342E',
                    'doc_font'        => '#5D4037',
                    'doc_link'        => '#FF8A65',
                    'header_bg'       => '#FF7043',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#FFAB91',
                    'header_tab_link' => '#FFCCBC',
                    'subheader_bg'    => '#FF8A65',
                    'subheader_font'  => '#4E342E',
                    'button'          => '#FF8C42',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#E76F51',
                ],
            ],
            'ocean'     => [
                'label'  => __( 'Ocean', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#0288D1',
                    'doc_bg'          => '#E3F2FD',
                    'doc_title'       => '#01579B',
                    'doc_font'        => '#0277BD',
                    'doc_link'        => '#039BE5',
                    'header_bg'       => '#0277BD',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#4FC3F7',
                    'header_tab_link' => '#B3E5FC',
                    'subheader_bg'    => '#03A9F4',
                    'subheader_font'  => '#01579B',
                    'button'          => '#0277BD',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#01579B',
                ],
            ],
            'forest'    => [
                'label'  => __( 'Forest', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#2E7D32',
                    'doc_bg'          => '#E8F5E9',
                    'doc_title'       => '#1B5E20',
                    'doc_font'        => '#2E7D32',
                    'doc_link'        => '#43A047',
                    'header_bg'       => '#1B5E20',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#66BB6A',
                    'header_tab_link' => '#A5D6A7',
                    'subheader_bg'    => '#43A047',
                    'subheader_font'  => '#1B5E20',
                    'button'          => '#388E3C',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#1B5E20',
                ],
            ],
            'midnight'  => [
                'label'  => __( 'Midnight', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#8E24AA',
                    'doc_bg'          => '#1B1B2F',
                    'doc_title'       => '#E0E0E0',
                    'doc_font'        => '#C5CAE9',
                    'doc_link'        => '#7E57C2',
                    'header_bg'       => '#311B92',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#512DA8',
                    'header_tab_link' => '#B39DDB',
                    'subheader_bg'    => '#7E57C2',
                    'subheader_font'  => '#E0E0E0',
                    'button'          => '#6A1B9A',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#4A148C',
                ],
            ],
            'sunset'    => [
                'label'  => __( 'Sunset', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#FF7043',
                    'doc_bg'          => '#FFF3E0',
                    'doc_title'       => '#BF360C',
                    'doc_font'        => '#E64A19',
                    'doc_link'        => '#FF5722',
                    'header_bg'       => '#F4511E',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#FF8A65',
                    'header_tab_link' => '#FFCCBC',
                    'subheader_bg'    => '#FF7043',
                    'subheader_font'  => '#BF360C',
                    'button'          => '#F4511E',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#BF360C',
                ],
            ],
            'sandstone' => [
                'label'  => __( 'Sandstone', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#D79F65',
                    'doc_bg'          => '#FFF7E6',
                    'doc_title'       => '#8D6E63',
                    'doc_font'        => '#A1887F',
                    'doc_link'        => '#BCAAA4',
                    'header_bg'       => '#B8764C',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#D7B19D',
                    'header_tab_link' => '#FFE0B2',
                    'subheader_bg'    => '#D79F65',
                    'subheader_font'  => '#8D6E63',
                    'button'          => '#B8764C',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#8C5E3B',
                ],
            ],
            'skyline'   => [
                'label'  => __( 'Skyline', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#607D8B',
                    'doc_bg'          => '#ECEFF1',
                    'doc_title'       => '#37474F',
                    'doc_font'        => '#455A64',
                    'doc_link'        => '#546E7A',
                    'header_bg'       => '#455A64',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#90A4AE',
                    'header_tab_link' => '#CFD8DC',
                    'subheader_bg'    => '#607D8B',
                    'subheader_font'  => '#37474F',
                    'button'          => '#455A64',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#263238',
                ],
            ],
            'lavender'  => [
                'label'  => __( 'Lavender', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#7E57C2',
                    'doc_bg'          => '#F3E5F5',
                    'doc_title'       => '#4A148C',
                    'doc_font'        => '#6A1B9A',
                    'doc_link'        => '#9575CD',
                    'header_bg'       => '#5E35B1',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#B39DDB',
                    'header_tab_link' => '#E1BEE7',
                    'subheader_bg'    => '#7E57C2',
                    'subheader_font'  => '#4A148C',
                    'button'          => '#5E35B1',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#4527A0',
                ],
            ],
            'citrus'    => [
                'label'  => __( 'Citrus', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#FFA000',
                    'doc_bg'          => '#FFFDE7',
                    'doc_title'       => '#FF6F00',
                    'doc_font'        => '#FF8F00',
                    'doc_link'        => '#FFC107',
                    'header_bg'       => '#FFB300',
                    'header_font'     => '#FFFFFF',
                    'header_tab'      => '#FFD54F',
                    'header_tab_link' => '#FFF9C4',
                    'subheader_bg'    => '#FFA000',
                    'subheader_font'  => '#FF6F00',
                    'button'          => '#FFB300',
                    'button_font'     => '#FFFFFF',
                    'button_hover'    => '#FF8F00',
                ],
            ],
            'alabaster' => [
                'label'  => __( 'Alabaster', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#D0D0D0',
                    'doc_bg'          => '#FFFFFF',
                    'doc_title'       => '#1D2327',
                    'doc_font'        => '#1F2D5A',
                    'doc_link'        => '#2F76DB',
                    'header_bg'       => '#F5F5F5',
                    'header_font'     => '#1D2327',
                    'header_tab'      => '#E0E0E0',
                    'header_tab_link' => '#616161',
                    'subheader_bg'    => '#ECECEC',
                    'subheader_font'  => '#1D2327',
                    'button'          => '#E0E0E0',
                    'button_font'     => '#1D2327',
                    'button_hover'    => '#C0C0C0',
                ],
            ],
            'marigold' => [
                'label'  => __( 'Marigold', 'admin-help-docs' ),
                'colors' => [
                    'doc_accent'      => '#FFD54F',
                    'doc_bg'          => '#FFFDE7',
                    'doc_title'       => '#5D4037',
                    'doc_font'        => '#6D4C41',
                    'doc_link'        => '#FFA000',
                    'header_bg'       => '#FFF8E1',
                    'header_font'     => '#5D4037',
                    'header_tab'      => '#FFECB3',
                    'header_tab_link' => '#8D6E63',
                    'subheader_bg'    => '#FFE082',
                    'subheader_font'  => '#5D4037',
                    'button'          => '#FFD54F',
                    'button_font'     => '#5D4037',
                    'button_hover'    => '#FFC107',
                ],
            ],
        ];

        return apply_filters( 'helpdocs_color_themes', $themes );
    } // End themes()


    /**
     * Default colors
     */
    public static function defaults( $key = null ) : array {
        $defaults = [
            'doc_accent' => [
                'color' => '#306DC4',
                'label' => __( 'Doc Accent Color', 'admin-help-docs' ),
            ],
            'doc_bg' => [
                'color' => '#FBFBFB',
                'label' => __( 'Doc Background Color', 'admin-help-docs' ),
            ],
            'doc_title' => [
                'color' => '#1D2327',
                'label' => __( 'Doc Title Color', 'admin-help-docs' ),
            ],
            'doc_font' => [
                'color' => '#1F2D5A',
                'label' => __( 'Doc Font Color', 'admin-help-docs' ),
            ],
            'doc_link' => [
                'color' => '#2F76DB',
                'label' => __( 'Doc Link Color', 'admin-help-docs' ),
            ],
            'header_bg' => [
                'color' => '#1D2327',
                'label' => __( 'Header BG Color', 'admin-help-docs' ),
            ],
            'header_font' => [
                'color' => '#FFFFFF',
                'label' => __( 'Header Font Color', 'admin-help-docs' ),
            ],
            'header_tab' => [
                'color' => '#475467',
                'label' => __( 'Header Tab Color', 'admin-help-docs' ),
            ],
            'header_tab_link' => [
                'color' => '#98a2b3',
                'label' => __( 'Header Tab Link Color', 'admin-help-docs' ),
            ],
            'subheader_bg' => [
                'color' => '#0783be',
                'label' => __( 'Subheader BG Color', 'admin-help-docs' ),
            ],
            'subheader_font' => [
                'color' => '#1D2327',
                'label' => __( 'Subheader Font Color', 'admin-help-docs' ),
            ],
            'button' => [
                'color' => '#0783be',
                'label' => __( 'Button Color', 'admin-help-docs' ),
            ],
            'button_font' => [
                'color' => '#FFFFFF',
                'label' => __( 'Button Font Color', 'admin-help-docs' ),
            ],
            'button_hover' => [
                'color' => '#066998',
                'label' => __( 'Button Hover Color', 'admin-help-docs' ),
            ],
        ];

        $defaults = apply_filters( 'helpdocs_color_defaults', $defaults );

        if ( $key !== null ) {
            return $defaults[ $key ] ?? [];
        }

        return $defaults;
    } // End defaults()


    /**
     * Get a color value
     *
     * @param string|null $key The color key
     * @return string|array|false The color value(s) or false if invalid key
     */
    public static function get( $key = null ) : array|string|false {
        $defaults   = self::defaults();
        $stored     = get_option( 'helpdocs_colors', false );

        $option_map = [
            'doc_accent' => 'helpdocs_color_ac',
            'doc_bg'     => 'helpdocs_color_bg',
            'doc_title'  => 'helpdocs_color_fg',
            'doc_font'   => 'helpdocs_color_ti',
            'doc_link'   => 'helpdocs_color_cl',
        ];

        if ( $key === null ) {

            $resolved = [];
            foreach ( $defaults as $color_key => $default ) {
                $value = false;

                if ( is_array( $stored ) && isset( $stored[ $color_key ] ) && $stored[ $color_key ] !== '' ) {
                    $value = sanitize_text_field( $stored[ $color_key ] );
                } elseif ( isset( $option_map[ $color_key ] ) ) {
                    $legacy = sanitize_text_field( get_option( $option_map[ $color_key ], '' ) );
                    if ( $legacy !== '' ) {
                        $value = $legacy;
                    }
                }

                $resolved[ $color_key ] = $value !== false ? $value : $default[ 'color' ];
            }

            return $resolved;
        }

        if ( empty( $defaults[ $key ] ) ) {
            return false;
        }

        if ( is_array( $stored ) && isset( $stored[ $key ] ) && $stored[ $key ] !== '' ) {
            return sanitize_text_field( $stored[ $key ] );
        }

        if ( isset( $option_map[ $key ] ) ) {
            $legacy = sanitize_text_field( get_option( $option_map[ $key ], '' ) );
            if ( $legacy !== '' ) {
                return $legacy;
            }
        }

        return $defaults[ $key ][ 'color' ];
    } // End get()


    /**
     * Convert the old method of storage each color as a separate option into the new method of storing all colors together in a single option as an associative array. This should only be run once during an upgrade routine, and will check if the new option already exists before attempting to convert.   
     */
    public static function convert_color_storage() : void {
        if ( get_option( 'helpdocs_colors', false ) !== false ) {
            return;
        }

        $option_map = [
            'doc_accent' => 'helpdocs_color_ac',
            'doc_bg'     => 'helpdocs_color_bg',
            'doc_title'  => 'helpdocs_color_fg',
            'doc_font'   => 'helpdocs_color_ti',
            'doc_link'   => 'helpdocs_color_cl',
        ];

        $new_colors = [];
        foreach ( $option_map as $new_key => $old_option_name ) {
            $old_value = sanitize_text_field( get_option( $old_option_name, '' ) );
            if ( $old_value !== '' ) {
                $new_colors[ $new_key ] = $old_value;
            }
        }

        if ( ! empty( $new_colors ) ) {
            update_option( 'helpdocs_colors', $new_colors );
            foreach ( $option_map as $old_option_name ) {
                delete_option( $old_option_name );
            }
        }
    } // End convert_color_storage()

}