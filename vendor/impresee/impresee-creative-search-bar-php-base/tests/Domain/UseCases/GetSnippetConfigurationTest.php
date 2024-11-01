<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetGeneralConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetLabelsConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetSearchByTextConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;


class GetSnippetConfigurationTest extends TestCase { 
    private $usecase;
    private $repository;

    protected function setUp(): void{
        $this->repository = $this->createMock(SearchBarDisplayConfigurationRepository::class);
        $this->usecase = new GetSnippetConfiguration($this->repository);
    }

    public function testCallRepositoryToObtainTheData(){
        $expected_general_config = new ImpreseeSnippetGeneralConfiguration;
        $expected_general_config->load_after_page_render = FALSE;
        $expected_general_config->container_selector = '.value';
        $expected_general_config->main_color = '#9CD333';
        $expected_general_config->add_search_data_to_url = TRUE;
        $expected_general_config->images_only_loaded_from_camera = FALSE;
        $expected_general_config->disable_image_crop = FALSE;
        $expected_general_config->price_fraction_digit_number = 2;
        $expected_general_config->currency_symbol_at_the_end = FALSE;
        $expected_general_config->on_sale_label_color = '#FF0000';
        $expected_label_config = new ImpreseeSnippetLabelsConfiguration;
        $expected_label_config->search_results_title = 'value 1';
        $expected_label_config->search_button_label = 'value 2';
        $expected_label_config->oops_exclamation = 'value 3';
        $expected_label_config->error_title = 'value 4';
        $expected_label_config->error_message = 'value 5';
        $expected_label_config->drag_and_drop_image_title = 'value 6';
        $expected_label_config->drag_and_drop_image_body = 'value 7';
        $expected_label_config->custom_crop_label = 'value 8';
        $expected_label_config->start_writing_label = 'value 9';
        $expected_label_config->currency_symbol = '$';
        $expected_label_config->search_by_photo_label = 'value 10';
        $expected_label_config->search_by_sketch_label = 'value 11';
        $expected_label_config->see_all_results_label = 'value 12';
        $expected_label_config->no_matching_results = 'value 13';
        $expected_label_config->on_sale_label = 'value 14';
        $expected_label_config->result_title_search_by_text = 'value 15';
        $expected_label_config->number_of_results_label_desktop = 'value 16';
        $expected_label_config->number_of_results_label_mobile = 'value 17';
        $expected_label_config->filters_title_label_mobile = 'value 18';
        $expected_label_config->clear_filters_label = 'value 19';
        $expected_label_config->sort_by_label = 'value 20';
        $expected_label_config->apply_filters_label_mobile = 'value 21';
        $expected_label_config->try_searching_again_label = 'value 22';
        $expected_text_config = new ImpreseeSnippetSearchByTextConfiguration;
        $expected_text_config->use_text = TRUE;
        $expected_text_config->search_delay_millis = 300;
        $expected_text_config->full_text_search_results_container = '.container';
        $expected_text_config->compute_results_top_position_from = 'header';
        $expected_text_config->use_instant_full_search = TRUE;
        $expected_text_config->use_floating_search_bar_button = TRUE;
        $expected_text_config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_RIGHT;
        $expected_config = new ImpreseeSnippetConfiguration;
        $expected_config->general_configuration = $expected_general_config;
        $expected_config->labels_configuration = $expected_label_config;
        $expected_config->search_by_text_configuration = $expected_text_config;
        $store = new Store;
        $store->url = 'http://example.com';
        $store->shop_email = 'example@example.com';
        $store->shop_title = 'Example shop';
        $store->language = 'en';
        $store->timezone = 'America/Santiago';
        $store->catalog_generation_code = '123456AB';
        $this->repository->expects($this->once())
            ->method('getLocalImpreseeSnippetConfiguration')
            ->with($this->equalTo($store))
            ->will($this->returnValue(new FulfilledPromise(Either::of($expected_config))));
        $return_promise = $this->usecase->execute($store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($expected_config)
        );
    }

}