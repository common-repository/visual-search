<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

class GetIndexationConfigurationTest extends TestCase {
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new GetIndexationConfiguration($this->repository);
    }

    public function testCallRepositoryAndReturnThatData(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $expected_indexation_config = new CatalogIndexationConfiguration;
        $expected_indexation_config->show_products_with_no_price = FALSE;
        $expected_indexation_config->index_only_in_stock_products = FALSE;
        $this->repository->expects($this->once())
            ->method('getIndexationConfiguration')
            ->with($this->equalTo($store))
            ->will($this->returnValue(new FulfilledPromise(
                Either::of($expected_indexation_config)
            )));
        $status_promise = $this->usecase->execute($store);
        $configuration = $status_promise->wait();
        $this->assertEquals(
            Either::of($expected_indexation_config),
            $configuration
        );
    }
}