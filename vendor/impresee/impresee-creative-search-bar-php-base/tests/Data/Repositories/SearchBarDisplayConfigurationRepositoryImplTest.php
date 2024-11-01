<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use PhpFp\Either\Constructor\Left;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration; 
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarInFormConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetGeneralConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetLabelsConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetSearchByTextConfiguration;
    use Impresee\CreativeSearchBar\Data\Repositories\SearchBarDisplayConfigurationRepositoryImpl;
    use Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException, CouldNotStoreDataException, FailureUpdateCustomCodeData,  FailedAtRemovingDataFailure,FailureUpdateImpreseeSnippetData,
        CouldNotRemoveDataException, UnknownFailure
    };

class SearchBarDisplayConfigurationRepositoryImplTest extends TestCase {
    private $repository;
    private $datasource;
    private $email_datasource;
    private $custom_code_model;
    private $custom_code_configuration;
    private $snippet_model;
    private $snippet_config;
    private $store;

    protected function setUp(): void{
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getProjectName')
            ->willReturn('Impresee');
        $project_stub->method('getIsDebug')
            ->willReturn(FALSE);
        $this->datasource = $this->createMock(SearchBarDisplayLocalDataSource::class);
        $this->email_datasource = $this->createMock(EmailDataSource::class);
        $this->repository = new SearchBarDisplayConfigurationRepositoryImpl(
            $this->datasource,
            $this->email_datasource,
            $project_stub
        );
        $this->createDataCustomCode();
        $this->createSnippetData();
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
    }

    public function createDataCustomCode(){
        $this->custom_code_model = new CustomCodeModel;
        $this->custom_code_model->js_add_buttons = 'let variable = 1;';
        $this->custom_code_model->css_style_buttons = '.button{}';
        $this->custom_code_model->js_after_load_results_code = "console.log('running');";
        $this->custom_code_model->js_before_load_results_code = "console.log('running');";
        $this->custom_code_model->js_search_failed_code = "console.log('running');";
        $this->custom_code_model->js_press_see_all_code = "console.log('running');";
        $this->custom_code_model->js_close_text_results_code = "console.log('running');";
        $this->custom_code_model->js_on_open_text_dropdown_code = "console.log('running');";
        $this->custom_code_configuration = new CustomCodeConfiguration;
        $this->custom_code_configuration->js_add_buttons = 'let variable = 1;';
        $this->custom_code_configuration->css_style_buttons = '.button{}';
        $this->custom_code_configuration->js_after_load_results_code = "console.log('running');";
        $this->custom_code_configuration->js_before_load_results_code = "console.log('running');";
        $this->custom_code_configuration->js_search_failed_code = "console.log('running');";
        $this->custom_code_configuration->js_press_see_all_code = "console.log('running');";
        $this->custom_code_configuration->js_close_text_results_code = "console.log('running');";
        $this->custom_code_configuration->js_on_open_text_dropdown_code = "console.log('running');";
    }

