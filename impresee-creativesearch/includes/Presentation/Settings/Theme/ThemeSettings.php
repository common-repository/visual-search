<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\Theme;
use Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSnippetConfiguration, ImpreseeSnippetGeneralConfiguration};
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;

if (! defined('ABSPATH')){
    exit;
}

class ThemeSettings extends BaseSettings{
    private $update_snippet_config;
    private $get_snippet_config;
    private $plugin_utils;

    function __construct(
        UpdateSnippetConfiguration $update_snippet_config, 
        GetSnippetConfiguration $get_snippet_config,
        PluginUtils $plugin_utils,
        Callbacks $callbacks
    ) {
        parent::__construct(SettingsNames::THEME, $callbacks);
        $this->update_snippet_config =  $update_snippet_config;
        $this->get_snippet_config = $get_snippet_config;
        $this->plugin_utils = $plugin_utils;
    }

    private function getSettingsOrDefault(){
        $config_data_promise = $this->get_snippet_config->execute($this->plugin_utils->getStore());
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
                $new_configuration = new ImpreseeSnippetConfiguration;
                $config = new ImpreseeSnippetGeneralConfiguration;
                $config->load_after_page_render = FALSE;
                $config->container_selector = '';
                $config->main_color = '#9CD333';
                $config->add_search_data_to_url = FALSE;
                $config->images_only_loaded_from_camera = FALSE;
                $config->disable_image_crop = FALSE;
                $config->price_fraction_digit_number = 2;
                $config->decimal_separator = stripslashes(wc_get_price_decimal_separator());
                $config->currency_symbol_at_the_end = FALSE;
                $config->on_sale_label_color = '#FF0000';
                $config->use_photo_search = TRUE;
                $config->use_sketch_search = TRUE;
                $config->search_by_photo_icon_url = '';
                $config->search_by_sketch_icon_url = '';
                $new_configuration->general_configuration = $config;

                return $new_configuration;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        $theme_config = $config_data->general_configuration;
        return $theme_config;
    }

    public function get( ){
        $theme_config = $this->getSettingsOrDefault();
        $data_array = array(
            $this->config_section_id => array(
                'load_after_page_render' => $theme_config->load_after_page_render,
                'container_selector' => $theme_config->container_selector,
                'impresee_main_color_picker' => $theme_config->main_color,
                'add_search_data_to_url' => $theme_config->add_search_data_to_url,
                'images_only_loaded_from_camera' => $theme_config->images_only_loaded_from_camera,
                'impresee_on_sale_label_color' => $theme_config->on_sale_label_color,
                'disable_image_crop' => $theme_config->disable_image_crop,
                'use_photo_search' => $theme_config->use_photo_search,
                'use_sketch_search' => $theme_config->use_sketch_search,
                'search_by_photo_icon_url' => $theme_config->search_by_photo_icon_url,
                'search_by_sketch_icon_url' => $theme_config->search_by_sketch_icon_url,
            )
        );
        return $data_array;
    }

    public function save($data) {
        $sanitized_post = sanitize_post($data, 'db');
        $new_settings = $sanitized_post[$this->config_section_id];
        $config_data_promise = $this->get_snippet_config->execute($this->plugin_utils->getStore());
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
                return NULL;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        if($config_data == NULL){
            return FALSE;
        } else {
            $currency_pos = get_option( 'woocommerce_currency_pos' );
            $new_configuration = new ImpreseeSnippetGeneralConfiguration;
            $new_configuration->load_after_page_render = isset($new_settings["load_after_page_render"]) ? TRUE : FALSE;
            $new_configuration->container_selector = stripslashes($new_settings["container_selector"]);
            $new_configuration->main_color = stripslashes($new_settings["impresee_main_color_picker"]);
            $new_configuration->add_search_data_to_url = isset($new_settings["add_search_data_to_url"]) ? TRUE : FALSE;
            $new_configuration->images_only_loaded_from_camera = isset($new_settings["images_only_loaded_from_camera"]) ? TRUE : FALSE;
            $new_configuration->price_fraction_digit_number = wc_get_price_decimals();
            $new_configuration->currency_symbol_at_the_end = strpos($currency_pos, 'right') !== false;
            $new_configuration->on_sale_label_color = stripslashes($new_settings["impresee_on_sale_label_color"]);
            $new_configuration->decimal_separator = stripslashes(wc_get_price_decimal_separator());
            $new_configuration->disable_image_crop = isset($new_settings['disable_image_crop']) ? TRUE : FALSE;
            $new_configuration->use_photo_search = isset($new_settings['use_photo_search']) ? TRUE : FALSE;
            $new_configuration->use_sketch_search = isset($new_settings['use_sketch_search']) ? TRUE : FALSE;
            $new_configuration->search_by_photo_icon_url = stripslashes($new_settings['search_by_photo_icon_url']);
            $new_configuration->search_by_sketch_icon_url = stripslashes($new_settings['search_by_sketch_icon_url']);
            
             // Update the data
            $config_data->general_configuration = $new_configuration;
            $update_promise = $this->update_snippet_config->execute(
                $this->plugin_utils->getStore(), 
                $config_data
            );
            $update_either = $update_promise->wait();
            $success = $update_either->either(
                function ($failure) { return FALSE; },
                function ($impresee_data) { return TRUE; }
            );
            return $success;
        }
    }

