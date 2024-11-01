<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayThemeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayLabelsConfiguration;
    use Impresee\CreativeSearchBar\Data\Mappers\HolidayConfigModel2HolidayConfigurationMapper;

class HolidayConfigModel2HolidayConfigurationMapperTest extends TestCase {
    private $mapper;
    private $model;
    private $configuration;

    protected function setUp(): void {
        $this->mapper = new HolidayConfigModel2HolidayConfigurationMapper;
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
        $this->model = new HolidayConfigurationModel;
        $this->model->pop_up_title = "PopUp title";
        $this->model->pop_up_text = "PopUp text";
        $this->model->searchbar_placeholder = "placeholder";
        $this->model->store_logo_url = "url";
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

    }

    public function testMapModelToConfiguration(){
        $configuration = $this->mapper->mapFrom($this->model);
        $this->assertEquals(
            $this->configuration,
            $configuration
        );
    }

    public function testMapConfigurationToModel(){
        $model = $this->mapper->mapTo($this->configuration);
        $this->assertEquals(
            $this->model,
            $model
        );
    }
}