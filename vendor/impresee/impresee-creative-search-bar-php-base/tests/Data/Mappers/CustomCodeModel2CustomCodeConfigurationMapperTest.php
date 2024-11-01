<?php
    use PHPUnit\Framework\TestCase;
    use Impresee\CreativeSearchBar\Data\Mappers\CustomCodeModel2CustomCodeConfigurationMapper;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;

class CustomCodeModel2CustomCodeConfigurationMapperTest extends Testcase {
    private $mapper;

    protected function setUp(): void{
        $this->mapper = new CustomCodeModel2CustomCodeConfigurationMapper;
    }

    public function testMapToEntity(){
        $expected_configuration = new CustomCodeConfiguration;
        $expected_configuration->js_add_buttons = 'let variable = 1;';
        $expected_configuration->css_style_buttons = '.button{}';
        $expected_configuration->js_after_load_results_code = "console.log('running');";
        $expected_configuration->js_before_load_results_code = "console.log('running');";
        $expected_configuration->js_search_failed_code = "console.log('running');";
        $expected_configuration->js_press_see_all_code = "console.log('running');";
        $expected_configuration->js_close_text_results_code = "console.log('running');";
        $expected_configuration->js_on_open_text_dropdown_code = "console.log('running');";
        $model = new CustomCodeModel;
        $model->js_add_buttons = 'let variable = 1;';
        $model->css_style_buttons = '.button{}';
        $model->js_after_load_results_code = "console.log('running');";
        $model->js_before_load_results_code = "console.log('running');";
        $model->js_search_failed_code = "console.log('running');";
        $model->js_press_see_all_code = "console.log('running');";
        $model->js_close_text_results_code = "console.log('running');";
        $model->js_on_open_text_dropdown_code = "console.log('running');";
        $result = $this->mapper->mapFrom($model);
        $this->assertEquals(
            $result,
            $expected_configuration
        );
    }

    public function testMapToModel(){
        $expected_model = new CustomCodeModel;
        $expected_model->js_add_buttons = 'let variable = 1;';
        $expected_model->css_style_buttons = '.button{}';
        $expected_model->js_after_load_results_code = "console.log('running');";
        $expected_model->js_before_load_results_code = "console.log('running');";
        $expected_model->js_search_failed_code = "console.log('running');";
        $expected_model->js_press_see_all_code = "console.log('running');";
        $expected_model->js_close_text_results_code = "console.log('running');";
        $expected_model->js_on_open_text_dropdown_code = "console.log('running');";

        $configuration = new CustomCodeConfiguration;
        $configuration->js_add_buttons = 'let variable = 1;';
        $configuration->css_style_buttons = '.button{}';
        $configuration->js_after_load_results_code = "console.log('running');";
        $configuration->js_before_load_results_code = "console.log('running');";
        $configuration->js_search_failed_code = "console.log('running');";
        $configuration->js_press_see_all_code = "console.log('running');";
        $configuration->js_close_text_results_code = "console.log('running');";
        $configuration->js_on_open_text_dropdown_code = "console.log('running');";
        
        $result = $this->mapper->mapTo($configuration);
        $this->assertEquals(
            $result,
            $expected_model
        );
    }
}