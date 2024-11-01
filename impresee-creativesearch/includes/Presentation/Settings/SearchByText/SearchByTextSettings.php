<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\SearchByText;
use Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSnippetConfiguration, ImpreseeSnippetSearchByTextConfiguration};
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;

if (! defined('ABSPATH')){
    exit;
}

class SearchByTextSettings extends BaseSettings{
    private $update_snippet_config;
    private $get_snippet_config;
    private $plugin_utils;

    function __construct(
        UpdateSnippetConfiguration $update_snippet_config, 
        GetSnippetConfiguration $get_snippet_config,
        PluginUtils $plugin_utils,
        Callbacks $callbacks
    ) {
        parent::__construct(SettingsNames::SEARCH_BY_TEXT, $callbacks);
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
                $config = new ImpreseeSnippetSearchByTextConfiguration;
                $config->use_text = TRUE;
                $config->search_delay_millis = 300;
                $config->full_text_search_results_container = "body";
                $config->compute_results_top_position_from = "header";
                $config->use_instant_full_search = TRUE;
                $config->use_floating_search_bar_button = TRUE;
                $config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
                $config->use_search_suggestions = TRUE;
                $config->search_bar_selector = 'input[name=q],input[name=s]';
                $config->mobile_instant_as_grid = FALSE;
                $new_configuration->search_by_text_configuration = $config;

                return $new_configuration;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        $search_config = $config_data->search_by_text_configuration;
        return $search_config;
    }

    public function get( ){
        $search_config = $this->getSettingsOrDefault();
        $data_array = array(
            $this->config_section_id => array(
                'use_text' => $search_config->use_text,
                'search_delay_millis' => $search_config->search_delay_millis,
                'full_text_search_results_container' => $search_config->full_text_search_results_container,
                'compute_results_top_position_from' => $search_config->compute_results_top_position_from,
                'use_instant_full_search' => $search_config->use_instant_full_search,
                'use_floating_search_bar_button' => $search_config->use_floating_search_bar_button,
                'floating_button_location' => $search_config->floating_button_location,
                'search_bar_selector' => $search_config->search_bar_selector,
                'use_search_suggestions' => $search_config->use_search_suggestions,
                'mobile_instant_as_grid' => $search_config->mobile_instant_as_grid
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
            $search_millis = intval($new_settings["search_delay_millis"]);
            $new_configuration = new ImpreseeSnippetSearchByTextConfiguration;
            $new_configuration->use_text = isset($new_settings["use_text"]) ? TRUE : FALSE;
            // if search_millis == 0 => 300
            $new_configuration->search_delay_millis = $search_millis ? $search_millis : 300;
            $new_configuration->full_text_search_results_container = stripslashes($new_settings["full_text_search_results_container"]);
            $new_configuration->compute_results_top_position_from = stripslashes($new_settings["compute_results_top_position_from"]);
            $new_configuration->use_instant_full_search = isset($new_settings["use_instant_full_search"]) ? TRUE : FALSE;
            $new_configuration->use_floating_search_bar_button = isset($new_settings["use_floating_search_bar_button"]) ? TRUE : FALSE;
            $new_configuration->floating_button_location = $new_settings["floating_button_location"];
            $new_configuration->search_bar_selector = stripslashes($new_settings["search_bar_selector"]);
            $new_configuration->use_search_suggestions = isset($new_settings["use_search_suggestions"])? TRUE : FALSE;
            $new_configuration->mobile_instant_as_grid = isset($new_settings["mobile_instant_as_grid"])? TRUE : FALSE;
             // Update the data
            $config_data->search_by_text_configuration = $new_configuration;
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
        $tab = SettingsNames::SEARCH_BY_TEXT;
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
        $search_config = $this->getSettingsOrDefault();
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'search_by_text_settings',
                'title'     => 'Search by text settings',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'use_text',
                'title'     => 'Use search by text feature',
                'callback'  => 'checkbox',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'use_text',
                    'current' => $search_config->use_text,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'use_search_suggestions',
                'title'     => 'Show search suggestions',
                'callback'  => 'checkbox',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'use_search_suggestions',
                    'current' => $search_config->use_search_suggestions,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'mobile_instant_as_grid',
                'title'     => 'Display instant results as a grid (mobile only)',
                'callback'  => 'checkbox',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'mobile_instant_as_grid',
                    'current' => $search_config->mobile_instant_as_grid,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_delay_millis',
                'title'     => 'Time it takes to start searching after pressing a key (in milliseconds)',
                'callback'  => 'text_input',
                'section'   => 'search_by_text_settings',
                'type'      => 'number',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_delay_millis',
                    'current' => $search_config->search_delay_millis,
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_bar_selector',
                'title'     => 'DOM search bar selector',
                'callback'  => 'text_input',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_bar_selector',
                    'current' => stripslashes($search_config->search_bar_selector),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'full_text_search_results_container',
                'title'     => 'DOM element which will contain the full search results screen',
                'callback'  => 'text_input',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'full_text_search_results_container',
                    'current' => stripslashes($search_config->full_text_search_results_container),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'compute_results_top_position_from',
                'title'     => 'DOM element used as a reference to position the search results screen vertically',
                'callback'  => 'text_input',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'compute_results_top_position_from',
                    'current' => stripslashes($search_config->compute_results_top_position_from),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'use_instant_full_search',
                'title'     => 'Load full search without reloading site',
                'callback'  => 'checkbox',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'use_instant_full_search',
                    'current' => $search_config->use_instant_full_search,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'use_floating_search_bar_button',
                'title'     => 'Add floating button',
                'callback'  => 'checkbox',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'use_floating_search_bar_button',
                    'current' => $search_config->use_floating_search_bar_button,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'floating_button_location',
                'title'     => 'Floating button location in screen',
                'callback'  => 'select',
                'section'   => 'search_by_text_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'floating_button_location',
                    'options'       => array(
                        ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT   => 'Bottom left',
                        ImpreseeSnippetSearchByTextConfiguration::TOP_LEFT   => 'Top left',
                        ImpreseeSnippetSearchByTextConfiguration::MIDDLE_LEFT   => 'Middle left',
                        ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT   => 'Bottom right',
                        ImpreseeSnippetSearchByTextConfiguration::TOP_RIGHT   => 'Top right',
                        ImpreseeSnippetSearchByTextConfiguration::MIDDLE_RIGHT   => 'Middle right',
                    ),
                    'current' => $search_config->floating_button_location,

                )
            ),
        );
        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}
