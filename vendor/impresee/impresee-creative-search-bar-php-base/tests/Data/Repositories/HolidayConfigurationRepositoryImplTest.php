<?php
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either; 
    use PhpFp\Either\Constructor\Left;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayThemeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayLabelsConfiguration;
    use Impresee\CreativeSearchBar\Data\Repositories\HolidayConfigurationRepositoryImpl;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveDataException;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Errors\FailureUpdateHolidayData;
    use Impresee\CreativeSearchBar\Core\Errors\FailedAtRemovingDataFailure;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

class HolidayConfigurationRepositoryImplTest extends TestCase {
    private $repository;
    private $datasource;
    private $email_datasource;
    private $configuration;
    private $model;
    private $store;


    protected function setUp(): void{
        $project_stub = $this->createMock(Project::class);
        $project_stub->method('getProjectName')
            ->willReturn('Impresee');
        $this->datasource = $this->createMock(HolidayConfigurationLocalDataSource::class);
        $this->email_datasource = $this->createMock(EmailDataSource::class);
        $this->repository = new HolidayConfigurationRepositoryImpl(
            $this->datasource,
            $this->email_datasource,
            $project_stub
        );
        $this->createHolidayData();
        $this->store = new Store;
        $this->store->url = 'http://example.com';
        $this->store->shop_email = 'example@example.com';
        $this->store->shop_title = 'Example shop';
        $this->store->language = 'en';
        $this->store->timezone = 'America/Santiago';
        $this->store->catalog_generation_code = '123456AB';
    }

    public function createHolidayData(){
        $this->model = new HolidayConfigurationModel;
        $this->model->store_logo_url = "url";
        $this->model->pop_up_title = "PopUp title";
        $this->model->pop_up_text = "PopUp text";
        $this->model->searchbar_placeholder = "placeholder";
        $this->model->search_drawing_button = "draw";
        $this->model->search_photo_button = "photo";
        $this->model->search_dropdown_label = "results";
        $this->model->to_label_letter = "to";
        $this->model->from_label_letter = "from";
        $this->model->placeholder_message_letter = "message";
        $this->model->title_canvas = "title";
        $this->model->search_button_canvas = "search";
        $this->model->button_in_product_page = "product";
        $this->model->search_results_title = "search results";
        $this->model->results_title_for_text_search = "text search results";
        $this->model->christmas_letter_share_message = "share message";
        $this->model->christmas_letter_share = "share";
        $this->model->christmas_letter_receiver_button = "this is";
        $this->model->is_mode_active = TRUE;
        $this->model->theme = HolidayConfigurationModel::ACCENT;
        $this->model->automatic_popup = TRUE;
        $this->model->add_style_to_search_bar = TRUE;
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
        $config->store_logo_url = "url";
        $config->add_style_to_search_bar = TRUE;
        $this->configuration = new HolidayConfiguration;
        $this->configuration->config_theme = $config;
        $this->configuration->labels_configuration = $labels;
    }

    /**
    * @group HolidayConfig
    */
    public function testGetHolidayConfigDataCorrectlyFromDatasource(){
        $this->datasource->expects($this->once())
            ->method('getLocalHolidayConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->returnValue($this->model));
        $return_promise = $this->repository->getHolidayConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($this->configuration)
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testGetHolidayConfigDataGenericError(){
        $this->datasource->expects($this->once())
            ->method('getLocalHolidayConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->getHolidayConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left(new UnknownFailure)
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testReturnEmptyHolidayConfigModelWhenThereIsNoData(){
        $labels = new HolidayLabelsConfiguration;
        $labels->pop_up_title = "Merry Christmas";
        $labels->pop_up_text = "Enjoy a magical moment while picking your perfect Christmas present in our store";
        $labels->searchbar_placeholder = "Find the perfect gift";
        $labels->search_drawing_button = "A drawing of the product";
        $labels->search_photo_button = "An image of the product";
        $labels->search_dropdown_label = "Send to Santa";
        $labels->to_label_letter = "To";
        $labels->from_label_letter = "From";
        $labels->placeholder_message_letter = "Write a message...";
        $labels->title_canvas = "Draw your dream gift";
        $labels->search_button_canvas = "Make it real";
        $labels->button_in_product_page = "Add this product to a Christmas letter";
        $labels->search_results_title = "Similar products";
        $labels->results_title_for_text_search = "My perfect gift is a ";
        $labels->christmas_letter_share_message = "In this christmas I wish this:";
        $labels->christmas_letter_share = "Share your letter:";
        $labels->christmas_letter_receiver_button = "View product";
        $config = new HolidayThemeConfiguration;
        $config->is_mode_active = FALSE;
        $config->theme = HolidayThemeConfiguration::ACCENT_THEME;
        $config->automatic_popup = TRUE;
        $config->add_style_to_search_bar = TRUE;
        $config->store_logo_url = "";
        $empty_configuration = new HolidayConfiguration;
        $empty_configuration->config_theme = $config;
        $empty_configuration->labels_configuration = $labels;
        $this->datasource->expects($this->once())
            ->method('getLocalHolidayConfiguration')
            ->with($this->equalTo($this->store))
            ->will($this->throwException(new NoDataException));
        $return_promise = $this->repository->getHolidayConfiguration($this->store);
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($empty_configuration)
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testUpdateHolidayConfigDataSuccessfully(){
        $this->datasource->expects($this->once())
            ->method('updateLocalHolidayConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->model))
            ->will($this->returnValue($this->model));
        $return_promise = $this->repository->updateHolidayConfiguration(
            $this->store,
            $this->configuration
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of($this->configuration)
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testUpdateHolidayConfigDataFails(){
        $expected_failure = new FailureUpdateHolidayData;
        $this->datasource->expects($this->once())
            ->method('updateLocalHolidayConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->model))
            ->will($this->throwException(new CouldNotStoreDataException));
        $return_promise = $this->repository->updateHolidayConfiguration(
            $this->store,
            $this->configuration
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

     /**
    * @group HolidayConfig
    */
    public function testUpdateHolidayConfigDataFailsGenericError(){
        $expected_failure = new UnknownFailure;
        $this->datasource->expects($this->once())
            ->method('updateLocalHolidayConfiguration')
            ->with($this->equalTo($this->store), $this->equalTo($this->model))
            ->will($this->throwException(new \Error));
        $return_promise = $this->repository->updateHolidayConfiguration(
            $this->store,
            $this->configuration
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            new Left($expected_failure)
        );
    }

    /**
    * @group HolidayConfig
    */
    public function testRemoveHolidayConfigDataSuccessfully(){
        $this->datasource->expects($this->once())
            ->method('removeLocalHolidayConfiguration')
            ->with($this->equalTo($this->store));
        $return_promise = $this->repository->removeHolidayConfiguration(
            $this->store
        );
        $return_value = $return_promise->wait();
        $this->assertEquals(
            $return_value,
            Either::of(NULL)
        );
    }

}