<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class ImpreseeSnippetConfigurationModelTest extends TestCase {

    public function testToArray(){
        $model = new ImpreseeSnippetConfigurationModel;
        $model->use_photo_search = TRUE;
        $model->use_sketch_search = TRUE;
        $model->search_by_photo_icon_url = 'value 25';
        $model->search_by_sketch_icon_url = 'value 26';
        $model->load_after_page_render = FALSE;
        $model->decimal_separator = ",";
        $model->container_selector = '.value';
        $model->main_color = '#9CD333';
        $model->add_search_data_to_url = TRUE;
        $model->images_only_loaded_from_camera = FALSE;
        $model->disable_image_crop = FALSE;
        $model->price_fraction_digit_number = 2;
        $model->currency_symbol_at_the_end = FALSE;
        $model->on_sale_label_color = '#FF0000';
        $model->search_results_title = 'value 1';
        $model->search_button_label = 'value 2';
        $model->oops_exclamation = 'value 3';
        $model->error_title = 'value 4';
        $model->error_message = 'value 5';
        $model->drag_and_drop_image_title = 'value 6';
        $model->drag_and_drop_image_body = 'value 7';
        $model->custom_crop_label = 'value 8';
        $model->start_writing_label = 'value 9';
        $model->currency_symbol = '$';
        $model->search_by_photo_label = 'value 10';
        $model->search_by_sketch_label = 'value 11';
        $model->see_all_results_label = 'value 12';
        $model->no_matching_results = 'value 13';
        $model->on_sale_label = 'value 14';
        $model->result_title_search_by_text = 'value 15';
        $model->number_of_results_label_desktop = 'value 16';
        $model->number_of_results_label_mobile = 'value 17';
        $model->filters_title_label_mobile = 'value 18';
        $model->clear_filters_label = 'value 19';
        $model->sort_by_label = 'value 20';
        $model->apply_filters_label_mobile = 'value 21';
        $model->try_searching_again_label = 'value 22';
        $model->search_suggestions_label = 'value 23';
        $model->search_recommendations_label = 'value 24';
        $model->use_text = TRUE;
        $model->search_delay_millis = 300;
        $model->full_text_search_results_container = '.container';
        $model->compute_results_top_position_from = 'header';
        $model->use_instant_full_search = TRUE;
        $model->use_floating_search_bar_button = TRUE;
        $model->search_bar_selector = 'input[name=q]';
        $model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $model->use_search_suggestions = FALSE;
        $model->mobile_instant_as_grid = TRUE;
        $expected_array = array(
            'load_after_page_render' => FALSE,
            'decimal_separator' => ',',
            'container_selector' => '.value',
            'main_color' => '#9CD333',
            'add_search_data_to_url' => TRUE,
            'images_only_loaded_from_camera' => FALSE,
            'disable_image_crop' => FALSE,
            'price_fraction_digit_number' => 2,
            'currency_symbol_at_the_end' => FALSE,
            'on_sale_label_color' => '#FF0000',
            'search_results_title' => 'value 1',
            'search_button_label' => 'value 2',
            'oops_exclamation' => 'value 3',
            'error_title' => 'value 4',
            'error_message' => 'value 5',
            'drag_and_drop_image_title' => 'value 6',
            'drag_and_drop_image_body' => 'value 7',
            'custom_crop_label' => 'value 8',
            'start_writing_label' => 'value 9',
            'currency_symbol' => '$',
            'search_by_photo_label' => 'value 10',
            'search_by_sketch_label' => 'value 11',
            'see_all_results_label' => 'value 12',
            'no_matching_results' => 'value 13',
            'on_sale_label' => 'value 14',
            'result_title_search_by_text' => 'value 15',
            'number_of_results_label_desktop' => 'value 16',
            'number_of_results_label_mobile' => 'value 17',
            'filters_title_label_mobile' => 'value 18',
            'clear_filters_label' => 'value 19',
            'sort_by_label' => 'value 20',
            'apply_filters_label_mobile' => 'value 21',
            'try_searching_again_label' => 'value 22',
            'search_suggestions_label' => 'value 23',
            'search_recommendations_label' => 'value 24',
            'use_text' => TRUE,
            'search_delay_millis' => 300,
            'full_text_search_results_container' => '.container',
            'compute_results_top_position_from' => 'header',
            'use_instant_full_search' => TRUE,
            'use_floating_search_bar_button' => TRUE,
            'floating_button_location' => 4,
            'search_bar_selector' => 'input[name=q]',
            'use_search_suggestions' => FALSE,
            'search_by_photo_icon_url' => 'value 25',
            'search_by_sketch_icon_url' => 'value 26',
            'use_photo_search' => TRUE,
            'use_sketch_search' => TRUE,
            'mobile_instant_as_grid' =>TRUE,
        );
        $this->assertEquals(
            $model->toArray(),
            $expected_array
        );
    }

    public function testLoadFromArray(){
        $data_array = array(
            'load_after_page_render' => FALSE,
            'decimal_separator' => ",",
            'container_selector' => '.value',
            'main_color' => '#9CD333',
            'add_search_data_to_url' => TRUE,
            'images_only_loaded_from_camera' => FALSE,
            'disable_image_crop' => FALSE,
            'price_fraction_digit_number' => 2,
            'currency_symbol_at_the_end' => FALSE,
            'on_sale_label_color' => '#FF0000',
            'search_results_title' => 'value 1',
            'search_button_label' => 'value 2',
            'oops_exclamation' => 'value 3',
            'error_title' => 'value 4',
            'error_message' => 'value 5',
            'drag_and_drop_image_title' => 'value 6',
            'drag_and_drop_image_body' => 'value 7',
            'custom_crop_label' => 'value 8',
            'start_writing_label' => 'value 9',
            'currency_symbol' => '$',
            'search_by_photo_label' => 'value 10',
            'search_by_sketch_label' => 'value 11',
            'see_all_results_label' => 'value 12',
            'no_matching_results' => 'value 13',
            'on_sale_label' => 'value 14',
            'result_title_search_by_text' => 'value 15',
            'number_of_results_label_desktop' => 'value 16',
            'number_of_results_label_mobile' => 'value 17',
            'filters_title_label_mobile' => 'value 18',
            'clear_filters_label' => 'value 19',
            'sort_by_label' => 'value 20',
            'apply_filters_label_mobile' => 'value 21',
            'try_searching_again_label' => 'value 22',
            'search_suggestions_label' => 'value 23',
            'search_recommendations_label' => 'value 24',
            'use_text' => TRUE,
            'search_delay_millis' => 300,
            'full_text_search_results_container' => '.container',
            'compute_results_top_position_from' => 'header',
            'use_instant_full_search' => TRUE,
            'use_floating_search_bar_button' => TRUE,
            'floating_button_location' => 4,
            'search_bar_selector' => 'input[name=q]',
            'search_by_photo_icon_url' => 'value 25',
            'search_by_sketch_icon_url' => 'value 26',
        );
        $expected_model = new ImpreseeSnippetConfigurationModel;
        $expected_model->search_by_photo_icon_url = 'value 25';
        $expected_model->search_by_sketch_icon_url = 'value 26';
        $expected_model->use_photo_search = TRUE;
        $expected_model->use_sketch_search = TRUE;
        $expected_model->decimal_separator = ",";
        $expected_model->load_after_page_render = FALSE;
        $expected_model->container_selector = '.value';
        $expected_model->main_color = '#9CD333';
        $expected_model->add_search_data_to_url = TRUE;
        $expected_model->images_only_loaded_from_camera = FALSE;
        $expected_model->disable_image_crop = FALSE;
        $expected_model->price_fraction_digit_number = 2;
        $expected_model->currency_symbol_at_the_end = FALSE;
        $expected_model->on_sale_label_color = '#FF0000';
        $expected_model->search_results_title = 'value 1';
        $expected_model->search_button_label = 'value 2';
        $expected_model->oops_exclamation = 'value 3';
        $expected_model->error_title = 'value 4';
        $expected_model->error_message = 'value 5';
        $expected_model->drag_and_drop_image_title = 'value 6';
        $expected_model->drag_and_drop_image_body = 'value 7';
        $expected_model->custom_crop_label = 'value 8';
        $expected_model->start_writing_label = 'value 9';
        $expected_model->currency_symbol = '$';
        $expected_model->search_by_photo_label = 'value 10';
        $expected_model->search_by_sketch_label = 'value 11';
        $expected_model->see_all_results_label = 'value 12';
        $expected_model->no_matching_results = 'value 13';
        $expected_model->on_sale_label = 'value 14';
        $expected_model->result_title_search_by_text = 'value 15';
        $expected_model->number_of_results_label_desktop = 'value 16';
        $expected_model->number_of_results_label_mobile = 'value 17';
        $expected_model->filters_title_label_mobile = 'value 18';
        $expected_model->clear_filters_label = 'value 19';
        $expected_model->sort_by_label = 'value 20';
        $expected_model->apply_filters_label_mobile = 'value 21';
        $expected_model->try_searching_again_label = 'value 22';
        $expected_model->search_suggestions_label = 'value 23';
        $expected_model->search_recommendations_label = 'value 24';
        $expected_model->use_text = TRUE;
        $expected_model->search_delay_millis = 300;
        $expected_model->full_text_search_results_container = '.container';
        $expected_model->compute_results_top_position_from = 'header';
        $expected_model->use_instant_full_search = TRUE;
        $expected_model->use_floating_search_bar_button = TRUE;
        $expected_model->search_bar_selector = 'input[name=q]';
        $expected_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $expected_model->use_search_suggestions = TRUE;
        $empty_model = new ImpreseeSnippetConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
        $this->assertEquals(
            $empty_model,
            $expected_model
        );
    }
    public function testLoadFromArrayIncompleteData(){
        $data_array = array();
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeSnippetConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testLoadFromOldFormatArray(){
        $data_array = array(
            'currency_symbol' => '$',
            'results_title' => 'Results',
            'impresee_main_color_picker' => '#9CD333',
            'search_button_label' => 'Search',
            'impresee_disallow_crop' => FALSE,
            'impresee_only_camera' => FALSE,
            'drag_and_drop_title' => 'Drag and drop',
            'drag_and_drop_body' => 'Body',
            'error_body' => "Error",
            'error_title' => 'Error title'
        );
        $expected_model = new ImpreseeSnippetConfigurationModel;
        $expected_model->use_photo_search = TRUE;
        $expected_model->use_sketch_search = TRUE;
        $expected_model->search_by_photo_icon_url = '';
        $expected_model->search_by_sketch_icon_url = '';
        $expected_model->load_after_page_render = FALSE;
        $expected_model->decimal_separator = ",";
        $expected_model->container_selector = '';
        $expected_model->main_color = '#9CD333';
        $expected_model->add_search_data_to_url = FALSE;
        $expected_model->images_only_loaded_from_camera = FALSE;
        $expected_model->disable_image_crop = FALSE;
        $expected_model->price_fraction_digit_number = 2;
        $expected_model->currency_symbol_at_the_end = FALSE;
        $expected_model->on_sale_label_color = '';
        $expected_model->search_results_title = 'Results';
        $expected_model->search_button_label = 'Search';
        $expected_model->oops_exclamation = '';
        $expected_model->error_title = 'Error title';
        $expected_model->error_message = 'Error';
        $expected_model->drag_and_drop_image_title = 'Drag and drop';
        $expected_model->drag_and_drop_image_body = 'Body';
        $expected_model->custom_crop_label = '';
        $expected_model->start_writing_label = '';
        $expected_model->currency_symbol = '$';
        $expected_model->search_by_photo_label = 'Search by photo';
        $expected_model->search_by_sketch_label = 'Search by sketch';
        $expected_model->see_all_results_label = '';
        $expected_model->no_matching_results = '';
        $expected_model->on_sale_label = '';
        $expected_model->result_title_search_by_text = '';
        $expected_model->number_of_results_label_desktop = '';
        $expected_model->number_of_results_label_mobile = '';
        $expected_model->filters_title_label_mobile = '';
        $expected_model->clear_filters_label = '';
        $expected_model->sort_by_label = '';
        $expected_model->apply_filters_label_mobile = '';
        $expected_model->try_searching_again_label = '';
        $expected_model->search_suggestions_label = 'Popular searches';
        $expected_model->search_recommendations_label = 'Recommended products';
        $expected_model->use_text = TRUE;
        $expected_model->search_delay_millis = 300;
        $expected_model->full_text_search_results_container = 'body';
        $expected_model->compute_results_top_position_from = 'header';
        $expected_model->use_instant_full_search = TRUE;
        $expected_model->use_floating_search_bar_button = TRUE;
        $expected_model->search_bar_selector = "input[name=q],input[name=s]";
        $expected_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        $expected_model->use_search_suggestions = TRUE;
        $empty_model = new ImpreseeSnippetConfigurationModel;
        $empty_model->loadFromOldStorageArray($data_array);
        $this->assertEquals(
            $empty_model,
            $expected_model
        );
    }

    public function testLoadFromOldFormatArrayIncompleteData(){
        $data_array = array();
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeSnippetConfigurationModel;
        $empty_model->loadFromOldStorageArray($data_array);
    }

}