    public function createSnippetData(){
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
        $general_config->decimal_separator = ",";
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
        $text_config->search_bar_selector = 'input[name=q]';
        $text_config->use_search_suggestions = FALSE;
        $text_config->mobile_instant_as_grid = TRUE;
        $text_config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
        $this->snippet_config = new ImpreseeSnippetConfiguration;
        $this->snippet_config->general_configuration = $general_config;
        $this->snippet_config->labels_configuration = $label_config;
        $this->snippet_config->search_by_text_configuration = $text_config;
        $this->snippet_model = new ImpreseeSnippetConfigurationModel;
        $this->snippet_model->load_after_page_render = FALSE;
        $this->snippet_model->container_selector = '.value';
        $this->snippet_model->main_color = '#9CD333';
        $this->snippet_model->add_search_data_to_url = TRUE;
        $this->snippet_model->images_only_loaded_from_camera = FALSE;
        $this->snippet_model->disable_image_crop = FALSE;
        $this->snippet_model->price_fraction_digit_number = 2;
        $this->snippet_model->decimal_separator = ",";
        $this->snippet_model->currency_symbol_at_the_end = FALSE;
        $this->snippet_model->on_sale_label_color = '#FF0000';
        $this->snippet_model->search_results_title = 'value 1';
        $this->snippet_model->search_button_label = 'value 2';
        $this->snippet_model->oops_exclamation = 'value 3';
        $this->snippet_model->error_title = 'value 4';
        $this->snippet_model->error_message = 'value 5';
        $this->snippet_model->drag_and_drop_image_title = 'value 6';
        $this->snippet_model->drag_and_drop_image_body = 'value 7';
        $this->snippet_model->custom_crop_label = 'value 8';
        $this->snippet_model->start_writing_label = 'value 9';
        $this->snippet_model->currency_symbol = '$';
        $this->snippet_model->search_by_photo_label = 'value 10';
        $this->snippet_model->search_by_sketch_label = 'value 11';
        $this->snippet_model->see_all_results_label = 'value 12';
        $this->snippet_model->no_matching_results = 'value 13';
        $this->snippet_model->on_sale_label = 'value 14';
        $this->snippet_model->result_title_search_by_text = 'value 15';
        $this->snippet_model->number_of_results_label_desktop = 'value 16';
        $this->snippet_model->number_of_results_label_mobile = 'value 17';
        $this->snippet_model->filters_title_label_mobile = 'value 18';
        $this->snippet_model->clear_filters_label = 'value 19';
        $this->snippet_model->sort_by_label = 'value 20';
        $this->snippet_model->apply_filters_label_mobile = 'value 21';
        $this->snippet_model->try_searching_again_label = 'value 22';
        $this->snippet_model->search_suggestions_label = 'value 23';
        $this->snippet_model->search_recommendations_label = 'value 24';
        $this->snippet_model->use_text = TRUE;
        $this->snippet_model->search_delay_millis = 300;
        $this->snippet_model->full_text_search_results_container = '.container';
        $this->snippet_model->compute_results_top_position_from = 'header';
        $this->snippet_model->use_instant_full_search = TRUE;
        $this->snippet_model->use_floating_search_bar_button = TRUE;
        $this->snippet_model->search_bar_selector = 'input[name=q]';
        $this->snippet_model->use_search_suggestions = FALSE;
        $this->snippet_model->mobile_instant_as_grid = TRUE;
        $this->snippet_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
    }

    /**
    * @group CustomCode
    */
    public function testGetCustomCodeDataCorrectlyFromDatasource(){
        $this->datasource->expects($this->once())
            ->method('getLocalCustomCodeConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->custom_code_model));
        $return_promise = $this->repository->getSearchBarCustomCodeConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($this->custom_code_configuration)
        );
    }

    /**
    * @group CustomCode
    */
    public function testGetCustomCodeDataGenericError(){
        $this->datasource->expects($this->once())
            ->method('getLocalCustomCodeConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->getSearchBarCustomCodeConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left(new UnknownFailure)
        );
    }

    /**
    * @group CustomCode
    */
    public function testReturnEmptyCustomCodeModelWhenThereIsNoData(){
        $empty_config = new CustomCodeConfiguration;
        $empty_config->js_add_buttons = '';
        $empty_config->css_style_buttons = '';
        $empty_config->js_after_load_results_code = '';
        $empty_config->js_before_load_results_code = '';
        $empty_config->js_search_failed_code = '';
        $empty_config->js_press_see_all_code = '';
        $empty_config->js_close_text_results_code = '';
        $empty_config->js_on_open_text_dropdown_code = '';
        $this->datasource->expects($this->once())
            ->method('getLocalCustomCodeConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $return_promise = $this->repository->getSearchBarCustomCodeConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($empty_config)
        );
    }

