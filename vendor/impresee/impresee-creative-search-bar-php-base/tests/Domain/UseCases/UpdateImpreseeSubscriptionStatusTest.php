<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Domain\Entities\{Store};
    use Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeSubscriptionStatus;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

final class UpdateImpreseeSubscriptionStatusTest extends TestCase {
    private $repository;
    private $usecase;
    private $store;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new UpdateImpreseeSubscriptionStatus($this->repository);
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
        $this->repository->expects($this->once())
            ->method('updateStoredSubscriptionStatus')
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