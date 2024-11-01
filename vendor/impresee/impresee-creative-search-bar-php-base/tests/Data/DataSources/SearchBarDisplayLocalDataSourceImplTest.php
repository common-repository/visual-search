<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException, CouldNotStoreDataException, CouldNotRemoveDataException};
    use Impresee\CreativeSearchBar\Data\Models\SearchBarDisplayConfigurationModel;
    use Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSourceImpl;

class SearchBarDisplayLocalDataSourceImplTest extends TestCase {
    private $key_value_storage;
    private $datasource;
    private $store;

    protected function setUp(): void{
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
        $this->key_value_storage = $this->createMock(KeyValueStorage::class);
        $storage_codes_stub = $this->createMock(StorageCodes::class);
        $storage_codes_stub->method('getLocalSnippetSettingsKeyPrefix')
            ->willReturn('see_wccs_snippet_');
        $storage_codes_stub->method('getOldSnippetConfigKey')
            ->willReturn('see_wccs_settings_display');
        $storage_codes_stub->method('getLocalCustomCodeSettingsKeyPrefix')
            ->willReturn('see_wccs_cc_');
        $storage_codes_stub->method('getOldLocalAdvancedSettingsKey')
            ->willReturn('see_wccs_settings_advanced');
        $this->datasource = new SearchBarDisplayLocalDataSourceImpl(
            $this->key_value_storage,
            $storage_codes_stub
        );
    }

    /**
    * @group CustomCode
    */
    public function testGetDataSucessfullyFromKeyValueStorage(){
        $expected_model = new CustomCodeModel;
        $expected_model->js_add_buttons = 'let variable = 1;';
        $expected_model->css_style_buttons = '.button{}';
        $expected_model->js_after_load_results_code = "console.log('running');";
        $expected_model->js_before_load_results_code = "console.log('running');";
        $expected_model->js_search_failed_code = "console.log('running');";
        $expected_model->js_press_see_all_code = "console.log('running');";
        $expected_model->js_close_text_results_code = "console.log('running');";
        $expected_model->js_on_open_text_dropdown_code = "console.log('running');";
        $stored_array = array(
           'js_add_buttons' =>  'let variable = 1;',
           'css_style_buttons' => '.button{}',
           'js_after_load_results_code' => "console.log('running');",
           'js_before_load_results_code' => "console.log('running');",
           'js_search_failed_code' => "console.log('running');",
           'js_press_see_all_code' => "console.log('running');",
           'js_close_text_results_code' => "console.log('running');",
           'js_on_open_text_dropdown_code' => "console.log('running');"
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue($stored_array));
        $returned_model = $this->datasource->getLocalCustomCodeConfiguration($this->store);
        $this->assertEquals(
            $returned_model,
            $expected_model
        );
    }

