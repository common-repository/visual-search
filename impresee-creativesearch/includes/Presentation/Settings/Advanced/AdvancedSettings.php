<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\Advanced;
use Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateCustomCodeConfiguration;
use SEE\WC\CreativeSearch\Presentation\Settings\ISettings;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;

if (! defined('ABSPATH')){
    exit;
}

class AdvancedSettings extends BaseSettings {
    private $update_custom_code_config;
    private $get_custom_code_config;
    private $plugin_utils;

    function __construct(
        UpdateCustomCodeConfiguration $update_custom_code_config, 
        GetCustomCodeConfiguration $get_custom_code_config,
        PluginUtils $plugin_utils,
        Callbacks $callbacks
    ) {
        parent::__construct(SettingsNames::ADVANCED, $callbacks);
        $this->update_custom_code_config =  $update_custom_code_config;
        $this->get_custom_code_config = $get_custom_code_config;
        $this->plugin_utils = $plugin_utils;
        add_action( 'see_wccs_print_js', array($this, 'add_js_as_assets') );
        add_action( 'see_wccs_print_css', array($this, 'add_css_as_assets') );
    }

    private function getSettingsOrDefault(){
        $custom_code_promise = $this->get_custom_code_config->execute($this->plugin_utils->getStore());
        $custom_code_either = $custom_code_promise->wait();
        $custom_code = $custom_code_either->either(
            function ($failure) { 
                $custom_code = new CustomCodeConfiguration;
                $custom_code->js_add_buttons = "";
                $custom_code->css_style_buttons = "";
                $custom_code->js_after_load_results_code = "";
                $custom_code->js_before_load_results_code = "";
                $custom_code->js_search_failed_code = "";
                $custom_code->js_press_see_all_code = "";
                $custom_code->js_close_text_results_code = "";
                $custom_code->js_on_open_text_dropdown_code = "";
                return $custom_code;
            },
            function ($custom_code) { return $custom_code; }
        );
        return $custom_code;
    }

    public function save($data) {
        $sanitized_post = sanitize_post($data, 'db');
        $new_settings = $sanitized_post[$this->config_section_id];
        $new_custom_code = new CustomCodeConfiguration;
        $new_custom_code->js_add_buttons = stripslashes($new_settings['js_buttons']);
        $new_custom_code->css_style_buttons = stripslashes($new_settings['css_buttons']);
        $new_custom_code->js_after_load_results_code = stripslashes($new_settings['js_after_search']);
        $new_custom_code->js_before_load_results_code = stripslashes($new_settings['js_before_load_results_code']);
        $new_custom_code->js_search_failed_code = stripslashes($new_settings['js_search_failed_code']);
        $new_custom_code->js_press_see_all_code = stripslashes($new_settings['js_press_see_all_code']);
        $new_custom_code->js_close_text_results_code = stripslashes($new_settings['js_close_text_results_code']);
        $new_custom_code->js_on_open_text_dropdown_code = stripslashes($new_settings['js_on_open_text_dropdown_code']);
        $update_config_promise = $this->update_custom_code_config->execute(
            $this->plugin_utils->getStore(), 
            $new_custom_code
        );
        $update_config_either = $update_config_promise->wait();
        $success = $update_config_either->either(
            function ($failure) { return FALSE; },
            function ($impresee_data) { return TRUE; }
        );
        return $success;
    }

    public function get( ){
        $custom_code = $this->getSettingsOrDefault();
        $data_array = array(
            $this->config_section_id => array(
                'js_buttons' => $custom_code->js_add_buttons,
                'css_buttons' => $custom_code->css_style_buttons,
                'js_after_search' => $custom_code->js_after_load_results_code,
                'js_before_load_results_code' => $custom_code->js_before_load_results_code,
                'js_search_failed_code' => $custom_code->js_search_failed_code,
                'js_press_see_all_code' => $custom_code->js_press_see_all_code,
                'js_close_text_results_code' => $custom_code->js_close_text_results_code,
                'js_on_open_text_dropdown_code' => $custom_code->js_on_open_text_dropdown_code,
            )
        );
        return $data_array;
    }

