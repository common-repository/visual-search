<?php
    use PHPUnit\Framework\TestCase;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;
    use GuzzleHttp\Promise\FulfilledPromise;
    use GuzzleHttp\Promise\RejectedPromise;
    use Impresee\CreativeSearchBar\Data\Repositories\ImpreseeConfigurationRepositoryImpl;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionStatusModel;
    use Impresee\CreativeSearchBar\Data\Models\{ImpreseeSubscriptionDataModel, ImpreseeCreateAccountUrlModel, PluginVersionModel};
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSubscriptionData, ImpreseeSubscriptionStatus};
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeConfigurationStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSearchBarConfiguration,
        EmptyImpreseeSearchBarConfiguration,ImpreseeCreateAccountUrl, PluginVersion};
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeApplication;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByPhoto;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBySketch;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByText;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ClothesMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveStoreCodeException;
    use Impresee\CreativeSearchBar\Core\Errors\NoImpreseeConfigurationDataFailure;
    use Impresee\CreativeSearchBar\Core\Errors\NoImpreseeConfigurationStatusFailure;
    use Impresee\CreativeSearchBar\Core\Errors\FailureCreateOwner;
    use Impresee\CreativeSearchBar\Core\Errors\FailureCreateCatalog;
    use Impresee\CreativeSearchBar\Core\Errors\FailureDataAlreadyExists;
    use Impresee\CreativeSearchBar\Core\Errors\FailureStoreOwnerData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureStoreImpreseeData;
    use Impresee\CreativeSearchBar\Core\Errors\FailedAtRemovingDataFailure;
    use Impresee\CreativeSearchBar\Core\Errors\FailureUpdateIndexationData;
    use Impresee\CreativeSearchBar\Core\Constants\SearchTypes;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;
    use Impresee\CreativeSearchBar\Core\Constants\ExceptionCodes;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Constants\CreateAccountUrlType;


final class ImpreseeConfigurationRepositoryImplTest extends TestCase {
    private $repository;
    private $store_data_source;
    private $local_data_source;
    private $remote_data_source;
    private $email_data_source;
    private $store;
    private $impresee_stored_model;
    private $catalog_market;
    private $owner_model;
    private $catalog_url;

        
    protected function setUp(): void {
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getProjectName')
            ->willReturn('Impresee');
        $this->store_data_source = $this->createMock(StoreLocalDataSource::class);
        $this->local_data_source = $this->createMock(ImpreseeLocalDataSource::class);
        $this->remote_data_source = $this->createMock(ImpreseeRemoteDataSource::class);
        $this->email_data_source = $this->createMock(EmailDataSource::class);
        $this->repository = new ImpreseeConfigurationRepositoryImpl(
            $this->local_data_source,
            $this->remote_data_source,
            $this->email_data_source,
            $this->store_data_source,
            $project_stub
        );
        $this->catalog_url = 'http://example.com/wp-json/impresee/v1/catalog/123456AB';
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
        $this->impresee_stored_model = new ImpreseeConfigurationModel;
        $this->impresee_stored_model->text_app_uuid = '12345';
        $this->impresee_stored_model->sketch_app_uuid = '6789';
        $this->impresee_stored_model->photo_app_uuid = 'abcdre';
        $this->impresee_stored_model->use_clothing = TRUE;
        $this->impresee_stored_model->catalog_processed_once = TRUE;
        $this->impresee_stored_model->catalog_code = 'CATALOG';
        $this->impresee_stored_model->catalog_market = CatalogMarketCodes::APPAREL;
        $this->owner_model = new OwnerModel;
        $this->owner_model->owner_code = 'owner code';
        $this->catalog_market = new ClothesMarket;
        $this->impresee_stored_model->owner_model = $this->owner_model;
        $this->impresee_stored_model->created_data = TRUE;
        $this->impresee_stored_model->send_catalog_to_update_first_time = TRUE;
        $this->impresee_stored_model->last_catalog_update_url = 'https://catalog-processing.com';
    }

