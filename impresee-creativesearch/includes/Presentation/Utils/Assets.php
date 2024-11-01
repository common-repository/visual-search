<?php
    namespace SEE\WC\CreativeSearch\Presentation\Utils;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Assets {
    private $plugin_utils;

    function __construct(PluginUtils $plugin_utils)  {
        $this->plugin_utils = $plugin_utils;
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'backend_scripts_styles' ) );
    }

    /**
     * Load styles & scripts
     */
    public function frontend_scripts_styles ( $hook ) {
        wp_enqueue_style(
            'see-wccs-buttons-styles',
            $this->plugin_utils->getPluginUrl() . '/impresee-creativesearch/includes/assets/css/wc-creativesearch-buttons-style.css',
            array(),
            IMSEE_CREATIVESEARCH_VERSION
        );

        do_action('see_wccs_generate_snippet');
        do_action('see_wccs_print_js');
        do_action('see_wccs_print_css');
        do_action('see_wccs_add_css_tab_buttons');
        do_action('see_wccs_add_js_tab_buttons');
    }

    /**
    * Loads style fof the frontend to a tab so that the user can see how the
    * visual search icons will look on their website
    */
    public function add_frontend_styles_to_backend( $hook, $style ) {
        wp_enqueue_style($style);
    }

    /**
     * Load styles & scripts
     */
    public function backend_scripts_styles ( $hook ) {
            // To enqueue styles
            wp_enqueue_style(
                'see-wccs-settings-styles',
                $this->plugin_utils->getPluginUrl() . '/impresee-creativesearch/includes/assets/css/wc-creativesearch-settings-style.css',
                array(),
                IMSEE_CREATIVESEARCH_VERSION
            );
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'colorpicker-script-handle', 
                $this->plugin_utils->getPluginUrl() . '/impresee-creativesearch/includes/assets/js/wc-creativesearch-colorpicker-script.js',
                array( 'wp-color-picker' ), 
                false, 
                true 
            );
    }

}