    /**
    * Includes the js code written in the advanced settings section to the
    * frontend of the site
    */
    public function add_js_as_assets(){
        $custom_code_promise = $this->get_custom_code_config->execute($this->plugin_utils->getStore());
        $custom_code_either = $custom_code_promise->wait();
        $custom_code = $custom_code_either->either(
            function ($failure) { 
                return NULL;
            },
            function ($custom_code) { return $custom_code; }
        );
        if ( $custom_code == NULL ){
            return;
        }
        wp_register_script( 'see-wccs-impresee-custom-js', '' );
        wp_enqueue_script( 'see-wccs-impresee-custom-js' );
        wp_add_inline_script( 'see-wccs-impresee-custom-js', $custom_code->js_add_buttons);       

    }


    /**
    * Includes the css code written in the advanced settings section to the
    * frontend of the site
    */
    public function add_css_as_assets(){
        $custom_code_promise = $this->get_custom_code_config->execute($this->plugin_utils->getStore());
        $custom_code_either = $custom_code_promise->wait();
        $custom_code = $custom_code_either->either(
            function ($failure) { 
                return NULL;
            },
            function ($custom_code) { return $custom_code; }
        );
        if ( $custom_code == NULL ){
            return;
        }
        wp_register_style( 'see-wccs-impresee-custom-css', '' );
        wp_enqueue_style( 'see-wccs-impresee-custom-css' );
        wp_add_inline_style( 'see-wccs-impresee-custom-css', $custom_code->css_style_buttons);       
    }

    public function saveFormAndRedirect( ){ 
        $page_id = $this->plugin_utils->getPluginPageId();
        $tab = SettingsNames::ADVANCED;
        $success = $this->save($_POST);
        $error_update = "";
        if (!$success){
            $error_update = urlencode("We could not update your configuration. Please try again later.");
        }
        wp_redirect(admin_url("admin.php?page={$page_id}&tab={$tab}&error={$error_update}"));
    }

    public function init_settings() {
        $custom_code = $this->getSettingsOrDefault();
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'advanced_settings',
                'title'     => 'Advanced settings [Optional]',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_buttons',
                'title'     => 'Add your own search buttons via javascript',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'Here you can change the look and behavior of the buttons via javascript. You can even replace them if you want! This code will be executed on-load. DO NOT write the code between script tags. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_buttons',
                    'current'       => stripslashes($custom_code->js_add_buttons),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'css_buttons',
                'title'     => 'Change the style of the search buttons via CSS code',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'Add your own CSS code here! DO NOT write the code between style tags. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'css_buttons',
                    'current'       => stripslashes($custom_code->css_style_buttons),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_after_search',
                'title'     => 'JS code to be executed after a search is performed',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'Here you can add code to modify the look and feel of the search results! This code will be executed after the search results are loaded. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_after_search',
                    'current'       => stripslashes($custom_code->js_after_load_results_code),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_before_load_results_code',
                'title'     => 'JS code to be before search results are loaded',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'This code will be executed before loading the search results. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_before_load_results_code',
                    'current'       => stripslashes($custom_code->js_before_load_results_code),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_search_failed_code',
                'title'     => 'JS code when the search fails',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'This code will be executed when the search couldn\'t be completed. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_search_failed_code',
                    'current'       => stripslashes($custom_code->js_search_failed_code),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_press_see_all_code',
                'title'     => 'JS code to be executed when the instant search "See all" button is pressed',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'This code when the "see all" button is pressed. Here you will have access to a queryText variable, which contains the text the user used to search [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_press_see_all_code',
                    'current'       => stripslashes($custom_code->js_press_see_all_code),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_close_text_results_code',
                'title'     => 'JS code to be executed when closing the search by text results',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'This code has access to a searchType variable, which will be "instant search" when the instant search overlay is closed, and "text" if the full search by text screen is closed. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_close_text_results_code'
                    ,
                    'current'       => stripslashes($custom_code->js_close_text_results_code),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'js_on_open_text_dropdown_code',
                'title'     => 'JS code to be executed when the instant search dropdown appears on screen',
                'callback'  => 'textarea',
                'section'   => 'advanced_settings',
                'args'      => array(
                    'description'   => 'This code will be executed when the instant search dropdown shows up after a user clicks on the search bar. [Optional]',
                    'width' => '80',
                    'height' => '7',
                    'option_name'   => $option_name,
                    'id'            => 'js_on_open_text_dropdown_code',
                    'current'       => stripslashes($custom_code->js_on_open_text_dropdown_code),
                ),
            ),
        );
        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}