    public function testGetValidImpreseeData(){
        $expected_data = new ImpreseeSearchBarConfiguration;
        $expected_catalog = new ImpreseeCatalog;
        $expected_catalog->catalog_code = 'CATALOG';
        $expected_catalog->processed_once = TRUE;
        $expected_catalog->catalog_market = $this->catalog_market;
        $expected_data->owner_code = 'owner code';
        $expected_data->catalog = $expected_catalog;
        $apps = [];
        // TODO: make it so that order doesnt matter
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $expected_data->applications = $apps;
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->returnValue($this->impresee_stored_model));
        $this->store_data_source->expects($this->never())
                ->method($this->anything());
        $this->remote_data_source->expects($this->never())
                ->method($this->anything());
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $data_promise = $this->repository->getImpreseeConfiguration($this->store);
        $data = $data_promise->wait();
        $this->assertEquals(
            Either::of($expected_data), 
            $data
        );
    }

    public function testGetImpreseeDataNoStoredData(){
        $expected_error = new NoImpreseeConfigurationDataFailure;
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->store_data_source->expects($this->once())
                ->method('getStoreUrl')
                ->will($this->returnValue('http://example.com'));
        $this->store_data_source->expects($this->once())
                ->method('finishedOnboarding')
                ->will($this->returnValue(TRUE));
        $this->email_data_source->expects($this->once())
                ->method('sendErrorEmail');
        $data_promise = $this->repository->getImpreseeConfiguration($this->store);
        $data_promise->then(
            function($impresee_data) use ($expected_error){
                $this->assertEquals(
                    new Left($expected_error), 
                    $impresee_data
                );
            }
        );
    }

    public function testGetImpreseeDataGenericError(){
        $expected_error = new UnknownFailure;
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new \Exception()));
        $this->store_data_source->expects($this->once())
                ->method('getStoreUrl')
                ->will($this->returnValue('http://example.com'));
        $this->store_data_source->expects($this->never())
                ->method('finishedOnboarding')
                ->will($this->returnValue(TRUE));
        $this->email_data_source->expects($this->once())
                ->method('sendErrorEmail');
        $data_promise = $this->repository->getImpreseeConfiguration($this->store);
        $data_promise->then(
            function($impresee_data) use ($expected_error){
                $this->assertEquals(
                    new Left($expected_error), 
                    $impresee_data
                );
            }
        );
    }

    private function verifyNoStoredData(){
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredOwner')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
    }

    public function testRegisterImpreseeDataSuccessfully(){
        $this->verifyNoStoredData();
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl');
        $this->store_data_source->expects($this->once())
            ->method('getCreateCatalogUrl')
            ->will($this->returnValue($this->catalog_url));
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise($this->owner_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerLocalOwner')
            ->with($this->equalTo($this->store), $this->equalTo($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('registerCatalog')
            ->with($this->equalTo($this->owner_model), $this->equalTo($this->catalog_market), $this->equalTo($this->store), $this->equalTo($this->catalog_url))
            ->will($this->returnValue(new FulfilledPromise($this->impresee_stored_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerImpreseeLocalData')
            ->with($this->equalTo($this->store), $this->equalTo($this->impresee_stored_model));
        $this->local_data_source->expects($this->once())
            ->method('setCreatedImpreseeData')
            ->with($this->equalTo($this->store));
        $promise = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $either = $promise->wait();

    }

    public function testRegisterImpreseeDataFailCreateOwner(){
        $expected_error = new FailureCreateOwner;
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredOwner')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new RejectedPromise(new \Exception(ExceptionCodes::CREATE_OWNER_ERROR))));
        $this->remote_data_source->expects($this->never())
            ->method('registerCatalog');
        $this->local_data_source->expects($this->never())
            ->method('registerImpreseeLocalData');
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($this->store->url));
        $this->email_data_source->expects($this->once())
            ->method('sendErrorEmail');
        $promise_error = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $error = $promise_error->wait();
        $this->assertEquals(
            new Left($expected_error),
            $error
        );
    }

    public function testRegisterImpreseeDataFailRegisterCatalog(){
        $expected_error = new FailureCreateCatalog;
        $this->verifyNoStoredData();
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise($this->owner_model)));
        $this->store_data_source->expects($this->once())
            ->method('getCreateCatalogUrl')
            ->will($this->returnValue($this->catalog_url));
        $this->remote_data_source->expects($this->once())
            ->method('registerCatalog')
            ->with($this->equalTo($this->owner_model), $this->equalTo($this->catalog_market), $this->equalTo($this->store), $this->equalTo($this->catalog_url))
            ->will($this->returnValue(new RejectedPromise(
                new \Exception(ExceptionCodes::CREATE_CATALOG_ERROR)))
            );
        $this->local_data_source->expects($this->never())
            ->method('registerImpreseeLocalData');
        $this->local_data_source->expects($this->never())
            ->method('setCreatedImpreseeData');
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($this->store->url));
        $this->email_data_source->expects($this->once())
            ->method('sendErrorEmail');
        $promise_error = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $error = $promise_error->wait();
        $this->assertEquals(
            new Left($expected_error),
            $error
        );
    }

    public function testTryToRegisterDataWithExistingData(){
        $expected_data = new ImpreseeSearchBarConfiguration;
        $expected_catalog = new ImpreseeCatalog;
        $expected_catalog->catalog_code = 'CATALOG';
        $expected_catalog->processed_once = TRUE;
        $expected_catalog->catalog_market = $this->catalog_market;
        $expected_data->owner_code = 'owner code';
        $expected_data->catalog = $expected_catalog;
        $apps = [];
        // TODO: make it so that order doesnt matter
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $expected_data->applications = $apps;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($this->store->url));
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->returnValue($this->impresee_stored_model));
        $promise = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $data = $promise->wait();
        $result = $data->either(
            function($var){ return $var;},
            function($impresee_data) { return $impresee_data; }
        );
        $this->assertEquals(
            $expected_data,
            $result
        );
    }

    public function testTryToRegisterDataWithExistingOwner(){
        $this->store_data_source->expects($this->once())
            ->method('getCreateCatalogUrl')
            ->will($this->returnValue($this->catalog_url));
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredOwner')
                ->with($this->equalTo($this->store))
                ->will($this->returnValue($this->owner_model));
                
        $this->remote_data_source->expects($this->never())
            ->method('registerOwner')
            ->with($this->equalTo($this->store));
        $this->local_data_source->expects($this->never())
                ->method('registerLocalOwner')
                ->with($this->equalTo($this->owner_model));

        $this->remote_data_source->expects($this->once())
            ->method('registerCatalog')
            ->with($this->equalTo($this->owner_model), $this->equalTo($this->catalog_market), $this->equalTo($this->store), $this->equalTo($this->catalog_url))
            ->will($this->returnValue(new FulfilledPromise($this->impresee_stored_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerImpreseeLocalData')
            ->with($this->equalTo($this->store), $this->equalTo($this->impresee_stored_model));
        $promise = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $promise->wait();
    }

    public function testRegisterFailsAtStoringOwnerDataShouldReturnFailure(){
        $expected_failure = new FailureStoreOwnerData;
        $this->verifyNoStoredData();
        $this->store_data_source->expects($this->exactly(2))
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise($this->owner_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerLocalOwner')
            ->with($this->equalTo($this->store), $this->equalTo($this->owner_model))
            ->will($this->throwException(new CouldNotStoreDataException));

        $this->remote_data_source->expects($this->never())
            ->method('registerCatalog')
            ->with($this->equalTo($this->owner_model), $this->equalTo($this->catalog_market), $this->equalTo($this->store), $this->equalTo($this->catalog_url));
        $this->local_data_source->expects($this->never())
            ->method('registerImpreseeLocalData')
            ->with($this->equalTo($this->store), $this->equalTo($this->impresee_stored_model));
        $promise = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }

    public function testRegisterFailsAtStoringImpreseeDataShouldReturnFailure(){
        $expected_failure = new FailureStoreImpreseeData;
        $this->verifyNoStoredData();
        $this->store_data_source->expects($this->once())
            ->method('getCreateCatalogUrl')
            ->will($this->returnValue($this->catalog_url));
        $this->store_data_source->expects($this->exactly(2))
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise($this->owner_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerLocalOwner')
            ->with($this->equalTo($this->store), $this->equalTo($this->owner_model));

        $this->remote_data_source->expects($this->once())
            ->method('registerCatalog')
            ->with($this->equalTo($this->owner_model), $this->equalTo($this->catalog_market), $this->equalTo($this->store), $this->equalTo($this->catalog_url))
            ->will($this->returnValue(new FulfilledPromise($this->impresee_stored_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerImpreseeLocalData')
            ->with($this->equalTo($this->store), $this->equalTo($this->impresee_stored_model))
            ->will($this->throwException(new CouldNotStoreDataException));
        $promise = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }

    public function testRegisterFailsAtStoringImpreseeDataGenericError(){
        $expected_failure = new UnknownFailure;
         $this->local_data_source->expects($this->once())
                ->method('getRegisteredImpreseeData')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new \Exception()));
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $promise = $this->repository->registerImpreseeConfiguration($this->store, $this->catalog_market);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }

    public function testRemoveDataSuccessfully(){
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner code';
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($owner_model));
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->remote_data_source->expects($this->once())
            ->method('removeData')
            ->with($this->equalTo($owner_model))
            ->will($this->returnValue(new FulfilledPromise(NULL)));
        $this->local_data_source->expects($this->once())
            ->method('removeAllLocalData')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->once())
            ->method('removeStoreData')
            ->with($this->equalTo($this->store->url));
        $promise = $this->repository->removeAllData($this->store);
        $either = $promise->wait();

    }

    public function testRemoveEmptyDataSuccessfully(){
        $configuration = new EmptyImpreseeSearchBarConfiguration;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $this->local_data_source->expects($this->once())
            ->method('removeAllLocalData')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->once())
            ->method('removeStoreData')
            ->with($this->equalTo($this->store->url));
        $promise = $this->repository->removeAllData($this->store);
        $either = $promise->wait();
    }

    public function testRemoveLocalFailure(){
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner code';
        $expected_failure = new FailedAtRemovingDataFailure;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('removeData')
            ->with($this->equalTo($owner_model))
            ->will($this->returnValue(new FulfilledPromise(NULL)));
        $this->local_data_source->expects($this->once())
            ->method('removeAllLocalData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new CouldNotRemoveDataException));
        $this->store_data_source->expects($this->once())
            ->method('removeStoreData')
            ->with($this->equalTo($this->store->url));
        $promise = $this->repository->removeAllData($this->store);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }

    public function testRemoveStoreCodeFailure(){
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner code';
        $expected_failure = new FailedAtRemovingDataFailure;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('removeData')
            ->with($this->equalTo($owner_model))
            ->will($this->returnValue(new FulfilledPromise(NULL)));
        $this->local_data_source->expects($this->once())
            ->method('removeAllLocalData')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->once())
            ->method('removeStoreData')
            ->with($this->equalTo($this->store->url))
            ->will($this->throwException(new CouldNotRemoveStoreCodeException));
        $promise = $this->repository->removeAllData($this->store);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }


    public function testRemoveRemoteFailure(){
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner code';
        $expected_failure = new FailedAtRemovingDataFailure;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('removeData')
            ->with($this->equalTo($owner_model))
            ->will($this->returnValue(new RejectedPromise(new Exception)));
        $this->local_data_source->expects($this->never())
            ->method('removeAllLocalData')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->never())
            ->method('removeStoreData')
            ->with($this->equalTo($this->store->url));
        $promise = $this->repository->removeAllData($this->store);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }

    public function testRemoveDataGenericError(){
        $expected_failure = new UnknownFailure;
        $owner_model = new OwnerModel;
        $owner_model->owner_code = 'owner code';
        $expected_failure = new FailedAtRemovingDataFailure;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('removeData')
            ->with($this->equalTo($owner_model))
            ->will($this->returnValue(new RejectedPromise(new Exception)));
        
        $promise = $this->repository->removeAllData($this->store);
        $either = $promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $either
        );
    }

    public function testGetImpreseeConfigurationStatus(){
        $expected_results = new ImpreseeConfigurationStatus;
        $expected_results->created_data = TRUE;
        $expected_results->sent_catalog_to_update = TRUE;
        $expected_results->last_catalog_update_url = 'https://catalog-processing.com';
        $expected_results->catalog_processed_once = TRUE;
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredImpreseeData')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->impresee_stored_model));
        $results_promise = $this->repository->getConfigurationStatus($this->store);
        $result_either = $results_promise->wait();
        $this->assertEquals(
            Either::of($expected_results),
            $result_either
        );
    }

    public function testGetImpreseeConfigurationStatusFails(){
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredImpreseeData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $results_promise = $this->repository->getConfigurationStatus($this->store);
        $result_either = $results_promise->wait();
        $this->assertEquals(
            new Left(new NoImpreseeConfigurationStatusFailure),
            $result_either
        );
    }

    public function testGetImpreseeConfigurationStatusGenericError(){
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredImpreseeData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Exception));
        $results_promise = $this->repository->getConfigurationStatus($this->store);
        $result_either = $results_promise->wait();
        $this->assertEquals(
            new Left(new UnknownFailure),
            $result_either
        );
    }

    public function testGetIndexationConfigurationWhenThereIsStoredData(){
        $returned_model = new IndexationConfigurationModel;
        $returned_model->only_products_with_price = TRUE;
        $returned_model->only_products_in_stock = FALSE;
        $expected_indexation_config = new CatalogIndexationConfiguration;
        $expected_indexation_config->show_products_with_no_price = FALSE;
        $expected_indexation_config->index_only_in_stock_products = FALSE;
        $this->local_data_source->expects($this->once())
            ->method('getIndexationConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($returned_model));
        $results_promise = $this->repository->getIndexationConfiguration($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            Either::of($expected_indexation_config),
            $results_either
        );
    }

    public function testGetIndexationConfigurationReturnDefaultWhenNoDataIsStored(){
        $expected_indexation_config = new CatalogIndexationConfiguration;
        $expected_indexation_config->show_products_with_no_price = TRUE;
        $expected_indexation_config->index_only_in_stock_products = FALSE;
        $this->local_data_source->expects($this->once())
            ->method('getIndexationConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $results_promise = $this->repository->getIndexationConfiguration($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            Either::of($expected_indexation_config),
            $results_either
        );
    }

    public function testGetIndexationConfigurationGenericError(){
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getIndexationConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Exception));
        $results_promise = $this->repository->getIndexationConfiguration($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            new Left(new UnknownFailure),
            $results_either
        );
    }

    public function testUpdateConfigurationSuccessfully(){
        $configuration_to_save = new CatalogIndexationConfiguration;
        $configuration_to_save->show_products_with_no_price = TRUE;
        $configuration_to_save->index_only_in_stock_products = FALSE;
        $configuration_model_to_save = new IndexationConfigurationModel;
        $configuration_model_to_save->only_products_with_price = FALSE;
        $configuration_model_to_save->only_products_in_stock = FALSE;
        $this->local_data_source->expects($this->once())
            ->method('updateIndexationConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($configuration_model_to_save))
            ->will($this->returnValue($configuration_model_to_save));
        $results_promise = $this->repository->updateIndexationConfiguration($this->store, $configuration_to_save);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($configuration_to_save)
        ); 
    }

    public function testUpdateConfigurationFails(){
        $configuration_to_save = new CatalogIndexationConfiguration;
        $configuration_to_save->show_products_with_no_price = TRUE;
        $configuration_to_save->index_only_in_stock_products = FALSE;
        $configuration_model_to_save = new IndexationConfigurationModel;
        $configuration_model_to_save->only_products_with_price = FALSE;
        $configuration_model_to_save->only_products_in_stock = FALSE;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('updateIndexationConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($configuration_model_to_save))
            ->will($this->throwException(new CouldNotStoreDataException));
        $results_promise = $this->repository->updateIndexationConfiguration($this->store, $configuration_to_save);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new FailureUpdateIndexationData)
        ); 
    }

    public function testUpdateConfigurationGenericError(){
        $configuration_to_save = new CatalogIndexationConfiguration;
        $configuration_to_save->show_products_with_no_price = TRUE;
        $configuration_to_save->index_only_in_stock_products = FALSE;
        $configuration_model_to_save = new IndexationConfigurationModel;
        $configuration_model_to_save->only_products_with_price = FALSE;
        $configuration_model_to_save->only_products_in_stock = FALSE;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('updateIndexationConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($configuration_model_to_save))
            ->will($this->throwException(new \Error));
        $results_promise = $this->repository->updateIndexationConfiguration($this->store, $configuration_to_save);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        ); 
    }
    
    public function testGetSubscriptionStatusSuccess(){
        $model = new ImpreseeSubscriptionStatusModel;
        $model->suspended = FALSE;
        $expected_result = new ImpreseeSubscriptionStatus;
        $expected_result->suspended = FALSE;
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getLocalSubscriptionStatusData')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($model));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->getStoredSubscriptionStatus($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($expected_result)
        ); 
    }

    public function testGetSubscriptionStatusFails(){
        $model = new ImpreseeSubscriptionStatusModel;
        $model->suspended = FALSE;
        $expected_result = new ImpreseeSubscriptionStatus;
        $expected_result->suspended = FALSE;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getLocalSubscriptionStatusData')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $this->email_data_source->expects($this->once())
                ->method($this->anything());
        $results_promise = $this->repository->getStoredSubscriptionStatus($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        ); 
    }

    public function testUpdateSubscriptionStatusSuccess(){
        $model = new ImpreseeSubscriptionStatusModel;
        $model->suspended = FALSE;
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->at(0))
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('isSuspended')
            ->with($this->equalTo($this->owner_model))
            ->will($this->returnValue(new FulfilledPromise($model)));
        $this->local_data_source->expects($this->at(1))
            ->method('updateLocalSubscriptionStatusData')
            ->with($this->equalTo($this->store), $this->equalTo($model))
            ->will($this->returnValue(Either::of(NULL)));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->updateStoredSubscriptionStatus($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of(NULL)
        ); 
    }

    public function testUpdateSubscriptionStatusFails(){
        $model = new ImpreseeSubscriptionStatusModel;
        $model->suspended = FALSE;
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->at(0))
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('isSuspended')
            ->with($this->equalTo($this->owner_model))
            ->will($this->throwException(new \Error));
        $this->email_data_source->expects($this->once())
                ->method($this->anything());
        $results_promise = $this->repository->updateStoredSubscriptionStatus($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        );
    }

    public function testGetSubscriptionDataSuccess(){
        $model = new ImpreseeSubscriptionDataModel;
        $model->trial_days_left = 10;
        $model->is_subscribed = FALSE;
        $model->plan_name = '';
        $model->plan_price = '';
        $expected_data = new ImpreseeSubscriptionData;
        $expected_data->trial_days_left = 10;
        $expected_data->is_subscribed = FALSE;
        $expected_data->plan_name = '';
        $expected_data->plan_price = '';
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('obtainSubscriptionData')
            ->with($this->equalTo($this->owner_model))
            ->will($this->returnValue(new FulfilledPromise($model)));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->getSubscriptionData($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($expected_data)
        ); 

    }

    public function testGetSubscriptionDataFails(){
        $model = new ImpreseeSubscriptionDataModel;
        $model->trial_days_left = 10;
        $model->is_subscribed = FALSE;
        $model->plan_name = '';
        $model->plan_price = '';
        $expected_data = new ImpreseeSubscriptionData;
        $expected_data->trial_days_left = 10;
        $expected_data->is_subscribed = FALSE;
        $expected_data->plan_name = '';
        $expected_data->plan_price = '';
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('obtainSubscriptionData')
            ->with($this->equalTo($this->owner_model))
            ->will($this->throwException(new \Error));
        $this->email_data_source->expects($this->once())
                ->method($this->anything());
        $results_promise = $this->repository->getSubscriptionData($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        ); 
    }

    public function testGetCreateAccountUrlDataSuccess(){
        $model = new ImpreseeCreateAccountUrlModel;
        $model->url = 'https://example.com';
        $expected_data = new ImpreseeCreateAccountUrl;
        $expected_data->url = 'https://example.com';
        $expected_type = "SUBSCRIBE";
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('getCreateAccountUrl')
            ->with($this->equalTo($this->owner_model), $this->equalTo($expected_type))
            ->will($this->returnValue(new FulfilledPromise($model)));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->getCreateAccountUrl($this->store, CreateAccountUrlType::SUBSCRIBE);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($expected_data)
        ); 

    }

    public function testGetCreateAccountUrlDataFails(){
        $model = new ImpreseeCreateAccountUrlModel;
        $model->url = 'https://example.com';
        $expected_data = new ImpreseeCreateAccountUrl;
        $expected_data->url = 'https://example.com';
        $expected_type = "SUBSCRIBE";
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getRegisteredOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->owner_model));
        $this->remote_data_source->expects($this->once())
            ->method('getCreateAccountUrl')
            ->with($this->equalTo($this->owner_model), $this->equalTo($expected_type))
            ->will($this->throwException(new \Error));
        $this->email_data_source->expects($this->once())
                ->method($this->anything());
        $results_promise = $this->repository->getCreateAccountUrl($this->store, CreateAccountUrlType::SUBSCRIBE);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        ); 
    }

    public function testUpdateStoredPluginVersionSuccess(){
        $model = new PluginVersionModel;
        $model->version = 'version';
        $expected_data = new PluginVersion;
        $expected_data->version = 'version';
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('updateStoredPluginVersion')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($model));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->updateStoredPluginVersion($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($expected_data)
        ); 

    }

    public function testUpdateStoredPluginVersionFails(){
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('updateStoredPluginVersion')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $this->email_data_source->expects($this->once())
                ->method($this->anything());
        $results_promise = $this->repository->updateStoredPluginVersion($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        ); 
    }

    public function testGetStoredPluginVersionSuccess(){
        $model = new PluginVersionModel;
        $model->version = 'version';
        $expected_data = new PluginVersion;
        $expected_data->version = 'version';
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getStoredPluginVersion')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($model));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->getStoredPluginVersion($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($expected_data)
        ); 

    }

    public function testGetStoredPluginVersionNoData(){
        $model = new PluginVersionModel;
        $model->version = 'version';
        $expected_data = new PluginVersion;
        $expected_data->version = 'version';
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->at(0))
            ->method('getStoredPluginVersion')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $this->local_data_source->expects($this->at(1))
            ->method('updateStoredPluginVersion')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($model));
        $this->email_data_source->expects($this->never())
                ->method($this->anything());
        $results_promise = $this->repository->getStoredPluginVersion($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            Either::of($expected_data)
        ); 

    }

    public function testGetStoredPluginVersionFails(){
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue('http://example.com'));
        $this->local_data_source->expects($this->once())
            ->method('getStoredPluginVersion')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $this->email_data_source->expects($this->once())
                ->method($this->anything());
        $results_promise = $this->repository->getStoredPluginVersion($this->store);
        $results_either = $results_promise->wait();
        $this->assertEquals(
            $results_either,
            new Left(new UnknownFailure)
        ); 
    }

    public function testRegisterOwnerSuccessfully(){
        $this->store_data_source->expects($this->never())
            ->method('getStoreUrl');
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredOwner')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise($this->owner_model)));
        $this->local_data_source->expects($this->once())
            ->method('registerLocalOwner')
            ->with($this->equalTo($this->store), $this->equalTo($this->owner_model));
        $promise = $this->repository->registerOwner($this->store);
        $either = $promise->wait();

    }

    public function testRegisterOwnerFail(){
        $expected_error = new FailureCreateOwner;
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredOwner')
                ->with($this->equalTo($this->store))
                ->will($this->throwException(new NoDataException()));
        $this->remote_data_source->expects($this->once())
            ->method('registerOwner')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new RejectedPromise(new \Exception(ExceptionCodes::CREATE_OWNER_ERROR))));
        $promise_error = $this->repository->registerOwner($this->store);
        $error = $promise_error->wait();
        $this->assertEquals(
            new Left($expected_error),
            $error
        );
    }

    public function testRegisterOwnerAlreadyExists(){
        $this->local_data_source->expects($this->once())
                ->method('getRegisteredOwner')
                ->with($this->equalTo($this->store))
                ->will($this->returnValue($this->owner_model));              
        $this->remote_data_source->expects($this->never())
            ->method('registerOwner')
            ->with($this->equalTo($this->store));
        $this->local_data_source->expects($this->never())
                ->method('registerLocalOwner')
                ->with($this->equalTo($this->owner_model));

        $promise = $this->repository->registerOwner($this->store);
        $promise->wait();
    }
}