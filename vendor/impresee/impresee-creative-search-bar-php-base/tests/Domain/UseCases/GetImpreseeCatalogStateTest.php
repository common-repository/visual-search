<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeApplication;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBySketch;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByPhoto;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchByText;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\OtherMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogDoneStatus;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository;

final class GetImpreseeCatalogStateTest extends TestCase {
    private $repository;
    private $usecase;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeCatalogRepository::class);
        $this->usecase = new GetImpreseeCatalogState($this->repository);
    }


    public function testGetCatalogState(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $expected_state = new CatalogDoneStatus;
        $owner_code = 'owner code';
        $impresee_data = new ImpreseeSearchBarConfiguration;
        $catalog = new ImpreseeCatalog;
        $catalog->catalog_code = 'CATALOG';
        $catalog->processed_once = TRUE;
        $catalog->catalog_market = new OtherMarket;
        $impresee_data->owner_code = $owner_code;
        $impresee_data->catalog = $catalog;
        $apps = [];
        $apps[] = new ImpreseeApplication('6789', new ImpreseeSearchBySketch);
        $apps[] = new ImpreseeApplication('abcdre', new ImpreseeSearchByPhoto);
        $apps[] = new ImpreseeApplication('12345', new ImpreseeSearchByText);
        $impresee_data->applications = $apps;
        $this->repository->expects($this->once())
            ->method('getCatalogState')
            ->with($this->equalTo($catalog))
            ->will($this->returnValue(new FulfilledPromise(
                Either::of(new CatalogDoneStatus)
            )));
        $status_promise = $this->usecase->execute($impresee_data, $store);
        $status = $status_promise->wait();
        $this->assertEquals(
            Either::of($expected_state),
            $status
        );
    }
}