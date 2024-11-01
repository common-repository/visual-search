<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetGeneralConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetLabelsConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetSearchByTextConfiguration;

class ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfigurationTest extends TestCase {
    private $mapper;

    protected function setUp(): void{
        $this->mapper = new ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfiguration;
    }
    
    /**
    * @group mapFromLocation
    */
    public function testMapFromFloatingButtonLocationBottomLeft(){
        $location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        $expected_parsed_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapFromFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapFromLocation
    */
    public function testMapFromFloatingButtonLocationBottomRight(){
        $location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $expected_parsed_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapFromFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapFromLocation
    */
    public function testMapFromFloatingButtonLocationTopLeft(){
        $location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_LEFT;
        $expected_parsed_location = ImpreseeSnippetSearchByTextConfiguration::TOP_LEFT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapFromFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapFromLocation
    */
    public function testMapFromFloatingButtonLocationTopRight(){
        $location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_RIGHT;
        $expected_parsed_location = ImpreseeSnippetSearchByTextConfiguration::TOP_RIGHT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapFromFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapFromLocation
    */
    public function testMapFromFloatingButtonLocationMiddleLeft(){
        $location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_LEFT;
        $expected_parsed_location = ImpreseeSnippetSearchByTextConfiguration::MIDDLE_LEFT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapFromFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapFromLocation
    */
    public function testMapFromFloatingButtonLocationMiddleRight(){
        $location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_RIGHT;
        $expected_parsed_location = ImpreseeSnippetSearchByTextConfiguration::MIDDLE_RIGHT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapFromFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapToLocation
    */
    public function testMapToFloatingButtonLocationBottomLeft(){
        $location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
        $expected_parsed_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapToFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapToLocation
    */
    public function testMapToFloatingButtonLocationBottomRight(){
        $location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
        $expected_parsed_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapToFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapToLocation
    */
    public function testMapToFloatingButtonLocationTopLeft(){
        $location = ImpreseeSnippetSearchByTextConfiguration::TOP_LEFT;
        $expected_parsed_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_LEFT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapToFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapToLocation
    */
    public function testMapToFloatingButtonLocationTopRight(){
        $location = ImpreseeSnippetSearchByTextConfiguration::TOP_RIGHT;
        $expected_parsed_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_TOP_RIGHT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapToFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapToLocation
    */
    public function testMapToFloatingButtonLocationMiddleLeft(){
        $location = ImpreseeSnippetSearchByTextConfiguration::MIDDLE_LEFT;
        $expected_parsed_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_LEFT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapToFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapToLocation
    */
    public function testMapToFloatingButtonLocationMiddleRight(){
        $location = ImpreseeSnippetSearchByTextConfiguration::MIDDLE_RIGHT;
        $expected_parsed_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_MIDDLE_RIGHT;
        $this->assertEquals(
            $expected_parsed_location,
            $this->mapper->mapToFloatingButtonLocation($location)
        );
    }

    /**
    * @group mapFrom
    */
    public function testMapFrom(){
        $model = new ImpreseeSnippetConfigurationModel;
        $model->use_photo_search = TRUE;
        $model->use_sketch_search = TRUE;
        $model->search_by_photo_icon_url = 'value 25';
        $model->search_by_sketch_icon_url = 'value 26';
        $model->load_after_page_render = FALSE;
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
        $model->use_search_suggestions = FALSE;
        $model->mobile_instant_as_grid = TRUE;
        $model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $expected_general_config = new ImpreseeSnippetGeneralConfiguration;
        $expected_general_config->load_after_page_render = FALSE;
        $expected_general_config->container_selector = '.value';
        $expected_general_config->main_color = '#9CD333';
        $expected_general_config->add_search_data_to_url = TRUE;
        $expected_general_config->images_only_loaded_from_camera = FALSE;
        $expected_general_config->disable_image_crop = FALSE;
        $expected_general_config->price_fraction_digit_number = 2;
        $expected_general_config->currency_symbol_at_the_end = FALSE;
        $expected_general_config->on_sale_label_color = '#FF0000';
        $expected_general_config->search_by_photo_icon_url = 'value 25';
        $expected_general_config->search_by_sketch_icon_url = 'value 26';
        $expected_general_config->use_photo_search = TRUE;
        $expected_general_config->use_sketch_search = TRUE;
        $expected_label_config = new ImpreseeSnippetLabelsConfiguration;
        $expected_label_config->search_results_title = 'value 1';
        $expected_label_config->search_button_label = 'value 2';
        $expected_label_config->oops_exclamation = 'value 3';
        $expected_label_config->error_title = 'value 4';
        $expected_label_config->error_message = 'value 5';
        $expected_label_config->drag_and_drop_image_title = 'value 6';
        $expected_label_config->drag_and_drop_image_body = 'value 7';
        $expected_label_config->custom_crop_label = 'value 8';
        $expected_label_config->start_writing_label = 'value 9';
        $expected_label_config->currency_symbol = '$';
        $expected_label_config->search_by_photo_label = 'value 10';
        $expected_label_config->search_by_sketch_label = 'value 11';
        $expected_label_config->see_all_results_label = 'value 12';
        $expected_label_config->no_matching_results = 'value 13';
        $expected_label_config->on_sale_label = 'value 14';
        $expected_label_config->result_title_search_by_text = 'value 15';
        $expected_label_config->number_of_results_label_desktop = 'value 16';
        $expected_label_config->number_of_results_label_mobile = 'value 17';
        $expected_label_config->filters_title_label_mobile = 'value 18';
        $expected_label_config->clear_filters_label = 'value 19';
        $expected_label_config->sort_by_label = 'value 20';
        $expected_label_config->apply_filters_label_mobile = 'value 21';
        $expected_label_config->try_searching_again_label = 'value 22';
        $expected_label_config->search_suggestions_label = 'value 23';
        $expected_label_config->search_recommendations_label = 'value 24';
        $expected_text_config = new ImpreseeSnippetSearchByTextConfiguration;
        $expected_text_config->use_text = TRUE;
        $expected_text_config->search_delay_millis = 300;
        $expected_text_config->full_text_search_results_container = '.container';
        $expected_text_config->compute_results_top_position_from = 'header';
        $expected_text_config->use_instant_full_search = TRUE;
        $expected_text_config->use_floating_search_bar_button = TRUE;
        $expected_text_config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
        $expected_text_config->use_search_suggestions = FALSE;
        $expected_text_config->mobile_instant_as_grid = TRUE;
        $expected_config = new ImpreseeSnippetConfiguration;
        $expected_config->general_configuration = $expected_general_config;
        $expected_config->labels_configuration = $expected_label_config;
        $expected_config->search_by_text_configuration = $expected_text_config;
        $this->assertEquals(
            $expected_config,
            $this->mapper->mapFrom($model)
        );
    }

    /**
    * @group mapTo
    */
    public function testMapTo(){
        $general_config = new ImpreseeSnippetGeneralConfiguration;
        $general_config->load_after_page_render = FALSE;
        $general_config->container_selector = '.value';
        $general_config->main_color = '#9CD333';
        $general_config->add_search_data_to_url = TRUE;
        $general_config->images_only_loaded_from_camera = FALSE;
        $general_config->disable_image_crop = FALSE;
        $general_config->price_fraction_digit_number = 2;
        $general_config->currency_symbol_at_the_end = FALSE;
        $general_config->on_sale_label_color = '#FF0000';
        $general_config->search_by_photo_icon_url = 'value 25';
        $general_config->search_by_sketch_icon_url = 'value 26';
        $general_config->use_photo_search = TRUE;
        $general_config->use_sketch_search = FALSE;
        $label_config = new ImpreseeSnippetLabelsConfiguration;
        $label_config->search_results_title = 'value 1';
        $label_config->search_button_label = 'value 2';
        $label_config->oops_exclamation = 'value 3';
        $label_config->error_title = 'value 4';
        $label_config->error_message = 'value 5';
        $label_config->drag_and_drop_image_title = 'value 6';
        $label_config->drag_and_drop_image_body = 'value 7';
        $label_config->custom_crop_label = 'value 8';
        $label_config->start_writing_label = 'value 9';
        $label_config->currency_symbol = '$';
        $label_config->search_by_photo_label = 'value 10';
        $label_config->search_by_sketch_label = 'value 11';
        $label_config->see_all_results_label = 'value 12';
        $label_config->no_matching_results = 'value 13';
        $label_config->on_sale_label = 'value 14';
        $label_config->result_title_search_by_text = 'value 15';
        $label_config->number_of_results_label_desktop = 'value 16';
        $label_config->number_of_results_label_mobile = 'value 17';
        $label_config->filters_title_label_mobile = 'value 18';
        $label_config->clear_filters_label = 'value 19';
        $label_config->sort_by_label = 'value 20';
        $label_config->apply_filters_label_mobile = 'value 21';
        $label_config->try_searching_again_label = 'value 22';
        $label_config->search_suggestions_label = 'value 23';
        $label_config->search_recommendations_label = 'value 24';
        $text_config = new ImpreseeSnippetSearchByTextConfiguration;
        $text_config->use_text = TRUE;
        $text_config->search_delay_millis = 300;
        $text_config->full_text_search_results_container = '.container';
        $text_config->compute_results_top_position_from = 'header';
        $text_config->use_instant_full_search = TRUE;
        $text_config->use_floating_search_bar_button = TRUE;
        $text_config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
        $text_config->use_search_suggestions = FALSE;
        $text_config->mobile_instant_as_grid = TRUE;
        $config = new ImpreseeSnippetConfiguration;
        $config->general_configuration = $general_config;
        $config->labels_configuration = $label_config;
        $config->search_by_text_configuration = $text_config;
        $expected_model = new ImpreseeSnippetConfigurationModel;
        $expected_model->use_photo_search = TRUE;
        $expected_model->use_sketch_search = FALSE;
        $expected_model->search_by_photo_icon_url = 'value 25';
        $expected_model->search_by_sketch_icon_url = 'value 26';
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
        $expected_model->use_search_suggestions = FALSE;
        $expected_model->mobile_instant_as_grid = TRUE;
        $expected_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $this->assertEquals(
            $this->mapper->mapTo($config),
            $expected_model
        );
    }
}