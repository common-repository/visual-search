<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\UpdateCustomCodeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;


class UpdateCustomCodeConfigurationTest extends TestCase { 
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(SearchBarDisplayConfigurationRepository::class);
        $this->usecase = new UpdateCustomCodeConfiguration($this->repository);
    }

    public function testCallRepositoryAndReturnTheData(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $store->catalog_generation_code = '123456AB';
        $configuration = new CustomCodeConfiguration;
        $configuration->js_add_buttons = 'let variable = 1;';
        $configuration->css_style_buttons = '.button{}';
        $configuration->js_after_load_results_code = "console.log('running');";
        $configuration->js_before_load_results_code = "console.log('running');";
        $configuration->js_search_failed_code = "console.log('running');";
        $configuration->js_press_see_all_code = "console.log('running');";
        $configuration->js_close_text_results_code = "console.log('running');";
        $configuration->js_on_open_text_dropdown_code = "console.log('running');";
        $this->repository->expects($this->once())
            ->method('updateCustomCodeConfiguration')
            ->with($this->equalTo($store), $this->equalTo($configuration))
            ->will($this->returnValue(
                new FulfilledPromise(Either::of($configuration)))
            );
        $return_promise = $this->usecase->execute($store, $configuration);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($configuration)
        );
    }
}