<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\Labels;
use Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration;
use SEE\WC\CreativeSearch\Presentation\Settings\ISettings;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSnippetConfiguration, ImpreseeSnippetLabelsConfiguration};
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;

if (! defined('ABSPATH')){
    exit;
}

class LabelsSettings extends BaseSettings {
    private $update_snippet_config;
    private $get_snippet_config;
    private $plugin_utils;

    function __construct(
        UpdateSnippetConfiguration $update_snippet_config, 
        GetSnippetConfiguration $get_snippet_config,
        PluginUtils $plugin_utils,
        Callbacks $callbacks
    ) {
        parent::__construct(SettingsNames::LABELS, $callbacks);
        $this->update_snippet_config =  $update_snippet_config;
        $this->get_snippet_config = $get_snippet_config;
        $this->plugin_utils = $plugin_utils;
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
            $new_labels_settings = new ImpreseeSnippetLabelsConfiguration;
            $new_labels_settings->search_results_title = stripslashes($new_settings['results_title']);
            $new_labels_settings->search_button_label = stripslashes($new_settings['search_button_label']);
            $new_labels_settings->oops_exclamation = stripslashes($new_settings['error_exclamation']);
            $new_labels_settings->error_title = stripslashes($new_settings['error_title']);
            $new_labels_settings->error_message = stripslashes($new_settings['error_body']);
            $new_labels_settings->drag_and_drop_image_title = stripslashes($new_settings['drag_and_drop_title']);
            $new_labels_settings->drag_and_drop_image_body = stripslashes($new_settings['drag_and_drop_body']);
            $new_labels_settings->custom_crop_label = stripslashes($new_settings['custom_crop_label']);
            $new_labels_settings->start_writing_label = stripslashes($new_settings['start_writing_label']);
            $new_labels_settings->currency_symbol = stripslashes(get_woocommerce_currency_symbol());
            $new_labels_settings->search_by_photo_label = stripslashes($new_settings['search_by_photo_label']);
            $new_labels_settings->search_by_sketch_label = stripslashes($new_settings['search_by_sketch_label']);
            $new_labels_settings->see_all_results_label = stripslashes($new_settings['see_all_results_label']);
            $new_labels_settings->no_matching_results = stripslashes($new_settings['no_matching_results']);
            $new_labels_settings->on_sale_label = stripslashes($new_settings['on_sale_label']);
            $new_labels_settings->result_title_search_by_text = stripslashes($new_settings['result_title_search_by_text']);
            $new_labels_settings->number_of_results_label_desktop = stripslashes($new_settings['number_of_results_label_desktop']);
            $new_labels_settings->number_of_results_label_mobile = stripslashes($new_settings['number_of_results_label_mobile']);
            $new_labels_settings->filters_title_label_mobile = stripslashes($new_settings['filters_title_label_mobile']);
            $new_labels_settings->clear_filters_label = stripslashes($new_settings['clear_filters_label']);
            $new_labels_settings->sort_by_label = stripslashes($new_settings['sort_by_label']);
            $new_labels_settings->apply_filters_label_mobile = stripslashes($new_settings['apply_filters_label_mobile']);
            $new_labels_settings->try_searching_again_label = stripslashes($new_settings['try_searching_again_label']);
            $new_labels_settings->search_suggestions_label = stripslashes($new_settings['search_suggestions_label']);
            $new_labels_settings->search_recommendations_label = stripslashes($new_settings['search_recommendations_label']);
            // Update the data
            $config_data->labels_configuration = $new_labels_settings;
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

    public function get( ){
        $config_data_promise = $this->get_snippet_config->execute($this->plugin_utils->getStore());
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
                $new_configuration = new ImpreseeSnippetConfiguration;
                $new_configuration->labels_configuration = new ImpreseeSnippetLabelsConfiguration;
                return $new_configuration;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        $labels_config = $config_data->labels_configuration;
        $data_array = array(
            $this->config_section_id => array(
                'results_title' => $labels_config->search_results_title,
                'search_button_label' => $labels_config->search_button_label,
                'error_exclamation' => $labels_config->oops_exclamation,
                'error_title' => $labels_config->error_title,
                'error_body' => $labels_config->error_message,
                'drag_and_drop_title' => $labels_config->drag_and_drop_image_title,
                'drag_and_drop_body'  => $labels_config->drag_and_drop_image_body,
                'custom_crop_label'   => $labels_config->custom_crop_label,
                'start_writing_label' => $labels_config->start_writing_label,
                'search_by_photo_label' => $labels_config->search_by_photo_label,
                'search_by_sketch_label' => $labels_config->search_by_sketch_label,
                'see_all_results_label' => $labels_config->see_all_results_label,
                'no_matching_results'  => $labels_config->no_matching_results,
                'on_sale_label'  => $labels_config->on_sale_label,
                'result_title_search_by_text' => $labels_config->result_title_search_by_text,
                'number_of_results_label_desktop' => $labels_config->number_of_results_label_desktop,
                'number_of_results_label_mobile' => $labels_config->number_of_results_label_mobile,
                'filters_title_label_mobile' => $labels_config->filters_title_label_mobile,
                'clear_filters_label' => $labels_config->clear_filters_label,
                'sort_by_label' => $labels_config->sort_by_label,
                'apply_filters_label_mobile' => $labels_config->apply_filters_label_mobile,
                'try_searching_again_label' => $labels_config->try_searching_again_label,
                'search_suggestions_label' => $labels->search_suggestions_label,
                'search_recommendations_label' => $labels->search_recommendations_label,
            )
        );
        return $data_array;
    }


    public function saveFormAndRedirect( ){
        $page_id = $this->plugin_utils->getPluginPageId();
        $tab = SettingsNames::LABELS;
        $error_update = "";
        $success = $this->save($_POST);
        if (!$success){
            $error_update = urlencode("We could not update your configuration. Please try again later.");
        }
        wp_redirect(admin_url("admin.php?page={$page_id}&tab={$tab}&error={$error_update}"));
    }

    /**
    * Add labels settings using add_settings_field
    */
    public function init_settings() {
        $config_data_promise = $this->get_snippet_config->execute($this->plugin_utils->getStore());
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
                $new_configuration = new ImpreseeSnippetConfiguration;
                $new_configuration->labels_configuration = new ImpreseeSnippetLabelsConfiguration;
                return $new_configuration;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        $labels_config = $config_data->labels_configuration;
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'labels_settings',
                'title'     => 'Labels settings',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'results_title',
                'title'     => 'Search results title',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'results_title',
                    'current' => stripslashes($labels_config->search_results_title),
                    'description' => 'Text that will appear above the search results'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_button_label',
                'title'     => 'Search button label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_button_label',
                    'current' => stripslashes($labels_config->search_button_label),
                    'description' => 'Text that will on the search button'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'drag_and_drop_title',
                'title'     => 'Drag and drop screen header',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'drag_and_drop_title',
                    'current' => stripslashes($labels_config->drag_and_drop_image_title),
                    'description' => 'Text that goes on the Drag & drop an image screen as a header'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'drag_and_drop_body',
                'title'     => 'Drag and drop screen body',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'drag_and_drop_body',
                    'current' => stripslashes($labels_config->drag_and_drop_image_body),
                    'description' => 'Text that goes on the Drag & drop an image screen as a body'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'error_exclamation',
                'title'     => 'Error exclamation',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'error_exclamation',
                    'current' => stripslashes($labels_config->oops_exclamation),
                    'description' => 'Exclamtion displayed when the search process fails'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'error_title',
                'title'     => 'Error title',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'error_title',
                    'current' => stripslashes($labels_config->error_title),
                    'description' => 'Main text that is displayed when the search process fails'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'error_body',
                'title'     => 'Error body',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'error_body',
                    'current' => stripslashes($labels_config->error_message),
                    'description' => 'Subtitle that is displayed when the search process fails'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'custom_crop_label',
                'title'     => 'Custom crop label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'custom_crop_label',
                    'current' => stripslashes($labels_config->custom_crop_label),
                    'description' => 'Label shown when cropping a query image in the search results screen'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'start_writing_label',
                'title'     => 'Start writing text',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'start_writing_label',
                    'current' => stripslashes($labels_config->start_writing_label),
                    'description' => 'Label displayed before the person starts writing'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_by_photo_label',
                'title'     => 'Search by photo label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_by_photo_label',
                    'current' => stripslashes($labels_config->search_by_photo_label),
                    'description' => 'Search by picture label show in the search by text dropdown menu'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_by_sketch_label',
                'title'     => 'Search by sketch label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_by_sketch_label',
                    'current' => stripslashes($labels_config->search_by_sketch_label),
                    'description' => 'Search by sketch label show in the search by text dropdown menu'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'see_all_results_label',
                'title'     => 'See all results',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'see_all_results_label',
                    'current' => stripslashes($labels_config->see_all_results_label),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'no_matching_results',
                'title'     => 'No matching results',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'no_matching_results',
                    'current' => stripslashes($labels_config->no_matching_results),
                    'description' => 'Zero hits label'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'on_sale_label',
                'title'     => 'On sale',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'on_sale_label',
                    'current' => stripslashes($labels_config->on_sale_label),
                    'description' => 'Label that shows on products that are on sale'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'result_title_search_by_text',
                'title'     => 'Search by text result title',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'result_title_search_by_text',
                    'current' => stripslashes($labels_config->result_title_search_by_text),
                    'description' => 'Title of the search by text results'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'number_of_results_label_desktop',
                'title'     => 'Number of results (desktop version)',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'number_of_results_label_desktop',
                    'current' => stripslashes($labels_config->number_of_results_label_desktop),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'number_of_results_label_mobile',
                'title'     => 'Number of results (mobile version)',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'number_of_results_label_mobile',
                    'current' => stripslashes($labels_config->number_of_results_label_mobile),
                    'description' => 'Please keep both placeholders when modifying the text'
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'filters_title_label_mobile',
                'title'     => 'Filters popover title (mobile version)',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'filters_title_label_mobile',
                    'current' => stripslashes($labels_config->filters_title_label_mobile),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'clear_filters_label',
                'title'     => 'Clear filters (mobile version)',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'clear_filters_label',
                    'current' => stripslashes($labels_config->clear_filters_label),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'sort_by_label',
                'title'     => 'Sort options label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'sort_by_label',
                    'current' => stripslashes($labels_config->sort_by_label),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'apply_filters_label_mobile',
                'title'     => 'Apply filters label (mobile version)',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'apply_filters_label_mobile',
                    'current' => stripslashes($labels_config->apply_filters_label_mobile),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'try_searching_again_label',
                'title'     => 'Try searching again label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'try_searching_again_label',
                    'current' => stripslashes($labels_config->try_searching_again_label),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_suggestions_label',
                'title'     => 'Search suggestions label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_suggestions_label',
                    'current' => stripslashes($labels_config->search_suggestions_label),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_recommendations_label',
                'title'     => 'Search recommendations title label',
                'callback'  => 'text_input',
                'section'   => 'labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_recommendations_label',
                    'current' => stripslashes($labels_config->search_recommendations_label),
                ),
            ),
        );
        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}