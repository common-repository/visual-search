<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayThemeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayLabelsConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;


class GetHolidayConfigurationTest extends TestCase { 
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(HolidayConfigurationRepository::class);
        $this->usecase = new GetHolidayConfiguration($this->repository);
    }

    public function testCallRepositoryCorrectly(){
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $store->catalog_generation_code = '123456AB';
        $labels = new HolidayLabelsConfiguration;
        $labels->pop_up_title = "PopUp title";
        $labels->pop_up_text = "PopUp text";
        $labels->searchbar_placeholder = "placeholder";
        $labels->search_drawing_button = "draw";
        $labels->search_photo_button = "photo";
        $labels->search_dropdown_label = "results";
        $labels->to_label_letter = "to";
        $labels->from_label_letter = "from";
        $labels->placeholder_message_letter = "message";
        $labels->title_canvas = "title";
        $labels->search_button_canvas = "search";
        $labels->button_in_product_page = "product";
        $labels->search_results_title = "search results";
        $labels->results_title_for_text_search = "text search results";
        $labels->christmas_letter_share_message = "share message";
        $labels->christmas_letter_share = "share";
        $labels->christmas_letter_receiver_button = "this is";
        $config = new HolidayThemeConfiguration;
        $config->is_mode_active = TRUE;
        $config->theme = HolidayThemeConfiguration::ACCENT_THEME;
        $config->automatic_popup = TRUE;
        $config->add_style_to_search_bar = TRUE;
        $expected_configuration = new HolidayConfiguration;
        $expected_configuration->config_theme = $config;
        $expected_configuration->labels_configuration = $labels;
        
        $this->repository->expects($this->once())
            ->method('getHolidayConfiguration')
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