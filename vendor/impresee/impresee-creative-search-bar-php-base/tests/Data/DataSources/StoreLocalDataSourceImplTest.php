<?php 
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage;
    use Impresee\CreativeSearchBar\Core\Utils\RestInterface;
    use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSourceImpl;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveStoreCodeException;


final class StoreLocalDataSourceImplTest extends TestCase { 
    private $key_value_storage;
    private $rest_interface;
    private $datasource;

    protected function setUp(): void{
        $this->key_value_storage = $this->createMock(KeyValueStorage::class);
        $this->rest_interface = $this->createMock(RestInterface::class);
        $storage_codes_stub = $this->createMock(StorageCodes::class);
        $storage_codes_stub->method('getOldImpreseeLocalDataKey')
            ->willReturn('see_wccs_impresee_data');
        $storage_codes_stub->method('getStoreCatalogCodeKeyPrefix')
            ->willReturn('see_wccs_store_catalog_code_');
        $storage_codes_stub->method('getStoreFinishedOnboardingKeyPrefix')
            ->willReturn('see_wccs_store_finished_onboarding_');
        $storage_codes_stub->method('getOldLocalGeneralSettingsKey')
            ->willReturn('see_wccs_settings_general');
        $storage_codes_stub->method('getLocaleKey')
            ->willReturn('LOCALE');
        $storage_codes_stub->method('getSiteTitleKey')
            ->willReturn('SITE_TITLE');
        $storage_codes_stub->method('getSiteHomeKey')
            ->willReturn('home');
        $storage_codes_stub->method('getUserEmailKey')
            ->willReturn('admin_email');
        $storage_codes_stub->method('getTimezoneKey')
            ->willReturn('timezone_string');
        $this->datasource = new StoreLocalDataSourceImpl(
            $this->key_value_storage,
            $this->rest_interface,
            $storage_codes_stub
        );
    }
public function testGetStoreUrl(){
        $expected_url = 'http://example.com';
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('home'))
            ->will($this->returnValue($expected_url));
        $store_url = $this->datasource->getStoreUrl();
        $this->assertEquals(
            $expected_url,
            $store_url
        );
    }

    public function testGetStoreUrlFails(){
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('home'))
            ->will($this->returnValue(FALSE));
        $this->expectException(NoDataException::class);
        $this->datasource->getStoreUrl();
    }

    public function testGetAdminEmail(){
        $expected_email = 'example@example.com';
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('admin_email'))
            ->will($this->returnValue($expected_email));
        $admin_email = $this->datasource->getStoreAdminData();
        $this->assertEquals(
            $expected_email,
            $admin_email
        );
    }

    public function testGetLocale(){
        $expected_language = 'es';
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('LOCALE'))
            ->will($this->returnValue($expected_language));
        $language = $this->datasource->getLanguage();
        $this->assertEquals(
            $expected_language,
            $language
        );
    }

    public function testGetSiteTitle(){
        $expected_site_title = 'Example Site';
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('SITE_TITLE'))
            ->will($this->returnValue($expected_site_title));
        $site_title = $this->datasource->getSiteTitle();
        $this->assertEquals(
            $expected_site_title,
            $site_title
        );
    }

    public function testGetTimezone(){
        $expected_timezone = 'Timezone string';
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('timezone_string'))
            ->will($this->returnValue($expected_timezone));
        $timezone = $this->datasource->getTimezone();
        $this->assertEquals(
            $expected_timezone,
            $timezone
        );
    }

    public function testGetStoreCatalogCode(){
        $site_url = 'http://example.com';
        $code = 'catalog code';
        $old_code_location = 'see_wccs_impresee_data';
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo(
                $old_code_location
            ))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ))
            ->will($this->returnValue($code));
        $catalog_code = $this->datasource->getCurrentCatalogGenerationCode($site_url);
        $this->assertEquals(
            $code,
            $catalog_code
        );
    }

    public function testGetStoreCatalogCodeRollbackOldKey(){
        $site_url = 'http://example.com';
        $code = 'catalog code';
        $old_code_location = 'see_wccs_impresee_data';
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo(
                $old_code_location
            ))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ))
            ->will($this->returnValue(FALSE));
            $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_{$site_url}"
            ))
            ->will($this->returnValue($code));
        $catalog_code = $this->datasource->getCurrentCatalogGenerationCode($site_url);
        $this->assertEquals(
            $code,
            $catalog_code
        );
    }

    public function testGetStoreCatalogCodeFromOldContainer(){
        $code = 'catalog code';
        $impresee_data_save = array(
            'photo_clothing_app_uuid' => 'app apparel',
            'sketch_app_uuid'         => 'app sketch',
            'photo_app_uuid'          => 'app home decor',
            'owner_code'              => 'owner code',
            'impresee_catalog_code'   => 'catalog code',
            'catalog_generation_code' => $code,
            'impresee_owner_active'   => TRUE,
        );
        $site_url = 'http://example.com';
        $old_code_location = 'see_wccs_impresee_data';
        $this->key_value_storage->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo(
                $old_code_location
            ))
            ->will($this->returnValue($impresee_data_save));
        $catalog_code = $this->datasource->getCurrentCatalogGenerationCode($site_url);
        $this->assertEquals(
            $code,
            $catalog_code
        );
    }

    public function testGetCodeNoStoredValue(){
        $site_url = 'http://example.com';
        $code = 'catalog code';
        $old_code_location = 'see_wccs_impresee_data';
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo(
                $old_code_location
            ))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(2))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_{$site_url}"
            ))
            ->will($this->returnValue(FALSE));
        $this->expectException(NoDataException::class);
        $this->datasource->getCurrentCatalogGenerationCode($site_url);
    }

    public function testStoreCodeSuccessfully(){
        $site_url = 'http://example.com';
        $code = 'catalog code';
        $return_status = TRUE;
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ), $this->equalTo($code))
            ->will($this->returnValue($return_status));
        $this->datasource->storeCatalogGenerationCode($site_url, $code);
    }

    public function testStoreCodeFails(){
        $site_url = 'http://example.com';
        $code = 'catalog code';
        $return_status = FALSE;
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ), $this->equalTo($code))
            ->will($this->returnValue($return_status));
        $this->expectException(CouldNotStoreDataException::class);
        $this->datasource->storeCatalogGenerationCode($site_url, $code);
    }

    public function testRemoveDataSuccessfully(){
        $site_url = 'http://example.com';
        $return_status = TRUE;
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ))
            ->will($this->returnValue($return_status));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_{$site_url}"
            ))
            ->will($this->returnValue($return_status));
        $this->key_value_storage->expects($this->at(2))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_"
            ))
            ->will($this->returnValue($return_status));
        $this->key_value_storage->expects($this->at(3))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_{$site_url}"
            ))
            ->will($this->returnValue($return_status));
        $this->datasource->removeStoreData($site_url);
    }

    public function testRemoveDataFailsBecauseOfCode(){
        $site_url = 'http://example.com';
        $return_status = FALSE;
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ))
            ->will($this->returnValue($return_status));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_{$site_url}"
            ))
            ->will($this->returnValue($return_status));
        $this->key_value_storage->expects($this->at(2))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_"
            ))
            ->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(3))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_{$site_url}"
            ))
            ->will($this->returnValue(TRUE));
        $this->expectException(CouldNotRemoveStoreCodeException::class);  
        $this->datasource->removeStoreData($site_url);
    }

    public function testRemoveDataFailsBecauseOfOnboarding(){
        $site_url = 'http://example.com';
        $return_status = FALSE;
        $this->key_value_storage->expects($this->at(0))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_"
            ))
            ->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(1))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_catalog_code_{$site_url}"
            ))
            ->will($this->returnValue(TRUE));
        $this->key_value_storage->expects($this->at(2))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_"
            ))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(3))
            ->method('removeKey')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_{$site_url}"
            ))
            ->will($this->returnValue(FALSE));
        $this->expectException(CouldNotRemoveStoreCodeException::class);  
        $this->datasource->removeStoreData($site_url);
    }

    public function testStoreFinishedOnboarding(){
        $site_url = 'http://example.com';
        $expected_status = TRUE;
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_"
            ))
            ->will($this->returnValue($expected_status));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_{$site_url}"
            ))
            ->will($this->returnValue(FALSE));
  
        $return_value = $this->datasource->finishedOnboarding($site_url);
        $this->assertEquals(
            $return_value,
            $expected_status
        );
    }

    public function testSetStoreFinishedOnboardingFallbackOldKey(){
        $site_url = 'http://example.com';
        $expected_status = TRUE;
        $this->key_value_storage->expects($this->at(0))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_"
            ))
            ->will($this->returnValue(FALSE));
        $this->key_value_storage->expects($this->at(1))
            ->method('getValue')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_{$site_url}"
            ))
            ->will($this->returnValue($expected_status));
  
        $return_value = $this->datasource->finishedOnboarding($site_url);
        $this->assertEquals(
            $return_value,
            $expected_status
        );
    }

    public function testSetStoreFinishedOnboarding(){
        $site_url = 'http://example.com';
        $expected_status = TRUE;
        $this->key_value_storage->expects($this->once())
            ->method('saveValue')
            ->with($this->equalTo(
                "see_wccs_store_finished_onboarding_"
            ), $this->equalTo(TRUE))
            ->will($this->returnValue($expected_status));
  
        $this->datasource->setFinishedOnboarding($site_url);
    }

    public function testGetRestUrlStore(){
        $expected_catalog_url = 'https://example.com';
        $code = 'code';
        $this->rest_interface->expects($this->once())
            ->method('getRestUrlCatalog')
            ->with($this->equalTo($code))
            ->will($this->returnValue($expected_catalog_url));
  
        $catalog_url = $this->datasource->getCreateCatalogUrl($code);
        $this->assertEquals(
            $catalog_url,
            $expected_catalog_url
        );
    }
}