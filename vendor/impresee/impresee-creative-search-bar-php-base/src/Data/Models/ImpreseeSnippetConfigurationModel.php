<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class ImpreseeSnippetConfigurationModel implements Serializable {
    const FLOATING_BUTTON_BOTTOM_LEFT = 1;
    const FLOATING_BUTTON_TOP_LEFT = 2;
    const FLOATING_BUTTON_MIDDLE_LEFT = 3;
    const FLOATING_BUTTON_BOTTOM_RIGHT = 4;
    const FLOATING_BUTTON_TOP_RIGHT = 5;
    const FLOATING_BUTTON_MIDDLE_RIGHT = 6;

    public $search_by_photo_icon_url;
    public $search_by_sketch_icon_url;
    public $load_after_page_render;
    public $decimal_separator;
    public $container_selector;
    public $main_color;
    public $add_search_data_to_url;
    public $images_only_loaded_from_camera;
    public $disable_image_crop;
    public $price_fraction_digit_number;
    public $currency_symbol_at_the_end;
    public $on_sale_label_color;
    public $search_results_title;
    public $search_button_label;
    public $oops_exclamation;
    public $error_title;
    public $error_message;
    public $drag_and_drop_image_title;
    public $drag_and_drop_image_body;
    public $custom_crop_label;
    public $start_writing_label;
    public $currency_symbol;
    public $search_by_photo_label;
    public $search_by_sketch_label;
    public $see_all_results_label;
    public $no_matching_results;
    public $on_sale_label;
    public $result_title_search_by_text;
    public $number_of_results_label_desktop;
    public $number_of_results_label_mobile;
    public $filters_title_label_mobile;
    public $clear_filters_label;
    public $sort_by_label;
    public $apply_filters_label_mobile;
    public $try_searching_again_label;
    public $search_suggestions_label;
    public $search_recommendations_label;
    public $use_text;
    public $search_delay_millis;
    public $mobile_instant_as_grid;
    public $full_text_search_results_container;
    public $compute_results_top_position_from;
    public $use_instant_full_search;
    public $use_floating_search_bar_button;
    public $floating_button_location;
    public $search_bar_selector;
    public $use_search_suggestions;
    public $use_photo_search;
    public $use_sketch_search;


    private function parseButtonLocation(int $location){
        switch ($location) {
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_LEFT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_LEFT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_LEFT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_LEFT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_RIGHT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_RIGHT;
            case ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_RIGHT:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_RIGHT;
            default:
                return ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        }
    }

    public function toArray(){
        return array(
            'load_after_page_render' => $this->load_after_page_render,
            'decimal_separator' => $this->decimal_separator,
            'container_selector' => $this->container_selector,
            'main_color' => $this->main_color,
            'add_search_data_to_url' => $this->add_search_data_to_url,
            'images_only_loaded_from_camera' => $this->images_only_loaded_from_camera,
            'disable_image_crop' => $this->disable_image_crop,
            'price_fraction_digit_number' => $this->price_fraction_digit_number,
            'currency_symbol_at_the_end' => $this->currency_symbol_at_the_end,
            'on_sale_label_color' => $this->on_sale_label_color,
            'search_results_title' => $this->search_results_title,
            'search_button_label' => $this->search_button_label,
            'oops_exclamation' => $this->oops_exclamation,
            'error_title' => $this->error_title,
            'error_message' => $this->error_message,
            'drag_and_drop_image_title' => $this->drag_and_drop_image_title,
            'drag_and_drop_image_body' => $this->drag_and_drop_image_body,
            'custom_crop_label' => $this->custom_crop_label,
            'start_writing_label' => $this->start_writing_label,
            'currency_symbol' => $this->currency_symbol,
            'search_by_photo_label' => $this->search_by_photo_label,
            'search_by_sketch_label' => $this->search_by_sketch_label,
            'see_all_results_label' => $this->see_all_results_label,
            'no_matching_results' => $this->no_matching_results,
            'on_sale_label' => $this->on_sale_label,
            'result_title_search_by_text' => $this->result_title_search_by_text,
            'number_of_results_label_desktop' => $this->number_of_results_label_desktop,
            'number_of_results_label_mobile' => $this->number_of_results_label_mobile,
            'filters_title_label_mobile' => $this->filters_title_label_mobile,
            'clear_filters_label' => $this->clear_filters_label,
            'sort_by_label' => $this->sort_by_label,
            'apply_filters_label_mobile' => $this->apply_filters_label_mobile,
            'try_searching_again_label' => $this->try_searching_again_label,
            'search_suggestions_label' => $this->search_suggestions_label,
            'search_recommendations_label' => $this->search_recommendations_label,
            'use_text' => $this->use_text,
            'search_delay_millis' => $this->search_delay_millis,
            'full_text_search_results_container' => $this->full_text_search_results_container,
            'compute_results_top_position_from' => $this->compute_results_top_position_from,
            'use_instant_full_search' => $this->use_instant_full_search,
            'use_floating_search_bar_button' => $this->use_floating_search_bar_button,
            'floating_button_location' => $this->floating_button_location,
            'search_bar_selector' => $this->search_bar_selector,
            'use_search_suggestions' => $this->use_search_suggestions,
            'search_by_photo_icon_url' => $this->search_by_photo_icon_url,
            'search_by_sketch_icon_url' => $this->search_by_sketch_icon_url,
            'use_photo_search' => $this->use_photo_search,
            'use_sketch_search' => $this->use_sketch_search,
            'mobile_instant_as_grid' => $this->mobile_instant_as_grid,
        );
    }

    public function loadDataFromArray(Array $array){
        if (!array_key_exists('load_after_page_render',$array) || !array_key_exists('container_selector',$array)
        || !array_key_exists('decimal_separator',$array) ||
        !array_key_exists('main_color',$array) || !array_key_exists('add_search_data_to_url',$array) ||
        !array_key_exists('images_only_loaded_from_camera',$array) || !array_key_exists('disable_image_crop',$array) ||
        !array_key_exists('price_fraction_digit_number',$array) || !array_key_exists('currency_symbol_at_the_end',$array) ||
        !array_key_exists('on_sale_label_color',$array) || !array_key_exists('search_results_title',$array) ||
        !array_key_exists('search_button_label',$array) || !array_key_exists('oops_exclamation',$array) ||
        !array_key_exists('error_title',$array) || !array_key_exists('error_message',$array) ||
        !array_key_exists('drag_and_drop_image_title',$array) || !array_key_exists('drag_and_drop_image_body',$array) ||
        !array_key_exists('custom_crop_label',$array) || !array_key_exists('start_writing_label',$array) ||
        !array_key_exists('currency_symbol',$array) || !array_key_exists('search_by_photo_label',$array) ||
        !array_key_exists('search_by_sketch_label',$array) || !array_key_exists('see_all_results_label',$array) ||
        !array_key_exists('no_matching_results',$array) || !array_key_exists('on_sale_label',$array) ||
        !array_key_exists('result_title_search_by_text',$array) || !array_key_exists('number_of_results_label_desktop',$array) ||
        !array_key_exists('number_of_results_label_mobile',$array) || !array_key_exists('filters_title_label_mobile',$array) ||
        !array_key_exists('clear_filters_label',$array) || !array_key_exists('sort_by_label',$array) ||
        !array_key_exists('apply_filters_label_mobile',$array) || !array_key_exists('try_searching_again_label',$array) ||
        !array_key_exists('use_text',$array) || !array_key_exists('search_delay_millis',$array) ||
        !array_key_exists('full_text_search_results_container',$array) || !array_key_exists('compute_results_top_position_from',$array) || !array_key_exists('use_instant_full_search',$array) ||
        !array_key_exists('use_floating_search_bar_button',$array) || !array_key_exists('floating_button_location',$array) || !array_key_exists('search_bar_selector', $array) ) {
            throw new NoDataException;
        }
        $this->load_after_page_render = $array['load_after_page_render'];
        $this->decimal_separator = $array['decimal_separator'];
        $this->container_selector = $array['container_selector'];
        $this->main_color = $array['main_color'];
        $this->add_search_data_to_url = $array['add_search_data_to_url'];
        $this->images_only_loaded_from_camera = $array['images_only_loaded_from_camera'];
        $this->disable_image_crop = $array['disable_image_crop'];
        $this->price_fraction_digit_number = $array['price_fraction_digit_number'];
        $this->currency_symbol_at_the_end = $array['currency_symbol_at_the_end'];
        $this->on_sale_label_color = $array['on_sale_label_color'];
        $this->search_results_title = $array['search_results_title'];
        $this->search_button_label = $array['search_button_label'];
        $this->oops_exclamation = $array['oops_exclamation'];
        $this->error_title = $array['error_title'];
        $this->error_message = $array['error_message'];
        $this->drag_and_drop_image_title = $array['drag_and_drop_image_title'];
        $this->drag_and_drop_image_body = $array['drag_and_drop_image_body'];
        $this->custom_crop_label = $array['custom_crop_label'];
        $this->start_writing_label = $array['start_writing_label'];
        $this->currency_symbol = $array['currency_symbol'];
        $this->search_by_photo_label = $array['search_by_photo_label'];
        $this->search_by_sketch_label = $array['search_by_sketch_label'];
        $this->see_all_results_label = $array['see_all_results_label'];
        $this->no_matching_results = $array['no_matching_results'];
        $this->on_sale_label = $array['on_sale_label'];
        $this->result_title_search_by_text = $array['result_title_search_by_text'];
        $this->number_of_results_label_desktop = $array['number_of_results_label_desktop'];
        $this->number_of_results_label_mobile = $array['number_of_results_label_mobile'];
        $this->filters_title_label_mobile = $array['filters_title_label_mobile'];
        $this->clear_filters_label = $array['clear_filters_label'];
        $this->sort_by_label = $array['sort_by_label'];
        $this->apply_filters_label_mobile = $array['apply_filters_label_mobile'];
        $this->try_searching_again_label = $array['try_searching_again_label'];
        $this->search_suggestions_label = !array_key_exists('search_suggestions_label',$array) ? 'Popular searches' : $array['search_suggestions_label'];
        $this->search_recommendations_label = !array_key_exists('search_recommendations_label',$array) ? 'Recommended products' : $array['search_recommendations_label'];
        $this->use_text = $array['use_text'];
        $this->search_delay_millis = $array['search_delay_millis'];
        $this->full_text_search_results_container = $array['full_text_search_results_container'];
        $this->compute_results_top_position_from = $array['compute_results_top_position_from'];
        $this->use_instant_full_search = $array['use_instant_full_search'];
        $this->use_floating_search_bar_button = $array['use_floating_search_bar_button'];
        $this->search_bar_selector = $array['search_bar_selector'];
        $this->floating_button_location = $this->parseButtonLocation($array['floating_button_location']);
        $this->use_search_suggestions = !array_key_exists('use_search_suggestions',$array) ? TRUE : $array['use_search_suggestions'];
        $this->search_by_photo_icon_url = !array_key_exists('search_by_photo_icon_url',$array) ? '' : $array['search_by_photo_icon_url'];
        $this->search_by_sketch_icon_url = !array_key_exists('search_by_sketch_icon_url',$array) ? '' : $array['search_by_sketch_icon_url'];
        $this->use_photo_search = !array_key_exists('use_photo_search',$array) ? TRUE : $array['use_photo_search'];
        $this->use_sketch_search = !array_key_exists('use_sketch_search',$array) ? TRUE : $array['use_sketch_search'];
        $this->mobile_instant_as_grid = !array_key_exists('mobile_instant_as_grid',$array) ? FALSE : $array['mobile_instant_as_grid'];
    }

    public function loadFromOldStorageArray(Array $array){
        if (!array_key_exists('currency_symbol',$array) ||!array_key_exists('results_title',$array) 
            || !array_key_exists('search_button_label',$array) || !array_key_exists('drag_and_drop_title',$array) 
            ||!array_key_exists('drag_and_drop_body',$array) || !array_key_exists('error_title',$array) 
            || !array_key_exists('error_body',$array) || !array_key_exists('impresee_main_color_picker',$array) 
            || !array_key_exists('impresee_only_camera',$array) 
            || !array_key_exists('impresee_disallow_crop',$array)){
            throw new NoDataException;
        }
        $this->load_after_page_render = FALSE;
        $this->decimal_separator = ",";
        $this->container_selector = '';
        $this->main_color = $array['impresee_main_color_picker'];
        $this->add_search_data_to_url = FALSE;
        $this->images_only_loaded_from_camera = $array['impresee_only_camera'];
        $this->disable_image_crop = $array['impresee_disallow_crop'];
        $this->price_fraction_digit_number = 2;
        $this->currency_symbol_at_the_end = FALSE;
        $this->on_sale_label_color = '';
        $this->search_results_title = $array['results_title'];
        $this->search_button_label = $array['search_button_label'];
        $this->oops_exclamation = '';
        $this->error_title = $array['error_title'];
        $this->error_message = $array['error_body'];
        $this->drag_and_drop_image_title = $array['drag_and_drop_title'];
        $this->drag_and_drop_image_body = $array['drag_and_drop_body'];
        $this->custom_crop_label = '';
        $this->start_writing_label = '';
        $this->currency_symbol = $array['currency_symbol'];
        $this->search_by_photo_label = 'Search by photo';
        $this->search_by_sketch_label = 'Search by sketch';
        $this->see_all_results_label = '';
        $this->no_matching_results = '';
        $this->on_sale_label = '';
        $this->result_title_search_by_text = '';
        $this->number_of_results_label_desktop = '';
        $this->number_of_results_label_mobile = '';
        $this->filters_title_label_mobile = '';
        $this->clear_filters_label = '';
        $this->sort_by_label = '';
        $this->apply_filters_label_mobile = '';
        $this->try_searching_again_label = '';
        $this->search_suggestions_label = 'Popular searches';
        $this->search_recommendations_label = 'Recommended products';
        $this->use_text = TRUE;
        $this->search_delay_millis = 300;
        $this->full_text_search_results_container = 'body';
        $this->compute_results_top_position_from = 'header';
        $this->use_instant_full_search = TRUE;
        $this->use_floating_search_bar_button = TRUE;
        $this->search_bar_selector = 'input[name=q],input[name=s]';
        $this->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        $this->use_search_suggestions = TRUE;
        $this->search_by_photo_icon_url = '';
        $this->search_by_sketch_icon_url = '';
        $this->use_photo_search = TRUE;
        $this->use_sketch_search = TRUE;
        $this->mobile_instant_as_grid = FALSE;
    }
}
