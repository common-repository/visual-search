<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeCatalog, Store, OtherMarket};
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

final class RegisterImpreseeConfigurationTest extends TestCase {
    private $repository;
    private $usecase;
    private $store;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new RegisterImpreseeConfiguration($this->repository);
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

    public function testRegisterImpreseeConfiguration(){
        $market = new OtherMarket;
        $this->repository->expects($this->once())
            ->method('registerImpreseeConfiguration')
            ->with($this->equalTo($this->store), 
                $this->equalTo($market))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of(NULL))
            ));
        $promise = $this->usecase->execute($this->store, $market);
        $promise->wait();
    }
}