<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeCatalog, Store, OtherMarket, ImpreseeApplication};
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSearchBarConfiguration, ImpreseeSearchByPhoto, ImpreseeSearchBySketch, ImpreseeSearchByText};
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

final class GetImpreseeConfigurationTest extends TestCase {
    private $repository;
    private $usecase;
    private $store;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new GetImpreseeConfiguration($this->repository);
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
    }

    public function testGetImpreseeConfigurationForStore(){
        $expected_data = new ImpreseeSearchBarConfiguration;
        $expected_catalog = new ImpreseeCatalog;
        $expected_catalog->catalog_code = 'CATALOG';
        $expected_catalog->processed_once = TRUE;
        $expected_catalog->catalog_market = new OtherMarket;
        $expected_data->owner_code = 'owner code';
        $expected_data->catalog = $expected_catalog;
        $apps = [];
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $expected_data->applications = $apps;
        $this->repository->expects($this->once())
            ->method('getImpreseeConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of($expected_data))
            ));
        $data_promise = $this->usecase->execute($this->store);
        $data = $data_promise->wait();
        $this->assertEquals(
            Either::of($expected_data),
            $data
        );
    }
}