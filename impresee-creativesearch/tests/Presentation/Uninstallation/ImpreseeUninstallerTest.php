<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
    use Impresee\CreativeSearchBar\Domain\UseCases\RemoveAllImpreseeRelatedData;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeCatalog, Store, OtherMarket, ImpreseeApplication};
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSearchBarConfiguration,ImpreseeSearchByPhoto, ImpreseeSearchBySketch, ImpreseeSearchByText,
        EmptyImpreseeSearchBarConfiguration
    };
    use SEE\WC\CreativeSearch\Presentation\Uninstallation\ImpreseeUninstaller;
    use Impresee\CreativeSearchBar\Core\Errors\NoImpreseeConfigurationDataFailure;

define( 'IMSEE_PLUGIN_PATH', 'plugin_path' ) ;
define( 'IMSEE_PLUGIN_URL', 'plugin_url' );
final class ImpreseeUninstallerTest extends TestCase{
    private $uninstaller;
    private $remove_impresee_data;
    private $utils;

    protected function setUp(){
        
        $this->remove_impresee_data = $this->createMock(RemoveAllImpreseeRelatedData::class);
        $this->utils = $this->createMock(PluginUtils::class);
        $this->uninstaller = new ImpreseeUninstaller(
            $this->remove_impresee_data,
            $this->utils
        );
    }

    public function testCalledUninstallCorrectly(){
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
        $apps = [];
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $this->utils->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($expected_store));
        $this->remove_impresee_data->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($expected_store))
            ->will($this->returnValue(new FulfilledPromise(Either::of(NULL))));
        $this->utils->expects($this->once())
            ->method('deleteAllOldAndFrontendOptions');
        $this->uninstaller->removeAllData();
    }

    public function testCalledUninstallCorrectlyWithEmptyData(){
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
        $this->utils->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($expected_store));
        $this->remove_impresee_data->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($expected_store))
            ->will($this->returnValue(new FulfilledPromise(Either::of(NULL))));
        $this->utils->expects($this->once())
            ->method('deleteAllOldAndFrontendOptions');
        $this->uninstaller->removeAllData();
    }

}