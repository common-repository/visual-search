<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetGeneralConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetLabelsConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetSearchByTextConfiguration;

class ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfiguration {

    public function mapFromFloatingButtonLocation(int $from_location){
        switch ($from_location) {
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT:
                return ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_LEFT:
                return ImpreseeSnippetSearchByTextConfiguration::TOP_LEFT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_LEFT:
                return ImpreseeSnippetSearchByTextConfiguration::MIDDLE_LEFT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT:
                return ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_RIGHT:
                return ImpreseeSnippetSearchByTextConfiguration::TOP_RIGHT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_RIGHT:
                return ImpreseeSnippetSearchByTextConfiguration::MIDDLE_RIGHT;
            default:
                return ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
        }
    }

    public function mapFrom(ImpreseeSnippetConfigurationModel $from){
        // General configuration
        $general_config = new ImpreseeSnippetGeneralConfiguration;
        $general_config->decimal_separator = $from->decimal_separator;
        $general_config->load_after_page_render = $from->load_after_page_render;
        $general_config->container_selector = $from->container_selector;
        $general_config->main_color = $from->main_color;
        $general_config->add_search_data_to_url = $from->add_search_data_to_url;
        $general_config->images_only_loaded_from_camera = $from->images_only_loaded_from_camera;
        $general_config->disable_image_crop = $from->disable_image_crop;
        $general_config->price_fraction_digit_number = $from->price_fraction_digit_number;
        $general_config->currency_symbol_at_the_end = $from->currency_symbol_at_the_end;
        $general_config->on_sale_label_color = $from->on_sale_label_color;
        $general_config->search_by_photo_icon_url =  $from->search_by_photo_icon_url;
        $general_config->search_by_sketch_icon_url = $from->search_by_sketch_icon_url;
        $general_config->use_photo_search =  $from->use_photo_search;
        $general_config->use_sketch_search = $from->use_sketch_search;
        // Labels
        $labels_config = new ImpreseeSnippetLabelsConfiguration;
        $labels_config->search_results_title = $from->search_results_title;
        $labels_config->search_button_label = $from->search_button_label;
        $labels_config->oops_exclamation = $from->oops_exclamation;
        $labels_config->error_title = $from->error_title;
        $labels_config->error_message = $from->error_message;
        $labels_config->drag_and_drop_image_title = $from->drag_and_drop_image_title;
        $labels_config->drag_and_drop_image_body = $from->drag_and_drop_image_body;
        $labels_config->custom_crop_label = $from->custom_crop_label;
        $labels_config->start_writing_label = $from->start_writing_label;
        $labels_config->currency_symbol = $from->currency_symbol;
        $labels_config->search_by_photo_label = $from->search_by_photo_label;
        $labels_config->search_by_sketch_label = $from->search_by_sketch_label;
        $labels_config->see_all_results_label = $from->see_all_results_label;
        $labels_config->no_matching_results = $from->no_matching_results;
        $labels_config->on_sale_label = $from->on_sale_label;
        $labels_config->result_title_search_by_text = $from->result_title_search_by_text;
        $labels_config->number_of_results_label_desktop = $from->number_of_results_label_desktop;
        $labels_config->number_of_results_label_mobile = $from->number_of_results_label_mobile;
        $labels_config->filters_title_label_mobile = $from->filters_title_label_mobile;
        $labels_config->clear_filters_label = $from->clear_filters_label;
        $labels_config->sort_by_label = $from->sort_by_label;
        $labels_config->apply_filters_label_mobile = $from->apply_filters_label_mobile;
        $labels_config->try_searching_again_label = $from->try_searching_again_label;
        $labels_config->search_suggestions_label = $from->search_suggestions_label;
        $labels_config->search_recommendations_label = $from->search_recommendations_label;
        // Search by text
        $search_by_text_config = new ImpreseeSnippetSearchByTextConfiguration;
        $search_by_text_config->use_text = $from->use_text;
        $search_by_text_config->search_delay_millis = $from->search_delay_millis;
        $search_by_text_config->full_text_search_results_container = $from->full_text_search_results_container;
        $search_by_text_config->compute_results_top_position_from = $from->compute_results_top_position_from;
        $search_by_text_config->use_instant_full_search = $from->use_instant_full_search;
        $search_by_text_config->use_floating_search_bar_button = $from->use_floating_search_bar_button;
        $search_by_text_config->floating_button_location = $this->mapFromFloatingButtonLocation(
            $from->floating_button_location
        );
        $search_by_text_config->search_bar_selector = $from->search_bar_selector;
        $search_by_text_config->use_search_suggestions = $from->use_search_suggestions;
        $search_by_text_config->mobile_instant_as_grid = $from->mobile_instant_as_grid;
        $configuration = new ImpreseeSnippetConfiguration;
        $configuration->general_configuration = $general_config;
        $configuration->labels_configuration = $labels_config;
        $configuration->search_by_text_configuration = $search_by_text_config;
        return $configuration;
    }