    public function saveFormAndRedirect( ){
        $success = $this->save($_POST);
        $page_id = $this->plugin_utils->getPluginPageId();
        $tab = SettingsNames::THEME;
        $error_update = "";
        if (!$success){
            $error_update = urlencode("We could not update your configuration. Please try again later.");
        }
        wp_redirect(admin_url("admin.php?page={$page_id}&tab={$tab}&error={$error_update}"));
    }

    /**
    * Add general settings using add_settings_field
    */
    public function init_settings() {
        $theme_config = $this->getSettingsOrDefault();
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'theme_settings',
                'title'     => 'Theme settings',
                'callback'  => 'section',
            ),
             array(
                'type'      => 'setting',
                'id'        => 'load_after_page_render',
                'title'     => 'Load scripts after page renders',
                'callback'  => 'checkbox',
                'section'   => 'theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'load_after_page_render',
                    'description'   => 'Useful when search bars load dynamically via Javascript',
                    'current' => $theme_config->load_after_page_render,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'use_photo_search',
                'title'     => 'Use image search',
                'callback'  => 'checkbox',
                'section'   => 'theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'use_photo_search',
                    'description' => 'Allows you to enable/disable image search in your site',
                    'current' => $theme_config->use_photo_search,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'use_sketch_search',
                'title'     => 'Use search by sketch',
                'callback'  => 'checkbox',
                'section'   => 'theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'use_sketch_search',
                    'description' => 'Allows you to enable/disable search by sketch in your site',
                    'current' => $theme_config->use_sketch_search,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'impresee_main_color_picker',
                'title'     => 'Main color',
                'callback'  => 'text_input',
                'section'   => 'theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'impresee_main_color_picker',
                    'default' => $theme_config->main_color,
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'impresee_on_sale_label_color',
                'title'     => 'On sale label color',
                'callback'  => 'text_input',
                'section'   => 'theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'impresee_on_sale_label_color',
                    'default' => $theme_config->on_sale_label_color,
                ),
            ),
            array(
                'type'      => 'section',
                'id'        => 'visual_search_settings',
                'title'     => 'Visual search settings',
                'callback'  => 'section',
            ),
           
            array(
                'type'      => 'setting',
                'id'        => 'container_selector',
                'title'     => 'Visual search container selector',
                'callback'  => 'text_input',
                'section'   => 'visual_search_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'container_selector',
                    'descriptor'    => 'DOM element where visual search results are emebedded [OPTIONAL]',
                    'current' => stripslashes($theme_config->container_selector),
                ),
            ),
            
            array(
                'type'      => 'setting',
                'id'        => 'add_search_data_to_url',
                'title'     => 'Add visual search id to url',
                'callback'  => 'checkbox',
                'section'   => 'visual_search_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'add_search_data_to_url',
                    'description'   => 'Adds a search id to the url so that the search is re-loaded when a user refreshes the site',
                    'current' => $theme_config->add_search_data_to_url,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'images_only_loaded_from_camera',
                'title'     => 'Upload pictures only from a camera',
                'callback'  => 'checkbox',
                'section'   => 'visual_search_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'images_only_loaded_from_camera',
                    'current' => $theme_config->images_only_loaded_from_camera,
                    'description' => 'Users will only be able to search by taking pictures directly from the camera (when searching on a mobile device)'

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'disable_image_crop',
                'title'     => 'Disable manual crop of the picture (not available for fashion & apparel or home & decor stores)',
                'callback'  => 'checkbox',
                'section'   => 'visual_search_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'disable_image_crop',
                    'current' => $theme_config->disable_image_crop,
                    'description' => 'Users won\'t be given the option to crop the uploaded picture'

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_by_photo_icon_url',
                'title'     => 'Search by photo icon url',
                'callback'  => 'text_input',
                'section'   => 'visual_search_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_by_photo_icon_url',
                    'current' => stripslashes($theme_config->search_by_photo_icon_url),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_by_sketch_icon_url',
                'title'     => 'Search by sketch icon url',
                'callback'  => 'text_input',
                'section'   => 'visual_search_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_by_sketch_icon_url',
                    'current' => stripslashes($theme_config->search_by_sketch_icon_url),
                ),
            ),
        );
        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}
