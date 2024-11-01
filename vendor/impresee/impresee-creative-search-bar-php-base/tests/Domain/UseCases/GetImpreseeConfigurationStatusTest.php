<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeConfigurationStatus, Store};
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

final class GetImpreseeConfigurationStatusTest extends TestCase {
    private $repository;
    private $usecase;
    private $store;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new GetImpreseeConfigurationStatus($this->repository);
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

    public function testGetImpreseeConfigurationStatusForStore(){
        $expected_data = new ImpreseeConfigurationStatus;
        $expected_data->created_data = TRUE;
        $expected_data->sent_catalog_to_update = TRUE;
        $expected_data->last_catalog_update_url = 'http://example.com';
        $expected_data->finish_first_catalog_update = TRUE;
        $expected_data->catalog_processed_once = TRUE;
        $this->repository->expects($this->once())
            ->method('getConfigurationStatus')
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