    public function mapToFloatingButtonLocation(String $to_location){
        switch ($to_location) {
            case ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
            case ImpreseeSnippetSearchByTextConfiguration::TOP_LEFT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_LEFT;
            case ImpreseeSnippetSearchByTextConfiguration::MIDDLE_LEFT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_LEFT;
            case ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
            case ImpreseeSnippetSearchByTextConfiguration::TOP_RIGHT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_RIGHT;
            case ImpreseeSnippetSearchByTextConfiguration::MIDDLE_RIGHT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_RIGHT;
            default:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        }
    }

    public function mapTo(ImpreseeSnippetConfiguration $to){
        $general_config = $to->general_configuration;
        $labels_config = $to->labels_configuration;
        $search_by_text_config = $to->search_by_text_configuration;
        $model = new ImpreseeSnippetConfigurationModel;
        // General
        $model->decimal_separator = $general_config->decimal_separator;
        $model->load_after_page_render = $general_config->load_after_page_render;
        $model->container_selector = $general_config->container_selector;
        $model->main_color = $general_config->main_color;
        $model->add_search_data_to_url = $general_config->add_search_data_to_url;
        $model->images_only_loaded_from_camera = $general_config->images_only_loaded_from_camera;
        $model->disable_image_crop = $general_config->disable_image_crop;
        $model->price_fraction_digit_number = $general_config->price_fraction_digit_number;
        $model->currency_symbol_at_the_end = $general_config->currency_symbol_at_the_end;
        $model->on_sale_label_color = $general_config->on_sale_label_color;
        $model->search_by_photo_icon_url =  $general_config->search_by_photo_icon_url;
        $model->search_by_sketch_icon_url = $general_config->search_by_sketch_icon_url;
        $model->use_photo_search =  $general_config->use_photo_search;
        $model->use_sketch_search = $general_config->use_sketch_search;
        // Labels
        $model->search_results_title = $labels_config->search_results_title;
        $model->search_button_label = $labels_config->search_button_label;
        $model->oops_exclamation = $labels_config->oops_exclamation;
        $model->error_title = $labels_config->error_title;
        $model->error_message = $labels_config->error_message;
        $model->drag_and_drop_image_title = $labels_config->drag_and_drop_image_title;
        $model->drag_and_drop_image_body = $labels_config->drag_and_drop_image_body;
        $model->custom_crop_label = $labels_config->custom_crop_label;
        $model->start_writing_label = $labels_config->start_writing_label;
        $model->currency_symbol = $labels_config->currency_symbol;
        $model->search_by_photo_label = $labels_config->search_by_photo_label;
        $model->search_by_sketch_label = $labels_config->search_by_sketch_label;
        $model->see_all_results_label = $labels_config->see_all_results_label;
        $model->no_matching_results = $labels_config->no_matching_results;
        $model->on_sale_label = $labels_config->on_sale_label;
        $model->result_title_search_by_text = $labels_config->result_title_search_by_text;
        $model->number_of_results_label_desktop = $labels_config->number_of_results_label_desktop;
        $model->number_of_results_label_mobile = $labels_config->number_of_results_label_mobile;
        $model->filters_title_label_mobile = $labels_config->filters_title_label_mobile;
        $model->clear_filters_label = $labels_config->clear_filters_label;
        $model->sort_by_label = $labels_config->sort_by_label;
        $model->apply_filters_label_mobile = $labels_config->apply_filters_label_mobile;
        $model->try_searching_again_label = $labels_config->try_searching_again_label;
        $model->search_suggestions_label = $labels_config->search_suggestions_label;
        $model->search_recommendations_label = $labels_config->search_recommendations_label;
        // Search by text
        $model->use_text = $search_by_text_config->use_text;
        $model->search_delay_millis = $search_by_text_config->search_delay_millis;
        $model->full_text_search_results_container = $search_by_text_config->full_text_search_results_container;
        $model->compute_results_top_position_from = $search_by_text_config->compute_results_top_position_from;
        $model->use_instant_full_search = $search_by_text_config->use_instant_full_search;
        $model->use_floating_search_bar_button = $search_by_text_config->use_floating_search_bar_button;
        $model->floating_button_location = $this->mapToFloatingButtonLocation(
            $search_by_text_config->floating_button_location
        );
        $model->mobile_instant_as_grid = $search_by_text_config->mobile_instant_as_grid;
        $model->search_bar_selector = $search_by_text_config->search_bar_selector;
        $model->use_search_suggestions = $search_by_text_config->use_search_suggestions;
        return $model;
    }
}