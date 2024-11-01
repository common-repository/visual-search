<?php
    use PHPUnit\Framework\TestCase;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\{Right, Left};
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Repositories\StoreRepositoryImpl;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\CodesDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\NoStoreUrlFailure;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\FailureStoreCatalogGenerationCode;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

final class StoreRepositoryImplTest extends TestCase {
    private $repository;
    private $data_source;
    private $codes_data_source;
    private $email_datasource;
    
    protected function setUp(): void {
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getProjectName')
            ->willReturn('Impresee');
        $this->email_datasource = $this->createMock(EmailDataSource::class);
        $this->codes_data_source = $this->createMock(CodesDataSource::class);
        $this->data_source = $this->createMock(StoreLocalDataSource::class);
        $this->repository = new StoreRepositoryImpl($this->data_source,
         $this->codes_data_source,
         $this->email_datasource,
         $project_stub
        );
    }

    public function testGetAllStoreInformationStoredCode() {
        $store_url = 'http://ejemplo';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $expected_store = new Store;
        $expected_store->url = $store_url;
        $expected_store->shop_email = $admin_email;
        $expected_store->shop_title = $shop_title;
        $expected_store->timezone = $timezone;
        $expected_store->language = $language;
        $expected_store->catalog_generation_code = $catalog_code;
        $this->data_source->expects($this->once())
            ->method('getCurrentCatalogGenerationCode')
            ->will($this->returnValue($catalog_code));

        $this->codes_data_source->expects($this->never())
            ->method('generateNewCode');

        $this->data_source->expects($this->never())
            ->method('storeCatalogGenerationCode');

        $this->data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));

        $this->data_source->expects($this->once())
            ->method('getStoreAdminData')
            ->will($this->returnValue($admin_email));

        $this->data_source->expects($this->once())
            ->method('getLanguage')
            ->will($this->returnValue($language));

        $this->data_source->expects($this->once())
            ->method('getSiteTitle')
            ->will($this->returnValue($shop_title));

        $this->data_source->expects($this->once())
            ->method('getTimezone')
            ->will($this->returnValue($timezone));
        $store_promise = $this->repository->getStoreInformation();
        $store = $store_promise->wait();
        // Two objects are equal if they are the instances of the same class and have the same properties and values. 
        $this->assertEquals(
            Either::of($expected_store), 
            $store
        );
    }

    public function testGetStoredCatalogCode(){
        $store_url = 'http://ejemplo';
        $this->data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $stored_catalog_code = 'code';
        $this->data_source->expects($this->once())
            ->method('getCurrentCatalogGenerationCode')
            ->with($this->equalTo($store_url))
            ->will($this->returnValue($stored_catalog_code));
        $this->codes_data_source->expects($this->never())
            ->method('generateNewCode');
        $store_promise = $this->repository->getStoreInformation();
        $store = $store_promise->wait();
        $this->assertInstanceOf(Right::class, $store);
        $this->assertEquals(
            $stored_catalog_code, 
            $store->either(
                function($var) { return $var; },
                function($var) { return $var; }
            )->catalog_generation_code
        );
    }

    public function testGetNewCatalogCodeAndStoreIt(){
        $new_catalog_code = 'code';
        $store_url = 'http://ejemplo';
        $this->data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->data_source->expects($this->once())
            ->method('getCurrentCatalogGenerationCode')
            ->with($this->equalTo($store_url))
            ->will($this->throwException(new NoDataException()));
        $this->codes_data_source->expects($this->once())
            ->method('generateNewCode')
            ->will($this->returnValue($new_catalog_code));
        $this->data_source->expects($this->once())
            ->method('storeCatalogGenerationCode')
            ->with($this->equalTo($store_url), $this->equalTo($new_catalog_code));
        $store_promise = $this->repository->getStoreInformation();
        $store = $store_promise->wait();
        $this->assertInstanceOf(Right::class, $store);
        $this->assertEquals(
            $new_catalog_code, 
            $store->either(
                function($var) { return $var; },
                function($var) { return $var; }
            )->catalog_generation_code
        );
    }

    public function testSaveNewCodeFailsAndReturnsFailure() {
        $new_catalog_code = 'code';
        $store_url = 'http://ejemplo';
        $expected_failure = new FailureStoreCatalogGenerationCode;
        $this->data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->data_source->expects($this->once())
            ->method('getCurrentCatalogGenerationCode')
            ->with($this->equalTo($store_url))
            ->will($this->throwException(new NoDataException()));
        $this->codes_data_source->expects($this->once())
            ->method('generateNewCode')
            ->will($this->returnValue($new_catalog_code));
        $this->data_source->expects($this->once())
            ->method('storeCatalogGenerationCode')
            ->with($this->equalTo($store_url), $this->equalTo($new_catalog_code))
            ->will($this->throwException(new CouldNotStoreDataException));
        $failure_promise = $this->repository->getStoreInformation();
        $failure = $failure_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $failure
        );
    }


    public function testNoStoreUrlMustReturnFailure(){
        $expected_failure = new NoStoreUrlFailure;
        $this->data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->throwException(new NoDataException));
        $failure_promise = $this->repository->getStoreInformation();
        $failure = $failure_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $failure
        );
    }

    public function testForceError(){
        $store_url = 'http://ejemplo';
        $expected_failure = new UnknownFailure;
        $this->data_source->expects($this->exactly(2))
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->data_source->expects($this->once())
            ->method('getCurrentCatalogGenerationCode')
            ->with($this->equalTo($store_url))
            ->will($this->throwException(new \Exception()));
        $failure_promise = $this->repository->getStoreInformation();
        $failure = $failure_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $failure
        );
    }

}