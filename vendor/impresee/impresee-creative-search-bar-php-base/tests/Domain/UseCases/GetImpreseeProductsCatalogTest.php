<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeProductsCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeProductsCatalog;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository;


class GetImpreseeProductsCatalogTest extends TestCase { 
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeCatalogRepository::class);
        $this->usecase = new GetImpreseeProductsCatalog($this->repository);
    }

    public function testCallRepositoryCorrectly(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $store->catalog_generation_code = '123456AB';
        $configuration = new CatalogIndexationConfiguration;
        $configuration->show_products_with_no_price = TRUE;
        $configuration->index_only_in_stock_products = TRUE;
        $expected_catalog = new ImpreseeProductsCatalog;
        $expected_catalog->impresee_catalog_version = 'xml_impresee_20';
        $expected_catalog->impresee_catalog_string = '<feed></feed>';
        
        $this->repository->expects($this->once())
            ->method('getProductsCatalog')
            ->with($this->equalTo($store), $this->equalTo($configuration))
            ->will($this->returnValue(
                new FulfilledPromise(
                    Either::of($expected_catalog)
                )
            ));
        $result_promise = $this->usecase->execute($store, $configuration);
        $result = $result_promise->wait();
        $this->assertEquals(
            $result,
            Either::of($expected_catalog)
        );
    } 
}