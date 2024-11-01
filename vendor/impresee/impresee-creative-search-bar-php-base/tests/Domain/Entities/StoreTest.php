<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

final class StoreTest extends TestCase {
    
    public function testGetStoreName(){
        $expected_store_name = "ejemplo_com";
        $store_url = 'http://ejemplo.com';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $store = new Store;
        $store->url = $store_url;
        $store->shop_email = $admin_email;
        $store->shop_title = $shop_title;
        $store->timezone = $timezone;
        $store->language = $language;
        $store->catalog_generation_code = $catalog_code;
        $store_name = $store->getStoreName();
        $this->assertEquals(
            $expected_store_name,
            $store_name
        );
    }

    public function testHasValidUrlValid() {
        $expected_return = TRUE;
        $expected_store_name = "ejemplo_com";
        $store_url = 'http://ejemplo.com';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $store = new Store;
        $store->url = $store_url;
        $store->shop_email = $admin_email;
        $store->shop_title = $shop_title;
        $store->timezone = $timezone;
        $store->language = $language;
        $store->catalog_generation_code = $catalog_code;
        $value = $store->hasValidUrl();
        $this->assertEquals(
            $value,
            $expected_return
        );
    }

    public function testHasValidUrlinvalidEmpty() {
        $expected_return = FALSE;
        $store_url = '';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $store = new Store;
        $store->url = $store_url;
        $store->shop_email = $admin_email;
        $store->shop_title = $shop_title;
        $store->timezone = $timezone;
        $store->language = $language;
        $store->catalog_generation_code = $catalog_code;
        $value = $store->hasValidUrl();
        $this->assertEquals(
            $value,
            $expected_return
        );
    }

    public function testHasValidUrlinvalidLocalhost() {
        $expected_return = FALSE;
        $store_url = 'http://localhost/';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $store = new Store;
        $store->url = $store_url;
        $store->shop_email = $admin_email;
        $store->shop_title = $shop_title;
        $store->timezone = $timezone;
        $store->language = $language;
        $store->catalog_generation_code = $catalog_code;
        $value = $store->hasValidUrl();
        $this->assertEquals(
            $value,
            $expected_return
        );
    }

    public function testHasValidUrlinvalidLocalIP() {
        $expected_return = FALSE;
        $store_url = 'http://127.0.0.1/';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $store = new Store;
        $store->url = $store_url;
        $store->shop_email = $admin_email;
        $store->shop_title = $shop_title;
        $store->timezone = $timezone;
        $store->language = $language;
        $store->catalog_generation_code = $catalog_code;
        $value = $store->hasValidUrl();
        $this->assertEquals(
            $value,
            $expected_return
        );
    }
}