<?php 
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use GuzzleHttp\Promise\RejectedPromise;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSourceImpl;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\UpdateCatalogModel;
    use Impresee\CreativeSearchBar\Data\Models\CatalogStatusModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionStatusModel;
    use Impresee\CreativeSearchBar\Data\Models\{ImpreseeSubscriptionDataModel, ImpreseeCreateAccountUrlModel};
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ClothesMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeApplication;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByText;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBySketch;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByPhoto;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Core\Constants\{Services, Project, CatalogMarketCodes};
    use Impresee\CreativeSearchBar\Core\Errors\ConnectionException;
    use Impresee\CreativeSearchBar\Core\Errors\ImpreseeServerException;
    use Impresee\CreativeSearchBar\Core\Utils\LogHandler;
    use ImpreseeGuzzleHttp\Client;
    use ImpreseeGuzzleHttp\Psr7\Response;
    use ImpreseeGuzzleHttp\Exception\RequestException;
    use ImpreseeGuzzleHttp\Psr7\Request;
    use Impresee\CreativeSearchBar\Core\Constants\CreateAccountUrlType;


final class ImpreseeRemoteDataSourceImplTest extends TestCase {
    private $client;
    private $datasource;
    private $store;
    private $create_owner_array;
    private $catalog_market;
    private $owner_model;
    private $create_catalog_array;
    private $catalog;
    private $configuration;
    private $catalog_url;
    private $log_handler;
    private $catalog_format = 'impresee_2';
    private $url_create_owner = 'url1';
    private $url_create_catalog = 'url2';
    private $url_update_catalog = 'url3';
    private $url_remove_data = 'url4';
    private $url_catalog_status = 'url5';
    private $url_subscription_data = 'url6';
    private $url_subscription_status = 'url7';
    private $url_get_account_url = 'url8';
    private $url_notify_change_status = 'url9';

    protected function setUp(): void{
        $services_stub = $this->createMock(Services::class);
        $services_stub->method('getCreateOwnerUrl')
            ->willreturn($this->url_create_owner);
        $services_stub->method('getCreateCatalogUrl')
            ->willreturn($this->url_create_catalog);
        $services_stub->method('getUpdateCatalogUrl')
            ->willreturn($this->url_update_catalog);
        $services_stub->method('getRemoveDataUrl')
            ->willreturn($this->url_remove_data);
        $services_stub->method('getCatalogStatusUrl')
            ->willreturn($this->url_catalog_status);
        $services_stub->method('getSubscriptionDataUrl')
            ->willreturn($this->url_subscription_data);
        $services_stub->method('getSubscriptionStatusUrl')
            ->willreturn($this->url_subscription_status);
        $services_stub->method('getCreateAccountUrl')
            ->willreturn($this->url_get_account_url);
        $services_stub->method('getNotifyChangePluginStatusUrl')
            ->willreturn($this->url_notify_change_status);
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getCatalogFormat')
            ->willReturn($this->catalog_format);
        $project_stub->method('getTrialDays')
            ->willReturn(20);
        $this->client = $this->createMock(Client::class);
        $this->log_handler = $this->createMock(LogHandler::class);
        $this->datasource = new ImpreseeRemoteDataSourceImpl($this->client, $this->log_handler, 
            $project_stub, $services_stub);
        $this->catalog_url = 'http://example.com/wp-json/impresee/v1/catalog/123456AB';
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
        $this->create_owner_array = array(
            'store_url'     => $this->store->url,
            'store_name'    => $this->store->getStoreName(),
            'user_name'     => $this->store->shop_title,
            'user_email'    => $this->store->shop_email,
            'locale_code'   => $this->store->language,
            'timezone_code' => $this->store->timezone,
            'trial_days'    => 20
        );
        $this->catalog_market = new ClothesMarket;
        $this->owner_model = new OwnerModel;
        $this->owner_model->owner_code = 'owner_code';
        $this->create_catalog_array = array(
            'catalog_market'       => $this->catalog_market->toString(),
            'catalog_format'       => $this->catalog_format,
            'catalog_url_download' => $this->catalog_url
        );
        $this->catalog = new ImpreseeCatalog;
        $this->catalog->catalog_code = 'CATALOG';
        $this->catalog->processed_once = TRUE;
        $this->catalog->catalog_market = $this->catalog_market;
        $this->configuration = new ImpreseeSearchBarConfiguration;
        $this->configuration->owner_code = $this->owner_model->owner_code;
        $this->configuration->catalog = $this->catalog;
        $apps = [];
        // TODO: make it so that order doesnt matter
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $this->configuration->applications = $apps;
    }

