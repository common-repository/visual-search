<?php 
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Constants\{CatalogMarketCodes, StorageCodes, Project};
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSourceImpl;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\{OwnerModel, ImpreseeSubscriptionStatusModel, PluginVersionModel};
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveDataException;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;


final class ImpreseeLocalDataSourceImplTest extends TestCase {
    private $key_value_storage;
    private $datasource;
    private $store;
    private $impresee_model;
    private $owner_model;
    private $catalog_processing_url;
    private $project;

    protected function setUp(): void{
        $this->key_value_storage = $this->createMock(KeyValueStorage::class);
        $this->project = $this->createMock(Project::class);
        $this->project->method('getVersion')
            ->willReturn('version');
        $storage_codes_stub = $this->createMock(StorageCodes::class);
        $storage_codes_stub->method('getOldImpreseeLocalDataKey')
            ->willReturn('see_wccs_impresee_data');
        $storage_codes_stub->method('getImpreseeLocalDataKeyPrefix')
            ->willReturn('see_wccs_impresee_data_');
        $storage_codes_stub->method('getLocalIndexationConfigKeyPrefix')
            ->willReturn('see_wccs_index_');
        $storage_codes_stub->method('getOldLocalGeneralSettingsKey')
            ->willReturn('see_wccs_settings_general');
        $storage_codes_stub->method('getPluginVersionStorageKey')
            ->willReturn('see_wccs_plugin_version_');
        $this->datasource = new ImpreseeLocalDataSourceImpl(
            $this->key_value_storage,
            $storage_codes_stub,
            $this->project
        );
        $store_url = 'http://ejemplo';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $this->catalog_processing_url = 'http://catalog-processing.com';
        $this->store = new Store;
        $this->store->url = $store_url;
        $this->store->shop_email = $admin_email;
        $this->store->shop_title = $shop_title;
        $this->store->timezone = $timezone;
        $this->store->language = $language;
        $this->store->catalog_generation_code = $catalog_code;

        $this->impresee_model = new ImpreseeConfigurationModel;
        $this->impresee_model->text_app_uuid = '12345';
        $this->impresee_model->sketch_app_uuid = '6789';
        $this->impresee_model->photo_app_uuid = 'abcdre';
        $this->impresee_model->use_clothing = TRUE;
        $this->impresee_model->catalog_processed_once = TRUE;
        $this->impresee_model->catalog_code = 'CATALOG';
        $this->impresee_model->catalog_market = CatalogMarketCodes::APPAREL;
        $this->owner_model = new OwnerModel;
        $this->owner_model->owner_code = 'owner code';
        $this->impresee_model->owner_model = $this->owner_model;
        $this->impresee_model->created_data = TRUE;
        $this->impresee_model->send_catalog_to_update_first_time = TRUE;
        $this->impresee_model->last_catalog_update_url = $this->catalog_processing_url;
    }

    public function testSaveImpreseeConfigurationModelSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key), 
                $this->equalTo($this->impresee_model->toArray())
            )->will($this->returnValue(TRUE));
        $this->datasource->registerImpreseeLocalData(
            $this->store, 
            $this->impresee_model
        );
    }

    public function testSaveImpreseeConfigurationModelFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key), 
                $this->equalTo($this->impresee_model->toArray())
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->registerImpreseeLocalData(
            $this->store, 
            $this->impresee_model
        );
    }


    public function testGetImpreseeStoredConfigurationNoStoredData(){
        $store_name = $this->store->getStoreName();
        $old_storage_key = 'see_wccs_impresee_data';
        $storage_key = "see_wccs_impresee_data_";
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
        $this->expectException(NoDataException::class);
        $this->datasource->getRegisteredImpreseeData(
            $this->store
        ); 
    }

    public function testGetImpreseeStoredConfiguration(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $stored_model = $this->datasource->getRegisteredImpreseeData(
            $this->store
        ); 
        $this->assertEquals(
            $this->impresee_model,
            $stored_model
        );
    }

    public function testGetImpreseeStoredConfigurationFallbackOldKey(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue($stored_array));
        $stored_model = $this->datasource->getRegisteredImpreseeData(
            $this->store
        ); 
        $this->assertEquals(
            $this->impresee_model,
            $stored_model
        );
    }

    public function testGetImpreseeStoredConfigurationFromOldData(){
        $impresee_model = new ImpreseeConfigurationModel;
        $impresee_model->text_app_uuid = 'abcdre';
        $impresee_model->sketch_app_uuid = '6789';
        $impresee_model->photo_app_uuid = 'abcdre';
        $impresee_model->use_clothing = TRUE;
        $impresee_model->catalog_processed_once = TRUE;
        $impresee_model->catalog_code = 'CATALOG';
        $impresee_model->catalog_market = CatalogMarketCodes::APPAREL;
        $impresee_model->owner_model = $this->owner_model;
        $impresee_model->created_data = TRUE;
        $impresee_model->send_catalog_to_update_first_time = TRUE;
        $impresee_model->last_catalog_update_url = '';
        $stored_array = array(
            'photo_clothing_app_uuid' => 'abcdre',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'photo',
            'catalog_generation_code' => 'code',
            'impresee_catalog_code' => 'CATALOG',
            'owner_code' => 'owner code',
            'impresee_owner_active' => true
        );

        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $old_storage_key = 'see_wccs_impresee_data';
        $general_data_storage = 'see_wccs_settings_general'; 
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo($old_storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->at(3))
            ->method('getValue')
            ->with(
                $this->equalTo($general_data_storage)
            )->will($this->returnValue(['product_type' => 'apparel']));
        $stored_model = $this->datasource->getRegisteredImpreseeData(
            $this->store
        ); 
        $this->assertEquals(
            $impresee_model,
            $stored_model
        );
    }

    public function testSaveOwnerModelSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key), 
                $this->equalTo($this->owner_model->toArray())
            )->will($this->returnValue(TRUE));
        $this->datasource->registerLocalOwner(
            $this->store, 
            $this->owner_model
        );
    }

    public function testSaveOwnerModelFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key), 
                $this->equalTo($this->owner_model->toArray())
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->registerLocalOwner(
            $this->store, 
            $this->owner_model
        );
    }


    public function testUpdateCatalogProcessedOnceSuccessfully(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => False,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(TRUE));
        $this->datasource->setCatalogProcessedOnce(
            $this->store
        );
    }

    public function testUpdateCatalogProcessedOnceSuccessfullyCatalogAlreadyMarked(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
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
            );
        $this->datasource->setCatalogProcessedOnce(
            $this->store
        );
    }

    public function testUpdateCatalogProcessedOnceFailsBecauseCouldNotStoreData(){
        $store_name = $this->store->getStoreName();
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => False,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->setCatalogProcessedOnce(
            $this->store
        ); 
    }

    public function testUpdateCatalogProcessedOnceFailsBecauseThereIsNoData(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $old_storage_key = 'see_wccs_impresee_data';
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
        $this->expectException(NoDataException::class);
        $this->datasource->setCatalogProcessedOnce(
            $this->store
        ); 
    }

    public function testGetStoredOwnerConfiguration(){
        $expected_array = array(
            'owner_code' => 'owner code' 
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($expected_array));
        $stored_owner = $this->datasource->getRegisteredOwner(
            $this->store
        ); 
        $this->assertEquals(
            $this->owner_model,
            $stored_owner
        );
    }

    public function testGetStoredOwnerConfigurationFallbackOldKey(){
        $expected_array = array(
            'owner_code' => 'owner code' 
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key.$store_name)
            )->will($this->returnValue($expected_array));
        $stored_owner = $this->datasource->getRegisteredOwner(
            $this->store
        ); 
        $this->assertEquals(
            $this->owner_model,
            $stored_owner
        );
    }

    public function testGetStoredOwnerConfigurationNoStoredData(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
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
        $this->expectException(NoDataException::class);
        $this->datasource->getRegisteredOwner(
            $this->store
        ); 
    }

    public function testRemoveAllDataSuccessfully(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $index_storage_key = "see_wccs_index_";
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
        $this->key_value_storage->expects($this->at(2))
            ->method('removeKey')
            ->with(
                $this->equalTo($index_storage_key)
            )->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(3))
            ->method('removeKey')
            ->with(
                $this->equalTo($index_storage_key.$store_name)
            )->will($this->returnValue(TRUE));
        $this->datasource->removeAllLocalData(
            $this->store
        ); 
    }

    public function testRemoveAllDataWithErrors(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $index_storage_key = "see_wccs_index_";
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
        $this->key_value_storage->expects($this->at(2))
            ->method('removeKey')
            ->with(
                $this->equalTo($index_storage_key)
            )->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(3))
            ->method('removeKey')
            ->with(
                $this->equalTo($index_storage_key.$store_name)
            )->will($this->returnValue(TRUE));
        $this->expectException(CouldNotRemoveDataException::class);
        $this->datasource->removeAllLocalData(
            $this->store
        ); 
    }

    public function testSetCreatedImpreseeDataSuccessfully(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => FALSE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(TRUE));
        $this->datasource->setCreatedImpreseeData(
            $this->store
        );
    }

    public function testSetCreatedImpreseeDataAlreadyMarked(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
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
            );
        $this->datasource->setCreatedImpreseeData(
            $this->store
        );
    }


    public function testSetCreatedImpreseeDataFailsBecauseCouldNotStoreData(){
        $store_name = $this->store->getStoreName();
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => FALSE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->setCreatedImpreseeData(
            $this->store
        ); 
    }

    public function testSetSentCatalogToUpdateSuccessfully(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => FALSE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(TRUE));
        $this->datasource->setSentCatalogToUpdate(
            $this->store
        );
    }

    public function testSetSentCatalogToUpdateAlreadyMarked(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
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
            );
        $this->datasource->setSentCatalogToUpdate(
            $this->store
        );
    }


    public function testSetSentCatalogToUpdateFailsBecauseCouldNotStoreData(){
        $store_name = $this->store->getStoreName();
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => FALSE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->setSentCatalogToUpdate(
            $this->store
        ); 
    }

    public function testSetLastCatalogUpdateProcessUrl(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => ''
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(TRUE));
        $this->datasource->setLastCatalogUpdateProcessUrl(
            $this->store,
            $this->catalog_processing_url
        );
    }

        public function testSetLastCatalogUpdateProcessUrlFailsBecauseCouldNotStoreData(){
        $store_name = $this->store->getStoreName();
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => ''
        );
        $expected_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($expected_to_save)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->setLastCatalogUpdateProcessUrl(
            $this->store,
            $this->catalog_processing_url
        ); 
    }

    public function testGetIndexationConfigurationDataFromKeyValueStorage(){
        $store_name = $this->store->getStoreName();
        $old_storage_key = 'see_wccs_settings_general';
        $storage_key = "see_wccs_index_";
        $stored_array = array(
            'only_products_with_price' => FALSE,
            'only_products_in_stock'   => FALSE
        );
        $expected_model = new IndexationConfigurationModel;
        $expected_model->only_products_with_price = FALSE;
        $expected_model->only_products_in_stock = FALSE;
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue($stored_array));
        $result = $this->datasource->getIndexationConfiguration($this->store);
        $this->assertEquals(
            $result,
            $expected_model
        );
    }

    public function testGetIndexationConfigurationDataFromKeyValueStorageFallbackOldKey(){
        $store_name = $this->store->getStoreName();
        $old_storage_key = 'see_wccs_settings_general';
        $storage_key = "see_wccs_index_";
        $stored_array = array(
            'only_products_with_price' => FALSE,
            'only_products_in_stock'   => FALSE
        );
        $expected_model = new IndexationConfigurationModel;
        $expected_model->only_products_with_price = FALSE;
        $expected_model->only_products_in_stock = FALSE;
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo($storage_key))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue($stored_array));
        $result = $this->datasource->getIndexationConfiguration($this->store);
        $this->assertEquals(
            $result,
            $expected_model
        );
    }

    public function testGetIndexationConfigurationDataFromKeyValueStorageFromOldConfiguration(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_index_";
        $old_storage_key = 'see_wccs_settings_general';
        $stored_array = array(
            'enable_search' => 'enabled',
            'show_products_no_price' => 'disabled',
            'catalog_stock' => 'all'
        );
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo($storage_key.$store_name))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo($old_storage_key)
            )->will($this->returnValue($stored_array));
        $expected_model = new IndexationConfigurationModel;
        $expected_model->only_products_with_price = TRUE;
        $expected_model->only_products_in_stock = FALSE;
        $result = $this->datasource->getIndexationConfiguration($this->store);
        $this->assertEquals(
            $result,
            $expected_model
        );
    }

    public function testGetIndexationConfigurationDataFromKeyValueStorageNoStoredData(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_index_";
        $old_storage_key = 'see_wccs_settings_general';
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
        $this->expectException(NoDataException::class);
        $this->datasource->getIndexationConfiguration($this->store);
    }

    public function testUpdateIndexationConfigCorrectly(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_index_";
        $old_storage_key = 'see_wccs_settings_general';
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = TRUE;
        $model->only_products_in_stock = FALSE;
        $model_array= array(
            'only_products_with_price' => TRUE,
            'only_products_in_stock'   => FALSE
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
        $result = $this->datasource->updateIndexationConfiguration($this->store, $model);
        $this->assertEquals(
            $result,
            $model
        );
    }

    public function testDontUpdateIndexationConfigBecauseStoredDataIsTheSame(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_index_";
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = TRUE;
        $model->only_products_in_stock = FALSE;
        $model_array= array(
            'only_products_with_price' => TRUE,
            'only_products_in_stock'   => FALSE
        );
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($model_array));
        $this->key_value_storage->expects($this->never())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo($model_array)
            )->will($this->returnValue(TRUE));
        $result = $this->datasource->updateIndexationConfiguration($this->store, $model);
        $this->assertEquals(
            $result,
            $model
        );
    }

    public function testUpdateIndexationConfigFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_index_";
        $old_storage_key = 'see_wccs_settings_general';
        $model = new IndexationConfigurationModel;
        $model->only_products_with_price = TRUE;
        $model->only_products_in_stock = FALSE;
        $model_array= array(
            'only_products_with_price' => TRUE,
            'only_products_in_stock'   => FALSE
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
        $this->datasource->updateIndexationConfiguration($this->store, $model);
    }

    public function testGetLocalSubscriptionStatusDataNotSuspended(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url,
            'suspended' => FALSE
        );
        $status_model = new ImpreseeSubscriptionStatusModel;
        $status_model->suspended = FALSE;
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));

        $stored_status = $this->datasource->getLocalSubscriptionStatusData(
            $this->store
        ); 
        $this->assertEquals(
            $status_model,
            $stored_status
        );
    }

    public function testGetLocalSubscriptionStatusDataSuspended(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url,
            'suspended' => TRUE
        );
        $status_model = new ImpreseeSubscriptionStatusModel;
        $status_model->suspended = TRUE;
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $stored_status = $this->datasource->getLocalSubscriptionStatusData(
            $this->store
        ); 
        $this->assertEquals(
            $status_model,
            $stored_status
        );
    }

    public function testGetLocalSubscriptionStatusDataNoStoredDataNoImpreseeData(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
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
        $this->expectException(NoDataException::class);
        $this->datasource->getLocalSubscriptionStatusData(
            $this->store
        ); 
    }

    public function testGetLocalSubscriptionStatusDataNoStoredData(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $status_model = new ImpreseeSubscriptionStatusModel;
        $status_model->suspended = TRUE;
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->expectException(NoDataException::class);
        $this->datasource->getLocalSubscriptionStatusData(
            $this->store
        ); 
    }

    public function testUpdateLocalSubscriptionStatusData(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $array_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url,
            'suspended' => TRUE
        );
        $status_model = new ImpreseeSubscriptionStatusModel;
        $status_model->suspended = TRUE;
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->at(1))
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key), 
                $this->equalTo($array_to_save)
            )->will($this->returnValue(TRUE));
        $this->datasource->updateLocalSubscriptionStatusData(
            $this->store, 
            $status_model
        );
    }

    public function testUpdateLocalSubscriptionStatusDataFails(){
        $stored_array = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url
        );
        $array_to_save = array(
            'text_app_uuid' => '12345',
            'sketch_app_uuid' => '6789',
            'photo_app_uuid' => 'abcdre',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'CATALOG',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => $this->catalog_processing_url,
            'suspended' => TRUE
        );
        $status_model = new ImpreseeSubscriptionStatusModel;
        $status_model->suspended = TRUE;
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_impresee_data_";
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue($stored_array));
        $this->key_value_storage->expects($this->at(1))
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key), 
                $this->equalTo($array_to_save)
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->updateLocalSubscriptionStatusData(
            $this->store, 
            $status_model
        );
    }

    public function testUpdateStoredPluginVersion(){
        $expected_model = new PluginVersionModel;
        $expected_model->version = 'version';
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_plugin_version_";
        $this->project->expects($this->once())
            ->method('getVersion')
            ->will($this->returnValue('version'));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo(array('plugin_version' => 'version'))
            )->will($this->returnValue(TRUE));
        $this->datasource->updateStoredPluginVersion(
            $this->store
        );
    }

    public function testUpdateStoredPluginVersionFails(){
        $expected_model = new PluginVersionModel;
        $expected_model->version = 'version';
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_plugin_version_";
        $this->project->expects($this->once())
            ->method('getVersion')
            ->will($this->returnValue('version'));
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with(
                $this->equalTo($storage_key),
                $this->equalTo(array('plugin_version' => 'version'))
            )->will($this->returnValue(FALSE));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->updateStoredPluginVersion(
            $this->store
        );
    }

    public function testGetStoredPluginVersion(){
        $expected_model = new PluginVersionModel;
        $expected_model->version = 'version';
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_plugin_version_";
        $this->project->expects($this->never())
            ->method('getVersion')
            ->will($this->returnValue('version'));
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo($storage_key)
            )->will($this->returnValue(array('plugin_version' => 'version')));
        $model = $this->datasource->getStoredPluginVersion(
            $this->store
        );
        $this->assertEquals(
            $model,
            $expected_model
        );
    }

    public function testGetStoredPluginVersionFails(){
        $store_name = $this->store->getStoreName();
        $storage_key = "see_wccs_plugin_version_";
        $this->project->expects($this->never())
            ->method('getVersion')
            ->will($this->returnValue('version'));
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
        $this->expectException(NoDataException::class);
        $this->datasource->getStoredPluginVersion(
            $this->store
        );
    }
}