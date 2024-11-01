<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\StoreModel;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Mappers\StoreModel2StoreMapper;

class StoreModel2StoreMapperTest extends TestCase {
    private $mapper;
    private $url;
    private $site_title;
    private $admin_email;
    private $timezone;
    private $language;
    private $catalog_generation_code;

    protected function setUp(): void {
        $this->mapper = new StoreModel2StoreMapper;
        $this->url = 'https://example.com';
        $this->site_title = 'Example';
        $this->admin_email = 'admin@example.com';
        $this->timezone = 'UTC-5';
        $this->language = 'en';
        $this->catalog_generation_code = 'hidden_code';
    }

    public function testMapModelToStore(){
        $model = new StoreModel;
        $model->url = $this->url;
        $model->site_title = $this->site_title;
        $model->admin_email = $this->admin_email;
        $model->timezone = $this->timezone;
        $model->language = $this->language;
        $model->catalog_generation_code = $this->catalog_generation_code;
        $expected_store = new Store;
        $expected_store->url = $this->url;
        $expected_store->shop_email = $this->admin_email;
        $expected_store->shop_title = $this->site_title;
        $expected_store->language = $this->language;
        $expected_store->timezone = $this->timezone;
        $expected_store->catalog_generation_code = $this->catalog_generation_code;
        $this->assertEquals(
            $expected_store,
            $this->mapper->mapFrom($model)
        );
    }

    public function testStoreToMapModel(){
        $store = new Store;
        $store->url = $this->url;
        $store->shop_email = $this->admin_email;
        $store->shop_title = $this->site_title;
        $store->language = $this->language;
        $store->timezone = $this->timezone;
        $store->catalog_generation_code = $this->catalog_generation_code;
        $expected_model = new StoreModel;
        $expected_model->url = $this->url;
        $expected_model->site_title = $this->site_title;
        $expected_model->admin_email = $this->admin_email;
        $expected_model->timezone = $this->timezone;
        $expected_model->language = $this->language;
        $expected_model->catalog_generation_code = $this->catalog_generation_code;   
        $this->assertEquals(
            $expected_model,
            $this->mapper->mapTo($store)
        );
    }

}