<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSearchBarConfiguration, Store, ImpreseeCatalog, ImpreseeApplication, ImpreseeSearchByPhoto,
        ImpreseeSearchBySketch, ImpreseeSearchByText, OtherMarket
    };
    use Impresee\CreativeSearchBar\Domain\UseCases\RemoveAllImpreseeRelatedData;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;

final class RemoveAllImpreseeRelatedDataTest extends TestCase {
    private $repository;
    private $searchbar_repository;
    private $holiday_config_repository;
    private $usecase;
    private $store;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->searchbar_repository = $this->createMock(SearchBarDisplayConfigurationRepository::class);
        $this->holiday_config_repository = $this->createMock(HolidayConfigurationRepository::class);
        $this->usecase = new RemoveAllImpreseeRelatedData($this->repository, $this->searchbar_repository, $this->holiday_config_repository);
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

    public function testRemoveAllImpreseeDataForStore(){
        $this->repository->expects($this->once())
            ->method('removeAllData')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of(NULL))
            ));
        $this->searchbar_repository->expects($this->once())
            ->method('removeCustomCodeConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of(NULL))
            ));
        $this->searchbar_repository->expects($this->once())
            ->method('removeSnippetConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of(NULL))
            ));
        $this->holiday_config_repository->expects($this->once())
            ->method('removeHolidayConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of(NULL))
            ));
        $data_promise = $this->usecase->execute($this->store);
        $data = $data_promise->wait();
        $this->assertEquals(
            Either::of(NULL),
            $data
        );
    }
}