    /**
    * @group CustomCode
    */
    public function testGetDataFromOldKey(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $old_storage_key = 'see_wccs_settings_advanced';
        $stored_array = array(
           'js_add_buttons' =>  'let variable = 1;',
           'css_style_buttons' => '.button{}',
           'js_after_load_results_code' => "console.log('running');",
           'js_before_load_results_code' => "console.log('running');",
           'js_search_failed_code' => "console.log('running');",
           'js_press_see_all_code' => "console.log('running');",
           'js_close_text_results_code' => "console.log('running');",
           'js_on_open_text_dropdown_code' => "console.log('running');"
        );
        $expected_model = new CustomCodeModel;
        $expected_model->js_add_buttons = 'let variable = 1;';
        $expected_model->css_style_buttons = '.button{}';
        $expected_model->js_after_load_results_code = "console.log('running');";
        $expected_model->js_before_load_results_code = "console.log('running');";
        $expected_model->js_search_failed_code = "console.log('running');";
        $expected_model->js_press_see_all_code = "console.log('running');";
        $expected_model->js_close_text_results_code = "console.log('running');";
        $expected_model->js_on_open_text_dropdown_code = "console.log('running');";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue($stored_array));
        $returned_model = $this->datasource->getLocalCustomCodeConfiguration($this->store);
        $this->assertEquals(
            $returned_model,
            $expected_model
        );
    }

    /**
    * @group CustomCode
    */
    public function testGetDataFromOldArray(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $old_storage_key = 'see_wccs_settings_advanced';
        $stored_array = array(
           'js_buttons' =>  'let variable = 1;',
           'css_buttons' => '.button{}',
           'js_after_search' => "console.log('running');"
        );
        $expected_model = new CustomCodeModel;
        $expected_model->js_add_buttons = 'let variable = 1;';
        $expected_model->css_style_buttons = '.button{}';
        $expected_model->js_after_load_results_code = "console.log('running');";
        $expected_model->js_before_load_results_code = "";
        $expected_model->js_search_failed_code = "";
        $expected_model->js_press_see_all_code = "";
        $expected_model->js_close_text_results_code = "";
        $expected_model->js_on_open_text_dropdown_code = "";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with($this->equalTo($old_storage_key))
            ->will($this->returnValue($stored_array));
        $returned_model = $this->datasource->getLocalCustomCodeConfiguration($this->store);
        $this->assertEquals(
            $returned_model,
            $expected_model
        );
    }

    /**
    * @group CustomCode
    */
    public function testThrowExceptionWhenNoArrayIsPresent(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $old_storage_key = 'see_wccs_settings_advanced';
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with($this->equalTo($old_storage_key))
            ->will($this->returnValue(FALSE));
        
        $this->expectException(NoDataException::class);
        $this->datasource->getLocalCustomCodeConfiguration($this->store);
    }

    /**
    * @group CustomCode
    */
    public function testUpdateCustomCodeSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $old_storage_key = 'see_wccs_settings_advanced';
        $model = new CustomCodeModel;
        $model->js_add_buttons = 'let variable = 1;';
        $model->css_style_buttons = '.button{}';
        $model->js_after_load_results_code = "console.log('running');";
        $model->js_before_load_results_code = "console.log('running');";
        $model->js_search_failed_code = "console.log('running');";
        $model->js_press_see_all_code = "console.log('running');";
        $model->js_close_text_results_code = "console.log('running');";
        $model->js_on_open_text_dropdown_code = "console.log('running');";
        $model_array = array(
           'js_add_buttons' =>  'let variable = 1;',
           'css_style_buttons' => '.button{}',
           'js_after_load_results_code' => "console.log('running');",
           'js_before_load_results_code' => "console.log('running');",
           'js_search_failed_code' => "console.log('running');",
           'js_press_see_all_code' => "console.log('running');",
           'js_close_text_results_code' => "console.log('running');",
           'js_on_open_text_dropdown_code' => "console.log('running');"
        );

        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo($old_storage_key)
            )->will($this->returnValue(FALSE));
        
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($model_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateLocalCustomCodeConfiguration($this->store, $model);
        $this->assertEquals(
            $result,
            $model
        );
    }

    /**
    * @group CustomCode
    */
    public function testDontUpdateCustomCodeConfigBecauseStoredDataIsTheSame(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $old_storage_key = 'see_wccs_settings_advanced';
        $model = new CustomCodeModel;
        $model->js_add_buttons = 'let variable = 1;';
        $model->css_style_buttons = '.button{}';
        $model->js_after_load_results_code = "console.log('running');";
        $model->js_before_load_results_code = "console.log('running');";
        $model->js_search_failed_code = "console.log('running');";
        $model->js_press_see_all_code = "console.log('running');";
        $model->js_close_text_results_code = "console.log('running');";
        $model->js_on_open_text_dropdown_code = "console.log('running');";
        $stored_array = array(
           'js_add_buttons' =>  'let variable = 1;',
           'css_style_buttons' => '.button{}',
           'js_after_load_results_code' => "console.log('running');",
           'js_before_load_results_code' => "console.log('running');",
           'js_search_failed_code' => "console.log('running');",
           'js_press_see_all_code' => "console.log('running');",
           'js_close_text_results_code' => "console.log('running');",
           'js_on_open_text_dropdown_code' => "console.log('running');"
        );
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->never())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($stored_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateLocalCustomCodeConfiguration($this->store, $model);
        $this->assertEquals(
            $result,
            $model
        );
    }

    /**
    * @group CustomCode
    */
    public function testUpdateCustomCodeConfigFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $old_storage_key = 'see_wccs_settings_advanced';
        $model = new CustomCodeModel;
        $model->js_add_buttons = 'let variable = 1;';
        $model->css_style_buttons = '.button{}';
        $model->js_after_load_results_code = "console.log('running');";
        $model->js_before_load_results_code = "console.log('running');";
        $model->js_search_failed_code = "console.log('running');";
        $model->js_press_see_all_code = "console.log('running');";
        $model->js_close_text_results_code = "console.log('running');";
        $model->js_on_open_text_dropdown_code = "console.log('running');";
        $model_array = array(
           'js_add_buttons' =>  'let variable = 1;',
           'css_style_buttons' => '.button{}',
           'js_after_load_results_code' => "console.log('running');",
           'js_before_load_results_code' => "console.log('running');",
           'js_search_failed_code' => "console.log('running');",
           'js_press_see_all_code' => "console.log('running');",
           'js_close_text_results_code' => "console.log('running');",
           'js_on_open_text_dropdown_code' => "console.log('running');"
        );
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo($old_storage_key)
            )->will($this->returnValue(FALSE));
        
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($model_array)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->updateLocalCustomCodeConfiguration($this->store, $model);
    }

    /**
    * @group CustomCode
    */
    public function testRemoveCustomCodeSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(TRUE));
        $this->datasource->removeCustomCodeLocalData($this->store);
    }

    /**
    * @group CustomCode
    */
    public function testRemoveCustomCodeFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_cc_";
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotRemoveDataException::class);
        $this->datasource->removeCustomCodeLocalData($this->store);
    }


    /**
    * @group ImpreseeSnippet
    */
    public function testGetImpreseeSnippetDataSucessfullyFromKeyValueStorage(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $old_storage_key = 'see_wccs_settings_display';
        $stored_array = array(
            'use_photo_search' => TRUE,
            'use_sketch_search' => FALSE,
            'search_by_photo_icon_url' => 'value 25',
            'search_by_sketch_icon_url' => 'value 26',
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
            'search_bar_selector' => 'input',
            'use_search_suggestions' => FALSE,
        );
        $expected_model = new ImpreseeSnippetConfigurationModel;
        $expected_model->use_photo_search = TRUE;
        $expected_model->use_sketch_search = FALSE;
        $expected_model->search_by_photo_icon_url = "value 25";
        $expected_model->search_by_sketch_icon_url = "value 26";
        $expected_model->load_after_page_render = FALSE;
        $expected_model->decimal_separator = ",";
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
        $expected_model->search_bar_selector = 'input';
        $expected_model->use_search_suggestions = FALSE;
        $expected_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue($stored_array));
        $returned_model = $this->datasource->getLocalImpreseeSnippetConfiguration($this->store);
        $this->assertEquals(
            $expected_model,
            $returned_model
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testGetImpreseeSnippetDataSucessfullyFromKeyValueStorageFallbackOldKey(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $old_storage_key = 'see_wccs_settings_display';
        $stored_array = array(
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
            'search_bar_selector' => 'input',
            'use_photo_search' => TRUE,
            'use_sketch_search' => FALSE,
        );
        $expected_model = new ImpreseeSnippetConfigurationModel;
        $expected_model->use_photo_search = TRUE;
        $expected_model->use_sketch_search = FALSE;
        $expected_model->search_by_photo_icon_url = "";
        $expected_model->search_by_sketch_icon_url = "";
        $expected_model->load_after_page_render = FALSE;
        $expected_model->decimal_separator = ",";
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
        $expected_model->search_bar_selector = 'input';
        $expected_model->use_search_suggestions = TRUE;
        $expected_model->mobile_instant_as_grid = FALSE;
        $expected_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue($stored_array));
        $returned_model = $this->datasource->getLocalImpreseeSnippetConfiguration($this->store);
        $this->assertEquals(
            $expected_model,
            $returned_model
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testGetImpreseeSnippetDataFromOldArray(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $old_storage_key = 'see_wccs_settings_display';
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
        $expected_model->search_by_photo_icon_url = "";
        $expected_model->search_by_sketch_icon_url = "";
        $expected_model->use_photo_search = TRUE;
        $expected_model->use_sketch_search = TRUE;
        $expected_model->decimal_separator = ",";
        $expected_model->load_after_page_render = FALSE;
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
        $expected_model->search_bar_selector = 'input[name=q],input[name=s]';
        $expected_model->use_search_suggestions = TRUE;
        $expected_model->mobile_instant_as_grid = FALSE;
        $expected_model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_LEFT;
        $empty_model = new ImpreseeSnippetConfigurationModel;
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with($this->equalTo($old_storage_key))
            ->will($this->returnValue($data_array));
        $returned_model = $this->datasource->getLocalImpreseeSnippetConfiguration($this->store);
        $this->assertEquals(
            $expected_model,
            $returned_model
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testThrowExceptionWhenNoImpreseeSnippetArrayIsPresent(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $old_storage_key = 'see_wccs_settings_display';
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with($this->equalTo($old_storage_key))
            ->will($this->returnValue(FALSE));
        $this->expectException(NoDataException::class);
        $this->datasource->getLocalImpreseeSnippetConfiguration($this->store);
    }


    /**
    * @group ImpreseeSnippet
    */
    public function testUpdateImpreseeSnippetDataSuccessfully(){
        $store_name = $this->store->getStoreName();
        $model_array = array(
            'search_by_photo_icon_url' => 'value 25',
            'search_by_sketch_icon_url' => 'value 26',
            'use_photo_search' => FALSE,
            'use_sketch_search' => FALSE,
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
            'search_bar_selector' => 'input',
            'use_search_suggestions' => TRUE,
            'mobile_instant_as_grid' => TRUE
        );
        $model = new ImpreseeSnippetConfigurationModel;
        $model->use_photo_search = FALSE;
        $model->use_sketch_search = FALSE;
        $model->search_by_photo_icon_url = "value 25";
        $model->search_by_sketch_icon_url = "value 26";
        $model->decimal_separator = ",";
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
        $model->search_bar_selector = 'input';
        $model->use_search_suggestions = TRUE;
        $model->mobile_instant_as_grid = TRUE;
        $model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $storage_key = "see_wccs_snippet_";
        $old_storage_key = 'see_wccs_settings_display';
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo($old_storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($model_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateLocalImpreseeSnippetConfiguration($this->store, $model);
        $this->assertEquals(
            $result,
            $model
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testDontUpdateImpreseeSnippetDataConfigBecauseStoredDataIsTheSame(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $stored_array = array(
            'use_photo_search' => TRUE,
            'use_sketch_search' => TRUE,
            'search_by_photo_icon_url' => 'value 25',
            'search_by_sketch_icon_url' => 'value 26',
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
            'search_bar_selector' => 'input',
            'use_search_suggestions' => FALSE,
            'mobile_instant_as_grid' => TRUE
        );
        $model = new ImpreseeSnippetConfigurationModel;
        $model->use_photo_search = TRUE;
        $model->use_sketch_search = TRUE;
        $model->search_by_photo_icon_url = 'value 25';
        $model->search_by_sketch_icon_url = 'value 26';
        $model->decimal_separator = ",";
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
        $model->search_bar_selector = 'input';
        $model->use_search_suggestions = FALSE;
        $model->mobile_instant_as_grid = TRUE;
        $model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->never())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($stored_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateLocalImpreseeSnippetConfiguration($this->store, $model);
        $this->assertEquals(
            $result,
            $model
        );
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testUpdateImpreseeSnippetDataConfigFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $old_storage_key = 'see_wccs_settings_display';
        $model_array = array(
            'use_photo_search' => TRUE,
            'use_sketch_search' => FALSE,
            'search_by_photo_icon_url' => 'value 25',
            'search_by_sketch_icon_url' => 'value 26',
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
            'search_bar_selector' => 'input',
            'use_search_suggestions' => FALSE,
            'mobile_instant_as_grid' => TRUE,
        );
        $model = new ImpreseeSnippetConfigurationModel;
        $model->use_photo_search = TRUE;
        $model->use_sketch_search = FALSE;
        $model->search_by_photo_icon_url = 'value 25';
        $model->search_by_sketch_icon_url = 'value 26';
        $model->decimal_separator = ",";
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
        $model->search_bar_selector = 'input';
        $model->use_search_suggestions = FALSE;
        $model->mobile_instant_as_grid = TRUE;
        $model->floating_button_location = ImpreseeSnippetConfigurationModel::FLOATING_BUTTON_BOTTOM_RIGHT;
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo($old_storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($model_array)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->updateLocalImpreseeSnippetConfiguration($this->store, $model);
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testRemoveSnippetSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->datasource->removeSnippetLocalData($this->store);
    }

    /**
    * @group ImpreseeSnippet
    */
    public function testRemoveSnippetFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_snippet_";
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotRemoveDataException::class);
        $this->datasource->removeSnippetLocalData($this->store);
    }
}