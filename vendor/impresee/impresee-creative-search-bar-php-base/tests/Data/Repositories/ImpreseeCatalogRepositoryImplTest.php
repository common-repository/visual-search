<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use GuzzleHttp\Promise\RejectedPromise;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;
    use Impresee\CreativeSearchBar\Data\Repositories\ImpreseeCatalogRepositoryImpl;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ProductsCatalogXMLDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ProductsDataSource;
    use Impresee\CreativeSearchBar\Data\Models\CatalogStatusModel;
    use Impresee\CreativeSearchBar\Data\Models\UpdateCatalogModel;
    use Impresee\CreativeSearchBar\Data\Models\ProductModel;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\HomeDecorMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogDoneStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIsProcessingStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogStatusError;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeProductsCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Constants\ExceptionCodes;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Errors\ErrorObtainingProducts;
    use Impresee\CreativeSearchBar\Core\Errors\ErrorBuildingCatalog;
    use Impresee\CreativeSearchBar\Core\Errors\FailureAtObtainingProducts;
    use Impresee\CreativeSearchBar\Core\Errors\FailureAtCreatingCatalog;

final class ImpreseeCatalogRepositoryImplTest extends TestCase {
    private $remote_data_source;
    private $store_data_source;
    private $email_data_source;
    private $local_data_source;
    private $products_datasource;
    private $catalog_xml_datasource;
    private $repository;
    private $test_catalog;
    private $owner_code;
    private $store;

    protected function setUp(): void{
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getProjectName')
            ->willReturn('Impresee');
        $this->remote_data_source = $this->createMock(ImpreseeRemoteDataSource::class);
        $this->store_data_source = $this->createMock(StoreLocalDataSource::class);
        $this->email_data_source = $this->createMock(EmailDataSource::class);
        $this->local_data_source = $this->createMock(ImpreseeLocalDataSource::class);
        $this->products_datasource = $this->createMock(ProductsDataSource::class);
        $this->catalog_xml_datasource = $this->createMock(ProductsCatalogXMLDataSource::class);
        $this->repository = new ImpreseeCatalogRepositoryImpl(
            $this->remote_data_source, 
            $this->email_data_source, 
            $this->store_data_source,
            $this->local_data_source,
            $this->products_datasource,
            $this->catalog_xml_datasource,
            $project_stub
        );
        $this->test_catalog = new ImpreseeCatalog;
        $this->test_catalog->catalog_code = 'CATALOG';
        $this->test_catalog->processed_once = TRUE;
        $this->test_catalog->catalog_market = new HomeDecorMarket;
        $this->owner_code = 'owner_code';
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
    }

