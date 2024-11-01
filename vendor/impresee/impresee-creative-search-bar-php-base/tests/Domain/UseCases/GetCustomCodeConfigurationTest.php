<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;


class GetCustomCodeConfigurationTest extends TestCase { 
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(SearchBarDisplayConfigurationRepository::class);
        $this->usecase = new GetCustomCodeConfiguration($this->repository);
    }

    public function testCallRepositoryCorrectly(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $store->catalog_generation_code = '123456AB';
        $expected_configuration = new CustomCodeConfiguration;
        $expected_configuration->js_add_buttons = 'let variable = 1;';
        $expected_configuration->css_style_buttons = '.button{}';
        $expected_configuration->js_after_load_results_code = "console.log('running');";
        $expected_configuration->js_before_load_results_code = "console.log('running');";
        $expected_configuration->js_search_failed_code = "console.log('running');";
        $expected_configuration->js_press_see_all_code = "console.log('running');";
        $expected_configuration->js_close_text_results_code = "console.log('running');";
        $expected_configuration->js_on_open_text_dropdown_code = "console.log('running');";
        $this->repository->expects($this->once())
            ->method('getSearchBarCustomCodeConfiguration')
            ->with($this->equalTo($store))
            ->will($this->returnValue(
                new FulfilledPromise(
                    Either::of($expected_configuration)
                )
            ));
        $result_promise = $this->usecase->execute($store);
        $result = $result_promise->wait();
        $this->assertEquals(
            $result,
            Either::of($expected_configuration)
        );
    } 
}