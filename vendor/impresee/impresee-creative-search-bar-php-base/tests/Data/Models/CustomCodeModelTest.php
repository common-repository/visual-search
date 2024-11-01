<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class CustomCodeModelTest extends TestCase {

    public function testMapFromArrayCorrectly(){
        $data_array = array(
            'js_add_buttons' => "console.log('buttons')",
            'css_style_buttons' => ".buttons{}",
            'js_after_load_results_code' => "console.log('after')",
            'js_before_load_results_code' => "console.log('before')",
            'js_search_failed_code' => "console.log('error')",
            'js_press_see_all_code' => "console.log('see all')",
            'js_close_text_results_code' => "console.log('close results')",
            'js_on_open_text_dropdown_code' => "console.log('open dropdown')"
        );
        $expected_model = new CustomCodeModel;
        $expected_model->js_add_buttons = "console.log('buttons')";
        $expected_model->css_style_buttons = ".buttons{}";
        $expected_model->js_after_load_results_code = "console.log('after')";
        $expected_model->js_before_load_results_code = "console.log('before')";
        $expected_model->js_search_failed_code = "console.log('error')";
        $expected_model->js_press_see_all_code = "console.log('see all')";
        $expected_model->js_close_text_results_code = "console.log('close results')";
        $expected_model->js_on_open_text_dropdown_code = "console.log('open dropdown')";
        $empty_model = new CustomCodeModel;
        $empty_model->loadDataFromArray($data_array);
        $this->assertEquals(
            $empty_model,
            $expected_model
        );
    }

    public function testMapFromArrayFailsBecauseOfIncompleteData(){
        $data_array = array(
        );
        $this->expectException(NoDataException::class);
        $empty_model = new CustomCodeModel;
        $empty_model->loadDataFromArray($data_array);
    }

    public function testMapFromOldArrayCorrectly(){
        $data_array = array(
            'js_buttons' => "console.log('buttons')",
            'css_buttons' => ".buttons{}",
            'js_after_search' => "console.log('after')",
        );
        $expected_model = new CustomCodeModel;
        $expected_model->js_add_buttons = "console.log('buttons')";
        $expected_model->css_style_buttons = ".buttons{}";
        $expected_model->js_after_load_results_code = "console.log('after')";
        $expected_model->js_before_load_results_code = "";
        $expected_model->js_search_failed_code = "";
        $expected_model->js_press_see_all_code = "";
        $expected_model->js_close_text_results_code = "";
        $expected_model->js_on_open_text_dropdown_code = "";
        $mapped_result = CustomCodeModel::fromOldArray($data_array);
        $this->assertEquals(
            $mapped_result,
            $expected_model
        );
    }
    public function testMapFromArrayOldFormatFailsBecauseOfIncompleteData(){
        $data_array = array(
        );
        $this->expectException(NoDataException::class);
        $mapped_result = CustomCodeModel::fromOldArray($data_array);
    }

    public function testToArray(){
        $model = new CustomCodeModel;
        $model->js_add_buttons = "console.log('buttons')";
        $model->css_style_buttons = ".buttons{}";
        $model->js_after_load_results_code = "console.log('after')";
        $model->js_before_load_results_code = "console.log('before')";
        $model->js_search_failed_code = "console.log('error')";
        $model->js_press_see_all_code = "console.log('see all')";
        $model->js_close_text_results_code = "console.log('close results')";
        $model->js_on_open_text_dropdown_code = "console.log('open dropdown')";
        $expected_array = array(
            'js_add_buttons' => "console.log('buttons')",
            'css_style_buttons' => ".buttons{}",
            'js_after_load_results_code' => "console.log('after')",
            'js_before_load_results_code' => "console.log('before')",
            'js_search_failed_code' => "console.log('error')",
            'js_press_see_all_code' => "console.log('see all')",
            'js_close_text_results_code' => "console.log('close results')",
            'js_on_open_text_dropdown_code' => "console.log('open dropdown')"
        );
        $mapped_array = $model->toArray();
        $this->assertEquals(
            $mapped_array,
            $expected_array
        );
    }
}