    public function testGetCatalogStatusDoneSuccessfully(){
        $store_url = 'http://example.com';
        $expected_status_model = new CatalogStatusModel;
        $expected_status_model->processing = FALSE;
        $expected_status_model->update_url = '';
        $expected_status_model->last_successful_update = 12346;
        $this->remote_data_source->expects($this->once())
            ->method('getCatalogState')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code)
            )
            ->will($this->returnValue(new FulfilledPromise($expected_status_model)));
        $this->local_data_source->expects($this->once())
            ->method('setCatalogProcessedOnce')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->once())
            ->method('setFinishedOnboarding')
            ->with($this->equalTo($store_url));
        $this->email_data_source->expects($this->never())
            ->method($this->anything());
        $status_promise = $this->repository->getCatalogState($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_status = new CatalogDoneStatus;
        $this->assertEquals(
            Either::of($expected_status), 
            $status
        );
    }


    public function testGetCatalogStatusProcessingSuccessfully(){
        $processing_url = 'https://example.com/processing';
        $expected_status_model = new CatalogStatusModel;
        $expected_status_model->processing = TRUE;
        $expected_status_model->update_url = $processing_url;
        $expected_status_model->last_successful_update = 0;
        $this->remote_data_source->expects($this->once())
            ->method('getCatalogState')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code)
            )
            ->will($this->returnValue(new FulfilledPromise($expected_status_model)));
        $this->local_data_source->expects($this->never())
            ->method('setCatalogProcessedOnce')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->never())
            ->method($this->anything());
        $this->email_data_source->expects($this->never())
            ->method($this->anything());
        $status_promise = $this->repository->getCatalogState($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_status = new CatalogIsProcessingStatus($processing_url);
        $this->assertEquals(
            Either::of($expected_status), 
            $status
        );

    }

    public function testGetCatalogStatusWithErrors(){
        $store_url = 'https://example.com';
        $this->remote_data_source->expects($this->once())
            ->method('getCatalogState')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code))
            ->will($this->returnValue(new RejectedPromise(
                new \Exception(ExceptionCodes::ERROR_GETTING_CATALOG_STATUS)
            )));
        $this->local_data_source->expects($this->never())
            ->method('setCatalogProcessedOnce')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->email_data_source->expects($this->once())
            ->method('sendErrorEmail');
        $status_promise = $this->repository->getCatalogState($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_status = new CatalogStatusError;
        $this->assertEquals(
            Either::of($expected_status), 
            $status
        );
    }

    public function testGetCatalogStatusGenericError(){
        $store_url = 'https://example.com';
        $this->remote_data_source->expects($this->once())
            ->method('getCatalogState')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code))
            ->will($this->throwException(
                new \Exception(ExceptionCodes::ERROR_GETTING_CATALOG_STATUS)
            ));
        $this->local_data_source->expects($this->never())
            ->method('setCatalogProcessedOnce')
            ->with($this->equalTo($this->store));
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->email_data_source->expects($this->once())
            ->method('sendErrorEmail');
        $status_promise = $this->repository->getCatalogState($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_result = new UnknownFailure;
        $this->assertEquals(
            new Left($expected_result), 
            $status
        );
    }

    public function testSendToUpdateSucessfully(){
        $processing_url = 'https://example.com/processing';
        $expected_status_model = new UpdateCatalogModel;
        $expected_status_model->update_url = $processing_url;
        $this->remote_data_source->expects($this->once())
            ->method('updateCatalog')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code))
            ->will($this->returnValue(
                new FulfilledPromise($expected_status_model)
            ));
        $this->store_data_source->expects($this->never())
            ->method($this->anything());
        $this->local_data_source->expects($this->once())
            ->method('setSentCatalogToUpdate')
            ->with($this->equalTo($this->store));
        $this->local_data_source->expects($this->once())
            ->method('setLastCatalogUpdateProcessUrl')
            ->with($this->equalTo($this->store), $this->equalTo($processing_url));
        $this->email_data_source->expects($this->never())
            ->method($this->anything());
        $status_promise = $this->repository->updateCatalog($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_status = new CatalogIsProcessingStatus($processing_url);
        $this->assertEquals(
            Either::of($expected_status), 
            $status
        );
    }

    public function testSendToUpdateFailed(){
        $store_url = 'https://example.com';
        $this->remote_data_source->expects($this->once())
            ->method('updateCatalog')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code))
            ->will($this->returnValue(
                new RejectedPromise(new \Exception(
                    ExceptionCodes::ERROR_SENDING_CATALOG_TO_UPDATE
                ))
            ));
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->email_data_source->expects($this->once())
            ->method($this->anything());
        $this->local_data_source->expects($this->never())
            ->method('setSentCatalogToUpdate')
            ->with($this->equalTo($this->store));
        $this->local_data_source->expects($this->never())
            ->method('setLastCatalogUpdateProcessUrl');
        $status_promise = $this->repository->updateCatalog($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_status = new CatalogStatusError;
        $this->assertEquals(
            Either::of($expected_status), 
            $status
        );
    }

    public function testSendToUpdateGenericError(){
        $store_url = 'https://example.com';
        $this->remote_data_source->expects($this->once())
            ->method('updateCatalog')
            ->with($this->equalTo($this->test_catalog),
                $this->equalTo($this->owner_code))
            ->will($this->throwException(
                new \Exception(
                    ExceptionCodes::ERROR_SENDING_CATALOG_TO_UPDATE
                )
            ));
        $this->store_data_source->expects($this->once())
            ->method('getStoreUrl')
            ->will($this->returnValue($store_url));
        $this->email_data_source->expects($this->once())
            ->method($this->anything());
        $this->local_data_source->expects($this->never())
            ->method('setSentCatalogToUpdate')
            ->with($this->equalTo($this->store));
        $this->local_data_source->expects($this->never())
            ->method('setLastCatalogUpdateProcessUrl');
        $status_promise = $this->repository->updateCatalog($this->test_catalog, $this->owner_code, $this->store);
        $status = $status_promise->wait();
        $expected_status = new UnknownFailure;
        $this->assertEquals(
            new Left($expected_status), 
            $status
        );
    }

    public function testGenerateCatalogCorrectly(){
        $product = new ProductModel;
        $product->id = 123;
        $product->sku = "AB123";
        $product->name = "product";
        $product->url = "https://example.com/p";
        $product->price = 20.30;
        $product->price_from = 51;
        $product->parent_id = 1234;
        $product->main_category = "Category";
        $product->thumbnail = "https://example.com/p.jpg";
        $product->main_image = "https://example.com/p.jpg";
        $product->secondary_categories = array('Category 2');
        $product->secondary_images = array("https://example.com/p-1.jpg");
        $product->extra_attributes = array('type' => 'variation');
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $catalog_string = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'."\n".'<feed><product><id><![CDATA[123]]></id><sku><![CDATA[AB123]]></sku><name><![CDATA[product]]></name><url><![CDATA[https://example.com/p]]></url><price><![CDATA[20.99]]></price><price_from><![CDATA[51]]></price_from><parent_id><![CDATA[1234]]></parent_id><main_category><![CDATA[Category]]></main_category><main_image><![CDATA[https://example.com/p.jpg]]></main_image><thumbnail><![CDATA[https://example.com/p.jpg]]></thumbnail><secondary_category0><![CDATA[Category 2]]></secondary_category0><secondary_image0><![CDATA[https://example.com/p-1.jpg]]></secondary_image0><type><![CDATA[variation]]></type></product></feed>';
        $expected_catalog = new ImpreseeProductsCatalog;
        $expected_catalog->impresee_catalog_version = 'xml_impresee_20';
        $expected_catalog->impresee_catalog_string = $catalog_string;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->returnValue(array($product)));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('getCatalogVersion')
            ->will($this->returnValue('xml_impresee_20'));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('generateXmlFromProducts')
            ->with($this->equalTo(array($product)))
            ->will($this->returnValue($catalog_string));
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            Either::of($expected_catalog), 
            $catalog
        );
    }

    public function testGenerateCatalogFailAtObtainingProducts(){
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->throwException(new ErrorObtainingProducts));
        $this->catalog_xml_datasource->expects($this->never())
            ->method('getCatalogVersion');
        $this->catalog_xml_datasource->expects($this->never())
            ->method('generateXmlFromProducts');
        $expected_failure = new FailureAtObtainingProducts;
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $catalog
        );
    }

    public function testGenerateCatalogFailAtObtainingProductsGenericError(){
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->throwException(new \Error));
        $this->catalog_xml_datasource->expects($this->never())
            ->method('getCatalogVersion');
        $this->catalog_xml_datasource->expects($this->never())
            ->method('generateXmlFromProducts');
        $expected_failure = new UnknownFailure;
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            new Left($expected_failure),
            $catalog
        );
    }

    public function testGenerateCatalogFailAtGeneratingCatalog(){
        $product = new ProductModel;
        $product->id = 123;
        $product->sku = "AB123";
        $product->name = "product";
        $product->url = "https://example.com/p";
        $product->price = 20.30;
        $product->price_from = 51;
        $product->parent_id = 1234;
        $product->main_category = "Category";
        $product->thumbnail = "https://example.com/p.jpg";
        $product->main_image = "https://example.com/p.jpg";
        $product->secondary_categories = array('Category 2');
        $product->secondary_images = array("https://example.com/p-1.jpg");
        $product->extra_attributes = array('type' => 'variation');
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->returnValue(array($product)));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('getCatalogVersion')
            ->will($this->returnValue('xml_impresee_20'));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('generateXmlFromProducts')
            ->with($this->equalTo(array($product)))
            ->will($this->throwException(new ErrorBuildingCatalog));
        $expected_failure = new FailureAtCreatingCatalog;
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            new Left($expected_failure), 
            $catalog
        );
    }

    public function testGenerateCatalogFailAtObtainingCatalogVersion(){
        $product = new ProductModel;
        $product->id = 123;
        $product->sku = "AB123";
        $product->name = "product";
        $product->url = "https://example.com/p";
        $product->price = 20.30;
        $product->price_from = 51;
        $product->parent_id = 1234;
        $product->main_category = "Category";
        $product->thumbnail = "https://example.com/p.jpg";
        $product->main_image = "https://example.com/p.jpg";
        $product->secondary_categories = array('Category 2');
        $product->secondary_images = array("https://example.com/p-1.jpg");
        $product->extra_attributes = array('type' => 'variation');
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->returnValue(array($product)));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('getCatalogVersion')
            ->will($this->throwException(new ErrorBuildingCatalog));
        $this->catalog_xml_datasource->expects($this->never())
            ->method('generateXmlFromProducts')
            ->with($this->equalTo(array($product)));
        $expected_failure = new FailureAtCreatingCatalog;
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            new Left($expected_failure), 
            $catalog
        );
    }

    public function testGenerateCatalogFailAtGeneratingCatalogGenericError(){
        $product = new ProductModel;
        $product->id = 123;
        $product->sku = "AB123";
        $product->name = "product";
        $product->url = "https://example.com/p";
        $product->price = 20.30;
        $product->price_from = 51;
        $product->parent_id = 1234;
        $product->main_category = "Category";
        $product->thumbnail = "https://example.com/p.jpg";
        $product->main_image = "https://example.com/p.jpg";
        $product->secondary_categories = array('Category 2');
        $product->secondary_images = array("https://example.com/p-1.jpg");
        $product->extra_attributes = array('type' => 'variation');
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->returnValue(array($product)));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('getCatalogVersion')
            ->will($this->returnValue('xml_impresee_20'));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('generateXmlFromProducts')
            ->with($this->equalTo(array($product)))
            ->will($this->throwException(new \Error));
        $expected_failure = new UnknownFailure;
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            new Left($expected_failure), 
            $catalog
        );
    }

    public function testGenerateCatalogFailAtObtainingCatalogVersionGenericError(){
        $product = new ProductModel;
        $product->id = 123;
        $product->sku = "AB123";
        $product->name = "product";
        $product->url = "https://example.com/p";
        $product->price = 20.30;
        $product->price_from = 51;
        $product->parent_id = 1234;
        $product->main_category = "Category";
        $product->thumbnail = "https://example.com/p.jpg";
        $product->main_image = "https://example.com/p.jpg";
        $product->secondary_categories = array('Category 2');
        $product->secondary_images = array("https://example.com/p-1.jpg");
        $product->extra_attributes = array('type' => 'variation');
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $this->products_datasource->expects($this->once())
            ->method('getFilteredStoreProducts')
            ->with($this->equalTo($this->store), $this->equalTo($configuration))
            ->will($this->returnValue(array($product)));
        $this->catalog_xml_datasource->expects($this->once())
            ->method('getCatalogVersion')
            ->will($this->throwException(new \Error));
        $this->catalog_xml_datasource->expects($this->never())
            ->method('generateXmlFromProducts')
            ->with($this->equalTo(array($product)));
        $expected_failure = new UnknownFailure;
        $catalog_promise = $this->repository->getProductsCatalog($this->store, $configuration);
        $catalog = $catalog_promise->wait();
        $this->assertEquals(
            new Left($expected_failure), 
            $catalog
        );
    }
}