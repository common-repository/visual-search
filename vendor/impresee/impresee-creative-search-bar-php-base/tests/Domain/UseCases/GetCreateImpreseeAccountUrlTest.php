<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use Impresee\CreativeSearchBar\Core\Constants\CreateAccountUrlType;
    use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeCreateAccountUrl, Store};
    use Impresee\CreativeSearchBar\Domain\UseCases\GetCreateImpreseeAccountUrl;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

final class GetCreateImpreseeAccountUrlTest extends TestCase {
    private $repository;
    private $usecase;
    private $store;

    protected function setUp(): void{
        $this->repository = $this->createMock(ImpreseeConfigurationRepository::class);
        $this->usecase = new GetCreateImpreseeAccountUrl($this->repository);
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
        $expected_data = new ImpreseeCreateAccountUrl;
        $expected_data->url = 'https://example.com';
        $expected_type = "SUBSCRIBE";
        $this->repository->expects($this->once())
            ->method('getCreateAccountUrl')
            ->with($this->equalTo($this->store), $this->equalTo($expected_type))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of($expected_data))
            ));
        $data_promise = $this->usecase->execute($this->store, CreateAccountUrlType::SUBSCRIBE);
        $data = $data_promise->wait();
        $this->assertEquals(
            Either::of($expected_data),
            $data
        );
    }
}