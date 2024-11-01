<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetStoreInformation;
    use Impresee\CreativeSearchBar\Domain\Repositories\StoreRepository;

final class GetStoreInformationTest extends TestCase {
    private $repository;
    private $usecase;

    protected function setUp(): void{
        $this->repository = $this->createMock(StoreRepository::class);
        $this->usecase = new GetStoreInformation($this->repository);
    }

    public function testGetStoreInformation(){
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
        $this->repository->expects($this->once())
            ->method('getStoreInformation')
            ->will($this->returnValue(
                new FulfilledPromise(Either::of($expected_store))
            ));
        $store_promise = $this->usecase->execute();
        $store = $store_promise->wait();
        $this->assertEquals(
            Either::of($expected_store),
            $store
        );
    }

}