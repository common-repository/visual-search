<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;


class ImpreseeConfigurationModelTest extends TestCase {
    private $text_app_uuid;
    private $sketch_app_uuid;
    private $photo_app_uuid;
    private $clothing_app_uuid;
    private $owner_model;
    private $use_clothing;
    private $catalog_processed_once;
    private $catalog_code;
    private $catalog_market;
    private $created_data;
    private $send_catalog_to_update_first_time;
    private $last_catalog_update_url;

    protected function setUp(): void{
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner_code';
        $this->text_app_uuid = 'app_uuid_text';
        $this->sketch_app_uuid = 'sketch_app_uuid';
        $this->photo_app_uuid = 'photo_app_uuid';
        $this->clothing_app_uuid = 'clothing_app_uuid';
        $this->use_clothing = TRUE;
        $this->catalog_processed_once = TRUE;
        $this->catalog_code = 'catalog_code';
        $this->catalog_market = CatalogMarketCodes::APPAREL;
        $this->owner_model = $owner_model;
        $this->created_data = TRUE;
        $this->send_catalog_to_update_first_time = TRUE;
        $this->last_catalog_update_url = 'http://example.com';
    }

    public function testConfigurationModelToArray(){
        $configuration_model = new ImpreseeConfigurationModel;
        $configuration_model->text_app_uuid = $this->text_app_uuid;    
        $configuration_model->sketch_app_uuid = $this->sketch_app_uuid;
        $configuration_model->photo_app_uuid = $this->photo_app_uuid;
        $configuration_model->owner_model = $this->owner_model;
        $configuration_model->use_clothing = $this->use_clothing;
        $configuration_model->catalog_processed_once = $this->catalog_processed_once;
        $configuration_model->catalog_code = $this->catalog_code;
        $configuration_model->catalog_market = $this->catalog_market;
        $configuration_model->created_data = $this->created_data;
        $configuration_model->send_catalog_to_update_first_time = $this->send_catalog_to_update_first_time;
        $configuration_model->last_catalog_update_url = $this->last_catalog_update_url;
        $expected_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => 'http://example.com',
        );
        $this->assertEquals(
            $expected_array,
            $configuration_model->toArray()
        );
    }

    public function testConfigurationModelFromArray(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code',
            'created_data' => TRUE,
            'send_catalog_to_update_first_time' => TRUE,
            'last_catalog_update_url' => 'http://example.com',
        );
        $configuration_model = new ImpreseeConfigurationModel;
        $configuration_model->text_app_uuid = $this->text_app_uuid;    
        $configuration_model->sketch_app_uuid = $this->sketch_app_uuid;
        $configuration_model->photo_app_uuid = $this->photo_app_uuid;
        $configuration_model->owner_model = $this->owner_model;
        $configuration_model->use_clothing = $this->use_clothing;
        $configuration_model->catalog_processed_once = $this->catalog_processed_once;
        $configuration_model->catalog_code = $this->catalog_code;
        $configuration_model->catalog_market = $this->catalog_market;
        $configuration_model->created_data = $this->created_data;
        $configuration_model->send_catalog_to_update_first_time = $this->send_catalog_to_update_first_time;
        $configuration_model->last_catalog_update_url = $this->last_catalog_update_url;
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
        $this->assertEquals(
            $configuration_model,
            $empty_model
        );
    }

    public function testConfigurationModelFromArrayMissingTextApp(){
        $data_array = array(
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingSketchApp(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingPhotoApp(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingUseClothing(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingCatalogProcessedOnce(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingCatalogCode(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_market' => 'CLOTHES',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingCatalogMarket(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'owner_code' => 'owner_code'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayMissingOwnerCode(){
        $data_array = array(
            'text_app_uuid' => 'app_uuid_text',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'use_clothing' => TRUE,
            'catalog_processed_once' => TRUE,
            'catalog_code' => 'catalog_code',
            'catalog_market' => 'CLOTHES'
        );
        $this->expectException(NoDataException::class);
        $empty_model = new ImpreseeConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testConfigurationModelFromArrayOldData(){
        $stored_array = array(
            'photo_clothing_app_uuid' => 'clothing_app_uuid',
            'sketch_app_uuid' => 'sketch_app_uuid',
            'photo_app_uuid' => 'photo_app_uuid',
            'catalog_generation_code' => 'code',
            'impresee_catalog_code' => 'catalog_code',
            'owner_code' => 'owner_code',
            'impresee_owner_active' => true
        );
        $configuration_model = new ImpreseeConfigurationModel;
        $configuration_model->text_app_uuid = $this->clothing_app_uuid;    
        $configuration_model->sketch_app_uuid = $this->sketch_app_uuid;
        $configuration_model->photo_app_uuid = $this->clothing_app_uuid;
        $configuration_model->owner_model = $this->owner_model;
        $configuration_model->use_clothing = $this->use_clothing;
        $configuration_model->catalog_processed_once = TRUE;
        $configuration_model->catalog_code = $this->catalog_code;
        $configuration_model->catalog_market = $this->catalog_market;
        $configuration_model->created_data = $this->created_data;
        $configuration_model->send_catalog_to_update_first_time = $this->send_catalog_to_update_first_time;
        $configuration_model->last_catalog_update_url = '';
        $product_type = 'apparel';
        $this->assertEquals(
            $configuration_model,
            ImpreseeConfigurationModel::fromArrayOldStorage($stored_array, $product_type)
        );
    }
}