    /**
    * @group CustomCode
    */
    public function testUpdateDataSuccessfully(){
        $this->datasource->expects($this->once())
            ->method('updateLocalCustomCodeConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->custom_code_model))
            ->will($this->returnValue($this->custom_code_model));
        $return_promise = $this->repository->updateCustomCodeConfiguration(
            $this->store,
            $this->custom_code_configuration
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($this->custom_code_configuration)
        );
    }

    /**
    * @group CustomCode
    */
    public function testUpdateDataFails(){
        $expected_failure = new FailureUpdateCustomCodeData;
        $this->datasource->expects($this->once())
            ->method('updateLocalCustomCodeConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->custom_code_model))
            ->will($this->throwException(new CouldNotStoreDataException));
        $return_promise = $this->repository->updateCustomCodeConfiguration(
            $this->store,
            $this->custom_code_configuration
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

     /**
    * @group CustomCode
    */
    public function testUpdateDataFailsGenericError(){
        $expected_failure = new UnknownFailure;
        $this->datasource->expects($this->once())
            ->method('updateLocalCustomCodeConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->custom_code_model))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->updateCustomCodeConfiguration(
            $this->store,
            $this->custom_code_configuration
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

    /**
    * @group CustomCode
    */
    public function testRemoveDataSuccessfully(){
        $this->datasource->expects($this->once())
            ->method('removeCustomCodeLocalData')
            ->with($this->equalTo($this->store));
        $return_promise = $this->repository->removeCustomCodeConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of(NULL)
        );
    }


    /**
    * @group CustomCode
    */
    public function testRemoveDataFails(){
        $expected_failure = new FailedAtRemovingDataFailure;
        $this->datasource->expects($this->once())
            ->method('removeCustomCodeLocalData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new CouldNotRemoveDataException));
        $return_promise = $this->repository->removeCustomCodeConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

     /**
    * @group CustomCode
    */
    public function testRemoveDataFailsWithGenericError(){
        $expected_failure = new UnknownFailure;
        $this->datasource->expects($this->once())
            ->method('removeCustomCodeLocalData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->removeCustomCodeConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }



    /**
    * @group ImpreseeSnippet
    */
    public function testGetSnippetConfigurationDataCorrectlyFromDataSource(){
        $this->datasource->expects($this->once())
            ->method('getLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->snippet_model));
        $return_promise = $this->repository->getLocalImpreseeSnippetConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($this->snippet_config)
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testGetSnippetConfigurationDataFailsWithGenericError(){
        $this->datasource->expects($this->once())
            ->method('getLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->getLocalImpreseeSnippetConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left(new UnknownFailure)
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testReturnDefaultSnippetConfigurationModelWhenThereIsNoData(){
        $general_config = new ImpreseeSnippetGeneralConfiguration;
        $general_config->load_after_page_render = FALSE;
        $general_config->container_selector = '';
        $general_config->decimal_separator = ',';
        $general_config->main_color = '#9CD333';
        $general_config->add_search_data_to_url = FALSE;
        $general_config->images_only_loaded_from_camera = FALSE;
        $general_config->disable_image_crop = FALSE;
        $general_config->price_fraction_digit_number = 2;
        $general_config->currency_symbol_at_the_end = FALSE;
        $general_config->on_sale_label_color = '#FF0000';
        $general_config->search_by_photo_icon_url = '';
        $general_config->search_by_sketch_icon_url = '';
        $general_config->use_photo_search = TRUE;
        $general_config->use_sketch_search = TRUE;
        $label_config = new ImpreseeSnippetLabelsConfiguration;
        $label_config->search_results_title = 'Search results';
        $label_config->search_button_label = 'Search';
        $label_config->oops_exclamation = 'Oops...';
        $label_config->error_title = 'We didn\'t expect this at all.';
        $label_config->error_message = 'It seems our system is overheating, please try again later.';
        $label_config->drag_and_drop_image_title = 'Drag & Drop an image or just click here';
        $label_config->drag_and_drop_image_body = "Upload the image you'd like to use to search";
        $label_config->custom_crop_label = 'Custom search';
        $label_config->start_writing_label = 'Start typing to search';
        $label_config->currency_symbol = '$';
        $label_config->search_by_photo_label = 'Search by photo';
        $label_config->search_by_sketch_label = 'Search by drawing';
        $label_config->see_all_results_label = 'See all results';
        $label_config->no_matching_results = 'We couldn\'t find any results for:';
        $label_config->on_sale_label = 'On sale';
        $label_config->result_title_search_by_text = 'Search results for';
        $label_config->number_of_results_label_desktop = 'Displaying {1} results';
        $label_config->number_of_results_label_mobile = 'Displaying {1} results for "{2}"';
        $label_config->filters_title_label_mobile = 'Filters';
        $label_config->clear_filters_label = 'Clear filters';
        $label_config->sort_by_label = 'Sort by';
        $label_config->apply_filters_label_mobile = 'Apply';
        $label_config->try_searching_again_label = "Why don't you try drawing or taking a picture of what you want?";
        $label_config->search_suggestions_label = 'Popular searches';
        $label_config->search_recommendations_label = 'Recommended products';
        $text_config = new ImpreseeSnippetSearchByTextConfiguration;
        $text_config->use_text = TRUE;
        $text_config->search_delay_millis = 300;
        $text_config->full_text_search_results_container = 'body';
        $text_config->compute_results_top_position_from = 'header';
        $text_config->use_instant_full_search = TRUE;
        $text_config->use_floating_search_bar_button = TRUE;
        $text_config->search_bar_selector = "input[name=q],input[name=s]";
        $text_config->use_search_suggestions = TRUE;
        $text_config->mobile_instant_as_grid = FALSE;
        $text_config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
        $snippet_config = new ImpreseeSnippetConfiguration;
        $snippet_config->general_configuration = $general_config;
        $snippet_config->labels_configuration = $label_config;
        $snippet_config->search_by_text_configuration = $text_config;
        $this->datasource->expects($this->once())
            ->method('getLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $return_promise = $this->repository->getLocalImpreseeSnippetConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($snippet_config)
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testUpdateImpreseeSnippetDataSuccessfully(){
        $this->datasource->expects($this->once())
            ->method('updateLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->snippet_model))
            ->will($this->returnValue($this->snippet_model));
        $return_promise = $this->repository->updateImpreseeSnippetConfiguration(
            $this->store,
            $this->snippet_config
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            Either::of($this->snippet_config),
            $return_value
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testUpdateImpreseeSnippetDisplayDataFails(){
        $expected_failure = new FailureUpdateImpreseeSnippetData;
        $this->datasource->expects($this->once())
            ->method('updateLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->snippet_model))
            ->will($this->throwException(new CouldNotStoreDataException));
        $return_promise = $this->repository->updateImpreseeSnippetConfiguration(
            $this->store,
            $this->snippet_config
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $return_value
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testUpdateImpreseeSnippetDisplayDataFailsWithGenericError(){
        $expected_failure = new UnknownFailure;
        $this->datasource->expects($this->once())
            ->method('updateLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->snippet_model))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->updateImpreseeSnippetConfiguration(
            $this->store,
            $this->snippet_config
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $return_value
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testRemoveImpreseeSnippetDataSuccessfully(){
        $this->datasource->expects($this->once())
            ->method('removeSnippetLocalData')
            ->with($this->equalTo($this->store));
        $return_promise = $this->repository->removeSnippetConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of(NULL)
        );
    }


    /**
    * @group ImpreseeSnippet
    */
    public function testRemoveImpreseeSnippetDataFails(){
        $expected_failure = new FailedAtRemovingDataFailure;
        $this->datasource->expects($this->once())
            ->method('removeSnippetLocalData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new CouldNotRemoveDataException));
        $return_promise = $this->repository->removeSnippetConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

        /**
    * @group ImpreseeSnippet
    */
    public function testRemoveImpreseeSnippetDataFailsWithGenericError(){
        $expected_failure = new UnknownFailure;
        $this->datasource->expects($this->once())
            ->method('removeSnippetLocalData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->removeSnippetConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

}