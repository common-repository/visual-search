<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\UpdateIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

class UpdateIndexationConfigurationTest extends TestCase {
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new UpdateIndexationConfiguration($this->repository);
    }

    public function testCallRepositoryAndReturnThatData(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $indexation_config = new CatalogIndexationConfiguration;
        $indexation_config->show_products_with_no_price = FALSE;
        $indexation_config->index_only_in_stock_products = FALSE;
        $this->repository->expects($this->once())
            ->method('UpdateIndexationConfiguration')
            ->with($this->equalTo($store), $this->equalTo($indexation_config))
            ->will($this->returnValue(new FulfilledPromise(
                Either::of($indexation_config)
            )));
        $status_promise = $this->usecase->execute($store, $indexation_config);
        $result = $status_promise->wait();
        $this->assertEquals(
            $result,
            Either::of($indexation_config)
        );
    }
}