<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayThemeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayLabelsConfiguration;

class HolidayConfigModel2HolidayConfigurationMapper {
    
    public function mapFrom(HolidayConfigurationModel $model){
        $labels = new HolidayLabelsConfiguration;
        $labels->pop_up_title = $model->pop_up_title;
        $labels->pop_up_text = $model->pop_up_text;
        $labels->searchbar_placeholder = $model->searchbar_placeholder;
        $labels->search_drawing_button = $model->search_drawing_button;
        $labels->search_photo_button = $model->search_photo_button;
        $labels->search_dropdown_label = $model->search_dropdown_label;
        $labels->to_label_letter = $model->to_label_letter;
        $labels->from_label_letter = $model->from_label_letter;
        $labels->placeholder_message_letter = $model->placeholder_message_letter;
        $labels->title_canvas = $model->title_canvas;
        $labels->search_button_canvas = $model->search_button_canvas;
        $labels->button_in_product_page = $model->button_in_product_page;
        $labels->search_results_title = $model->search_results_title;
        $labels->results_title_for_text_search = $model->results_title_for_text_search;
        $labels->christmas_letter_share_message = $model->christmas_letter_share_message;
        $labels->christmas_letter_share = $model->christmas_letter_share;
        $labels->christmas_letter_receiver_button = $model->christmas_letter_receiver_button;
        $config = new HolidayThemeConfiguration;
        $config->is_mode_active = $model->is_mode_active;
        switch ($model->theme) {
            case HolidayConfigurationModel::ACCENT:
                $config->theme = HolidayThemeConfiguration::ACCENT_THEME;
                break;
            case HolidayConfigurationModel::NEUTRAL:
                $config->theme = HolidayThemeConfiguration::NEUTRAL_THEME;
                break;
            default:
                $config->theme = HolidayThemeConfiguration::ACCENT_THEME;
                break;
        }
        $config->automatic_popup = $model->automatic_popup;
        $config->add_style_to_search_bar = $model->add_style_to_search_bar;
        $config->store_logo_url = $model->store_logo_url ? $model->store_logo_url : "";
        $mapped_configuration = new HolidayConfiguration;
        $mapped_configuration->config_theme = $config;
        $mapped_configuration->labels_configuration = $labels;
        return $mapped_configuration;
    } 

    public function mapTo(HolidayConfiguration $configuration){
        $theme_config = $configuration->config_theme;
        $labels = $configuration->labels_configuration;
        $model = new HolidayConfigurationModel;
        $model->is_mode_active = $theme_config->is_mode_active;
        switch ($theme_config->theme) {
            case HolidayThemeConfiguration::ACCENT_THEME:
                $model->theme = HolidayConfigurationModel::ACCENT;
                break;
            case HolidayThemeConfiguration::NEUTRAL_THEME:
                $model->theme = HolidayConfigurationModel::NEUTRAL;
                break;
            default:
                $model->theme = HolidayConfigurationModel::ACCENT;
                break;
        }
        $model->store_logo_url = $theme_config->store_logo_url;
        $model->automatic_popup = $theme_config->automatic_popup;
        $model->add_style_to_search_bar = $theme_config->add_style_to_search_bar;
        $model->pop_up_title = $labels->pop_up_title;
        $model->pop_up_text = $labels->pop_up_text;
        $model->searchbar_placeholder = $labels->searchbar_placeholder;
        $model->search_drawing_button = $labels->search_drawing_button;
        $model->search_photo_button = $labels->search_photo_button;
        $model->search_dropdown_label = $labels->search_dropdown_label;
        $model->to_label_letter = $labels->to_label_letter;
        $model->from_label_letter = $labels->from_label_letter;
        $model->placeholder_message_letter = $labels->placeholder_message_letter;
        $model->title_canvas = $labels->title_canvas;
        $model->search_button_canvas = $labels->search_button_canvas;
        $model->button_in_product_page = $labels->button_in_product_page;
        $model->search_results_title = $labels->search_results_title;
        $model->results_title_for_text_search = $labels->results_title_for_text_search;
        $model->christmas_letter_share_message = $labels->christmas_letter_share_message;
        $model->christmas_letter_share = $labels->christmas_letter_share;
        $model->christmas_letter_receiver_button = $labels->christmas_letter_receiver_button;
        return $model;
    }    
}