    /**
    * @group registerOwner
    */
    public function testCreateOwnerSuccessfully(){
        $expected_owner_code = 'owner_code';
        $expected_owner_model = new OwnerModel;
        $expected_owner_model->owner_code = $expected_owner_code;
        $expected_response = new Response(
            200, 
            [], 
            json_encode(['status' => 0, 'owner_uuid' => $expected_owner_code])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_create_owner),
                $this->equalTo(['json' => $this->create_owner_array])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $owner_promise = $this->datasource->registerOwner($this->store);
        $owner = $owner_promise->wait();
        $this->assertEquals(
            $expected_owner_model,
            $owner
        );
    }

    /**
    * @group registerOwner
    */
    public function testCreateOwnerWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_create_owner),
                $this->equalTo(['json' => $this->create_owner_array])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_create_owner)
                    )
                )
            ));
        $owner_promise = $this->datasource->registerOwner($this->store);
        $this->expectException(ConnectionException::class);
        $owner_promise->wait();
    }

    /**
    * @group registerOwner
    */
    public function testCreateOwnerWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_create_owner),
                $this->equalTo(['json' => $this->create_owner_array])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $owner_promise = $this->datasource->registerOwner($this->store);
        $this->expectException(ImpreseeServerException::class);
        $owner_promise->wait();
    }



    /**
    * @group registerCatalog
    */
    public function testCreateCatalogSuccessfully(){
        $expected_model = new ImpreseeConfigurationModel;
        $expected_catalog_code = 'catalog';
        $expected_photo_uuid = 'photo_uuid';
        $expected_sketch_uuid = 'sketch uuid';
        $expected_text_uuid = 'text uuid';
        $expected_model->text_app_uuid = $expected_text_uuid;
        $expected_model->sketch_app_uuid = $expected_sketch_uuid;
        $expected_model->photo_app_uuid = $expected_photo_uuid;
        $expected_model->owner_model = $this->owner_model;
        $expected_model->use_clothing = TRUE;
        $expected_model->catalog_processed_once = FALSE;
        $expected_model->catalog_code = 'catalog';
        $expected_model->catalog_market = CatalogMarketCodes::APPAREL;
        $expected_model->created_data = TRUE;
        $expected_model->send_catalog_to_update_first_time = FALSE;
        $expected_model->last_catalog_update_url = '';
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0,
                'catalog_code' => $expected_catalog_code,
                'application_uuid_image' => $expected_photo_uuid,
                'application_uuid_sketch' => $expected_sketch_uuid,
                'application_uuid_text' => $expected_text_uuid,
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_create_catalog.$this->owner_model->owner_code),
                $this->equalTo(['json' => $this->create_catalog_array])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $impresee_data_promise = $this->datasource->registerCatalog(
            $this->owner_model,
            $this->catalog_market,
            $this->store,
            $this->catalog_url
        );
        $impresee_data = $impresee_data_promise->wait();
        $this->assertEquals(
            $expected_model,
            $impresee_data
        );
    }

    /**
    * @group registerCatalog
    */
    public function testCreateCatalogWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_create_catalog.$this->owner_model->owner_code),
                $this->equalTo(['json' => $this->create_catalog_array])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_create_catalog.$this->owner_model->owner_code)
                    )
                )
            ));
        $impresee_data_promise = $this->datasource->registerCatalog($this->owner_model, $this->catalog_market, $this->store, $this->catalog_url);
        $this->expectException(ConnectionException::class);
        $impresee_data_promise->wait();
    }

    /**
    * @group registerCatalog
    */
    public function testCreateCatalogWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_create_catalog.$this->owner_model->owner_code),
                $this->equalTo(['json' => $this->create_catalog_array])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $impresee_data_promise = $this->datasource->registerCatalog($this->owner_model, $this->catalog_market, $this->store, $this->catalog_url);
        $this->expectException(ImpreseeServerException::class);
        $impresee_data_promise->wait();
    }


    /**
    * @group updateCatalog
    */
    public function testUpdateCatalogSuccessfully(){
        $update_url = 'https://example.com';
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0,
                'catalog_process_status_url' => $update_url
            ])
        );
        $expected_model = new UpdateCatalogModel;
        $expected_model->update_url = $update_url;
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_update_catalog.$this->owner_model->owner_code.'/'.$this->catalog->catalog_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->updateCatalog(
            $this->catalog,
            $this->owner_model->owner_code);
        $update_data = $update_data_promise->wait();
        $this->assertEquals(
            $expected_model,
            $update_data
        );
    }

    /**
    * @group updateCatalog
    */
    public function testUpdateCatalogWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_update_catalog.$this->owner_model->owner_code.'/'.$this->catalog->catalog_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_update_catalog.$this->owner_model->owner_code.$this->catalog->catalog_code)
                    )
                )
            ));
        $update_data_promise = $this->datasource->updateCatalog($this->catalog, $this->owner_model->owner_code);
        $this->expectException(ConnectionException::class);
        $update_data_promise->wait();
    }

    /**
    * @group updateCatalog
    */
    public function testUpdateCatalogWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_update_catalog.$this->owner_model->owner_code.'/'.$this->catalog->catalog_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->updateCatalog($this->catalog, $this->owner_model->owner_code);
        $this->expectException(ImpreseeServerException::class);
        $update_data_promise->wait();
    }


    /**
    * @group getCatalogState
    */
    public function testGetCatalogStateSuccessfully(){
        $update_url = 'https://example.com';
        $expected_status = new CatalogStatusModel;
        $expected_status->update_url = $update_url;
        $expected_status->processing = TRUE;
        $expected_status->last_successful_update = 134534545;
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0,
                'catalog_is_processing'      => TRUE,
                'catalog_process_status_url' => $update_url,
                'catalog_last_successful_update_timestamp' => 134534545
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_catalog_status.$this->owner_model->owner_code.'/'.$this->catalog->catalog_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $status_promise = $this->datasource->getCatalogState(
            $this->catalog,
            $this->owner_model->owner_code);
        $status_data = $status_promise->wait();
        $this->assertEquals(
            $expected_status,
            $status_data
        );
    }

    /**
    * @group getCatalogState
    */
    public function testGetCatalogStateWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_catalog_status.$this->owner_model->owner_code.'/'.$this->catalog->catalog_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_catalog_status.$this->owner_model->owner_code.$this->catalog->catalog_code)
                    )
                )
            ));
        $update_data_promise = $this->datasource->getCatalogState($this->catalog, $this->owner_model->owner_code);
        $this->expectException(ConnectionException::class);
        $update_data_promise->wait();
    }

    /**
    * @group getCatalogState
    */
    public function testGetCatalogStateWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_catalog_status.$this->owner_model->owner_code.'/'.$this->catalog->catalog_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->getCatalogState($this->catalog, $this->owner_model->owner_code);
        $this->expectException(ImpreseeServerException::class);
        $update_data_promise->wait();
    }


        /**
    * @group removeData
    */
    public function testRemoveDataSuccessfully(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_remove_data.$this->configuration->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $remove_data_promise = $this->datasource->removeData(
            $this->owner_model);
        $remove_data = $remove_data_promise->wait();
    }

    /**
    * @group removeData
    */
    public function testRemoveDataWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_remove_data.$this->configuration->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_remove_data.$this->owner_model->owner_code)
                    )
                )
            ));
        $remove_data_promise = $this->datasource->removeData(
            $this->owner_model);
        $this->expectException(ConnectionException::class);
        $remove_data_promise->wait();
    }

    /**
    * @group removeData
    */
    public function testRemoveDataWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_remove_data.$this->configuration->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $remove_data_promise = $this->datasource->removeData(
            $this->owner_model);
        $this->expectException(ImpreseeServerException::class);
        $remove_data_promise->wait();
    }


    /**
    * @group obtainSubscriptionData
    */
    public function testObtainSubscriptionDataSuccessfully(){
        $update_url = 'https://example.com';
        $expected_status = new ImpreseeSubscriptionDataModel;
        $expected_status->trial_days_left = 20;
        $expected_status->is_subscribed = FALSE;
        $expected_status->plan_name = '';
        $expected_status->plan_price = '';
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0,
                'days_of_trial_left' => 20,
                'is_subscribed' => FALSE,
                'plan_name' => '',
                'plan_price' => ''
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_subscription_data.'/'.$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $status_promise = $this->datasource->obtainSubscriptionData(
            $this->owner_model);
        $status_data = $status_promise->wait();
        $this->assertEquals(
            $status_data,
            $expected_status
        );
    }

    /**
    * @group obtainSubscriptionData
    */
    public function testObtainSubscriptionDataWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_subscription_data.'/'.$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('GET', $this->url_subscription_data.'/'.$this->owner_model->owner_code)
                    )
                )
            ));
        $update_data_promise = $this->datasource->obtainSubscriptionData($this->owner_model);
        $this->expectException(ConnectionException::class);
        $update_data_promise->wait();
    }

    /**
    * @group obtainSubscriptionData
    */
    public function testObtainSubscriptionDataWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_subscription_data.'/'.$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->obtainSubscriptionData($this->owner_model);
        $this->expectException(ImpreseeServerException::class);
        $update_data_promise->wait();
    }

    /**
    * @group isSuspended
    */
    public function testIsSuspendedSuccessfully(){
        $expected_status = new ImpreseeSubscriptionStatusModel;
        $expected_status->suspended = FALSE;
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0,
                'isSuspended' => FALSE
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_subscription_status.'/'.$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $status_promise = $this->datasource->isSuspended(
            $this->owner_model);
        $status_data = $status_promise->wait();
        $this->assertEquals(
            $status_data,
            $expected_status
        );
    }

    /**
    * @group isSuspended
    */
    public function testIsSuspendedWithConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_subscription_status.'/'.$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('GET', $this->url_subscription_status.'/'.$this->owner_model->owner_code)
                    )
                )
            ));
        $update_data_promise = $this->datasource->isSuspended($this->owner_model);
        $this->expectException(ConnectionException::class);
        $update_data_promise->wait();
    }

    /**
    * @group isSuspended
    */
    public function testIsSuspendedWithImpreseeServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo($this->url_subscription_status.'/'.$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->isSuspended($this->owner_model);
        $this->expectException(ImpreseeServerException::class);
        $update_data_promise->wait();
    }

    /**
    * @group notifyChangeInEnableStatus
    */
    public function testNotifyChangeInEnableStatusSuccessfully(){
        $expected_status = new ImpreseeSubscriptionStatusModel;
        $expected_status->suspended = FALSE;
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_notify_change_status."enable/".$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $status_promise = $this->datasource->notifyChangeInActivationState(
            $this->owner_model, TRUE);
        $status_data = $status_promise->wait();
        $this->assertEquals(
            $status_data,
            NULL
        );
    }

    /**
    * @group notifyChangeInEnableStatus
    */
    public function testNotifyChangeInEnableStatusConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_notify_change_status."enable/".$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_notify_change_status."enable/".$this->owner_model->owner_code)
                    )
                )
            ));
        $update_data_promise = $this->datasource->notifyChangeInActivationState($this->owner_model, TRUE);
        $this->expectException(ConnectionException::class);
        $update_data_promise->wait();
    }

    /**
    * @group notifyChangeInEnableStatus
    */
    public function testNotifyChangeInEnableStatusServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_notify_change_status."enable/".$this->owner_model->owner_code),
                $this->equalTo([])
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->notifyChangeInActivationState($this->owner_model, TRUE);
        $this->expectException(ImpreseeServerException::class);
        $update_data_promise->wait();
    }

    /**
    * @group getCreateAccountUrl
    */
    public function testGetCreateAccountUrlSuccessfully(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 0,
                'signup_url' => 'create account url'
            ])
        );
        $expected_result = new ImpreseeCreateAccountUrlModel;
        $expected_result->url = 'create account url';
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_get_account_url),
                $this->callback(function($o) {
                    return isset($o['json']) && isset($o['json']['owner_uuid'])  && isset($o['json']['user_agent'])  && isset($o['json']['remote_ip'])  && isset($o['json']['request_url']);
                })
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $status_promise = $this->datasource->getCreateAccountUrl(
            $this->owner_model, CreateAccountUrlType::SUBSCRIBE);
        $status_data = $status_promise->wait();
        $this->assertEquals(
            $status_data,
            $expected_result
        );
    }

    /**
    * @group getCreateAccountUrl
    */
    public function testGetCreateAccountUrlConnectionError(){
        $expected_response = new Response(
            500, 
            [], 
            ''
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_get_account_url),
                $this->callback(function($o) {
                    return isset($o['json']) && isset($o['json']['owner_uuid'])  && isset($o['json']['user_agent'])  && isset($o['json']['remote_ip'])  && isset($o['json']['request_url']);
                })
            )
            ->will($this->returnValue(
                new RejectedPromise( 
                    new RequestException(
                        'Error Communicating with Server',
                        new Request('POST', $this->url_get_account_url.'/'.$this->owner_model->owner_code)
                    )
                )
            ));
        $update_data_promise = $this->datasource->getCreateAccountUrl($this->owner_model, CreateAccountUrlType::SUBSCRIBE);
        $this->expectException(ConnectionException::class);
        $update_data_promise->wait();
    }

    /**
    * @group getCreateAccountUrl
    */
    public function testGetCreateAccountUrlServerError(){
        $expected_response = new Response(
            200, 
            [], 
            json_encode([
                'status' => 1, 
                'error_message' => 'unexpected error'
            ])
        );
        $this->client->expects($this->once())
            ->method('requestAsync')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo($this->url_get_account_url),
                $this->callback(function($o) {
                    return isset($o['json']) && isset($o['json']['owner_uuid'])  && isset($o['json']['user_agent'])  && isset($o['json']['remote_ip'])  && isset($o['json']['request_url']);
                })
            )
            ->will($this->returnValue(
                new FulfilledPromise($expected_response)
            ));
        $update_data_promise = $this->datasource->getCreateAccountUrl($this->owner_model, CreateAccountUrlType::SUBSCRIBE);
        $this->expectException(ImpreseeServerException::class);
        $update_data_promise->wait();
    }
}