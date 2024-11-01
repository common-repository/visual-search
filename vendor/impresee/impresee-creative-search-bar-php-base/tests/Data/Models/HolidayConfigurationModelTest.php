<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class HolidayConfigurationModelTest extends TestCase {
    private $data_array;
    private $model;

    protected function setUp(): void {
        $this->data_array = array(
            'pop_up_title' => "PopUp title",
            'pop_up_text' => "PopUp text",
            'searchbar_placeholder' => "placeholder",
            'search_drawing_button' => "draw",
            'search_photo_button' => "photo",
            'search_dropdown_label' => "results",
            'to_label_letter' => "to",
            'from_label_letter' => "from",
            'placeholder_message_letter' => "message",
            'title_canvas' => "title",
            'search_button_canvas' => "search",
            'button_in_product_page' => "product",
            'search_results_title' => "search results",
            'results_title_for_text_search' => "text search results",
            'christmas_letter_share_message' => "share message",
            'christmas_letter_share' => "share",
            'christmas_letter_receiver_button' => "this is",
            'is_mode_active' => TRUE,
            'theme' => 1,
            'automatic_popup' => TRUE,
            'add_style_to_search_bar' => TRUE,
            'store_logo_url' => "url"
        );
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
    }

    public function testMapFromArrayCorrectly(){
        $empty_model = new HolidayConfigurationModel;
        $empty_model->loadDataFromArray($this->data_array);
        $this->assertEquals(
            $this->model,
            $empty_model
        );
    }

    public function testMapFromArrayFailsBecauseOfIncompleteData(){
        $data_array = array(
        );
        $this->expectException(NoDataException::class);
        $empty_model = new HolidayConfigurationModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testToArray(){
        $mapped_array = $this->model->toArray();
        $this->assertEquals(
            $this->data_array,
            $mapped_array
        );
    }
}