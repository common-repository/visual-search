<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog;
    use SEE\WC\CreativeSearch\Presentation\Models\ImpreseeCatalogStatus2Array;
    use SEE\WC\CreativeSearch\Presentation\Utils\{CatalogStatusGetter, PluginUtils};
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeCatalog, Store, OtherMarket, ImpreseeApplication};
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSearchBarConfiguration, ImpreseeSearchByPhoto, ImpreseeSearchBySketch, ImpreseeSearchByText};
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogDoneStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIsProcessingStatus;

final class CatalogStatusGetterTest extends TestCase {
    private $status_getter;
    private $get_store_information;
    private $get_catalog_state;
    private $get_impresee_config;
    private $plugin_utils;
    private $update_impresee_catalog;
    private $store;
    private $impresee_data;

    protected function setUp(){
        $store_url = 'http://ejemplo';
        $admin_email = 'admin@ejemplo.com';
        $shop_title = 'Tienda';
        $timezone = 'America/Santiago';
        $language = 'en';
        $catalog_code = 'code';
        $this->store = new Store;
        $this->store->url = $store_url;
        $this->store->shop_email = $admin_email;
        $this->store->shop_title = $shop_title;
        $this->store->timezone = $timezone;
        $this->store->language = $language;
        $this->store->catalog_generation_code = $catalog_code;
        $this->impresee_data = new ImpreseeSearchBarConfiguration;
        $expected_catalog = new ImpreseeCatalog;
        $expected_catalog->catalog_code = 'CATALOG';
        $expected_catalog->processed_once = TRUE;
        $expected_catalog->catalog_market = new OtherMarket;
        $this->impresee_data->owner_code = 'owner code';
        $this->impresee_data->catalog = $expected_catalog;
        $apps = [];
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $this->impresee_data->applications = $apps;
        $this->get_catalog_state = $this->createMock(GetImpreseeCatalogState::class);
        $this->get_impresee_config = $this->createMock(GetImpreseeConfiguration::class);
        $this->plugin_utils = $this->createMock(PluginUtils::class);
        $this->update_impresee_catalog = $this->createMock(UpdateImpreseeCatalog::class);
    }

    public function testGetCatalogStatusCorrectly(){
        $expected_array = [
            'processing' => FALSE,
            'has_error'  => FALSE
        ];
        $this->plugin_utils->expects($this->exactly(2))
            ->method('getStore')
            ->will($this->returnValue($this->store));
        $this->get_impresee_config->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise(Either::of($this->impresee_data))));
        $this->get_catalog_state->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($this->impresee_data), $this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise(Either::of(new CatalogDoneStatus))));
        $this->status_getter = new CatalogStatusGetter(
            $this->plugin_utils,
            $this->get_impresee_config,
            $this->get_catalog_state,
            $this->update_impresee_catalog
        );
        $status_as_array = $this->status_getter->getCatalogState('owner code');
        $this->assertEquals(
            $status_as_array,
            $expected_array
        );
    }



    public function testUpdateCatalogCorrectly(){
        $expected_array = [
            'processing' => TRUE,
            'has_error'  => FALSE
        ];
        $this->plugin_utils->expects($this->exactly(2))
            ->method('getStore')
            ->will($this->returnValue($this->store));
        $this->get_impresee_config->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise(Either::of($this->impresee_data))));
        $this->update_impresee_catalog->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($this->impresee_data), $this->equalTo($this->store))
            ->will($this->returnValue(new FulfilledPromise(Either::of(new CatalogIsProcessingStatus('https://example.com')))));
        $this->status_getter = new CatalogStatusGetter(
            $this->plugin_utils,
            $this->get_impresee_config,
            $this->get_catalog_state,
            $this->update_impresee_catalog
        );
        $status_as_array = $this->status_getter->updateCatalog('owner code', 'CATALOG');
        $this->assertEquals(
            $status_as_array,
            $expected_array
        );
    }

}