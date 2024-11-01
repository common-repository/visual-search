<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;

class CustomCodeModel2CustomCodeConfigurationMapper {
    
    public function mapFrom(CustomCodeModel $from){
        $configuration = new CustomCodeConfiguration;
        $configuration->js_add_buttons = $from->js_add_buttons;
        $configuration->css_style_buttons = $from->css_style_buttons;
        $configuration->js_after_load_results_code = $from->js_after_load_results_code;
        $configuration->js_before_load_results_code = $from->js_before_load_results_code;
        $configuration->js_search_failed_code = $from->js_search_failed_code;
        $configuration->js_press_see_all_code = $from->js_press_see_all_code;
        $configuration->js_close_text_results_code = $from->js_close_text_results_code;
        $configuration->js_on_open_text_dropdown_code = $from->js_on_open_text_dropdown_code;
        return $configuration;
    }

    public function mapTo(CustomCodeConfiguration $to){
        $model = new CustomCodeModel;
        $model->js_add_buttons = $to->js_add_buttons;
        $model->css_style_buttons = $to->css_style_buttons;
        $model->js_after_load_results_code = $to->js_after_load_results_code;
        $model->js_before_load_results_code = $to->js_before_load_results_code;
        $model->js_search_failed_code = $to->js_search_failed_code;
        $model->js_press_see_all_code = $to->js_press_see_all_code;
        $model->js_close_text_results_code = $to->js_close_text_results_code;
        $model->js_on_open_text_dropdown_code = $to->js_on_open_text_dropdown_code;
        return $model;
    }
}