<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException, CouldNotStoreDataException, CouldNotRemoveDataException};
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSourceImpl;

class HolidayConfigurationLocalDataSourceImplTest extends TestCase {
    private $key_value_storage;
    private $datasource;
    private $store;
    private $model;
    private $data_array;

    protected function setUp(): void{
        $this->key_value_storage = $this->createMock(KeyValueStorage::class);
        $storage_codes_stub = $this->createMock(StorageCodes::class);
        $storage_codes_stub->method('getLocalHolidayConfigKeyPrefix')
            ->willReturn('see_wccs_holiday_config_');
        $this->datasource = new HolidayConfigurationLocalDataSourceImpl(
            $this->key_value_storage,
            $storage_codes_stub
        );
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
        $this->model = new HolidayConfigurationModel;
        $this->model->pop_up_title = "PopUp title";
        $this->model->pop_up_text = "PopUp text";
        $this->model->searchbar_placeholder = "placeholder";
        $this->model->search_drawing_button = "draw";
        $this->model->search_photo_button = "photo";
        $this->model->search_dropdown_label = "results";
        $this->model->to_label_letter = "to";
        $this->model->from_label_letter = "from";
        $this->model->placeholder_message_letter = "message";
        $this->model->title_canvas = "title";
        $this->model->search_button_canvas = "search";
        $this->model->button_in_product_page = "product";
        $this->model->search_results_title = "search results";
        $this->model->results_title_for_text_search = "text search results";
        $this->model->christmas_letter_share_message = "share message";
        $this->model->christmas_letter_share = "share";
        $this->model->christmas_letter_receiver_button = "this is";
        $this->model->is_mode_active = TRUE;
        $this->model->theme = HolidayConfigurationModel::ACCENT;
        $this->model->automatic_popup = TRUE;
        $this->model->add_style_to_search_bar = TRUE;
        $this->model->store_logo_url = "url";
        $this->data_array = array(
            'pop_up_title' => "PopUp title",
            'pop_up_text' => "PopUp text",
            'searchbar_placeholder' => "placeholder",
            'search_drawing_button' => "draw",
            'search_photo_button' => "photo",
            'search_dropdown_label' => "results",
            'to_label_letter' => "to",
            'from_label_letter' => "from",
            'placeholder_message_letter' => "message",
            'title_canvas' => "title",
            'search_button_canvas' => "search",
            'button_in_product_page' => "product",
            'search_results_title' => "search results",
            'results_title_for_text_search' => "text search results",
            'christmas_letter_share_message' => "share message",
            'christmas_letter_share' => "share",
            'christmas_letter_receiver_button' => "this is",
            'is_mode_active' => TRUE,
            'theme' => 1,
            'automatic_popup' => TRUE,
            'add_style_to_search_bar' => TRUE,
            'store_logo_url' => "url"
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testGetDataSucessfullyFromKeyValueStorage(){
        $storage_key = "see_wccs_holiday_config_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue($this->data_array));
        $returned_model = $this->datasource->getLocalHolidayConfiguration($this->store);
        $this->assertEquals(
            $this->model,
            $returned_model
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testGetDataSucessfullyFromKeyValueStorageFallbackOldKey(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_holiday_config_";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue($this->data_array));
        $returned_model = $this->datasource->getLocalHolidayConfiguration($this->store);
        $this->assertEquals(
            $this->model,
            $returned_model
        );
    }


    /**
    * @group HolidayConfig
    */
    public function testThrowExceptionWhenNoArrayIsPresent(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_holiday_config_";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue(FALSE));
        $this->expectException(NoDataException::class);
        $this->datasource->getLocalHolidayConfiguration($this->store);
    }

    /**
    * @group HolidayConfig
    */
    public function testUpdateHolidayConfigSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_holiday_config_";
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
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($this->data_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateLocalHolidayConfiguration($this->store, $this->model);
        $this->assertEquals(
            $this->model,
            $result
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testDontUpdateCustomCodeConfigBecauseStoredDataIsTheSame(){
        $storage_key = "see_wccs_holiday_config_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($this->data_array));
        $this->key_value_storage->expects($this->never())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($this->data_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateLocalHolidayConfiguration($this->store, $this->model);
        $this->assertEquals(
            $this->model,
            $result
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testUpdateCustomCodeConfigFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_holiday_config_";
        
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
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($this->data_array)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->updateLocalHolidayConfiguration($this->store, $this->model);
    }

    /**
    * @group HolidayConfig
    */
    public function testRemoveCustomCodeSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_holiday_config_";
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
        $this->datasource->removeLocalHolidayConfiguration($this->store);
    }

    /**
    * @group HolidayConfig
    */
    public function testRemoveCustomCodeFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_holiday_config_";
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
        $this->datasource->removeLocalHolidayConfiguration($this->store);
    }

}