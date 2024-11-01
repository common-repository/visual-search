<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class CustomCodeModel implements Serializable {
    public $js_add_buttons;
    public $css_style_buttons;
    public $js_after_load_results_code;
    public $js_before_load_results_code;
    public $js_search_failed_code;
    public $js_press_see_all_code;
    public $js_close_text_results_code;
    public $js_on_open_text_dropdown_code;

    public function loadDataFromArray(Array $array){
        if(!array_key_exists('js_add_buttons',$array) ||
            !array_key_exists('css_style_buttons',$array) ||
            !array_key_exists('js_after_load_results_code',$array) ||
            !array_key_exists('js_before_load_results_code',$array) ||
            !array_key_exists('js_search_failed_code',$array) ||
            !array_key_exists('js_press_see_all_code',$array) ||
            !array_key_exists('js_close_text_results_code',$array) ||
            !array_key_exists('js_on_open_text_dropdown_code',$array) ){
            throw new NoDataException;
        }
        $this->js_add_buttons = $array['js_add_buttons'];
        $this->css_style_buttons = $array['css_style_buttons'];
        $this->js_after_load_results_code = $array['js_after_load_results_code'];
        $this->js_before_load_results_code = $array['js_before_load_results_code'];
        $this->js_search_failed_code = $array['js_search_failed_code'];
        $this->js_press_see_all_code = $array['js_press_see_all_code'];
        $this->js_close_text_results_code = $array['js_close_text_results_code'];
        $this->js_on_open_text_dropdown_code = $array['js_on_open_text_dropdown_code'];
    }
    
    public static function fromOldArray(Array $array){
        if(!array_key_exists('js_buttons',$array) || !array_key_exists('css_buttons',$array) || !array_key_exists('js_after_search',$array)){
            throw new NoDataException;
        }

        $model = new CustomCodeModel;
        $model->js_add_buttons = $array['js_buttons'];
        $model->css_style_buttons = $array['css_buttons'];
        $model->js_after_load_results_code = $array['js_after_search'];
        $model->js_before_load_results_code = '';
        $model->js_search_failed_code = '';
        $model->js_press_see_all_code = '';
        $model->js_close_text_results_code = '';
        $model->js_on_open_text_dropdown_code = '';
        return $model;
    }

    public function toArray(){
        return array(
            'js_add_buttons' => $this->js_add_buttons,
            'css_style_buttons' => $this->css_style_buttons,
            'js_after_load_results_code' => $this->js_after_load_results_code,
            'js_before_load_results_code' => $this->js_before_load_results_code,
            'js_search_failed_code' => $this->js_search_failed_code,
            'js_press_see_all_code' => $this->js_press_see_all_code,
            'js_close_text_results_code' => $this->js_close_text_results_code,
            'js_on_open_text_dropdown_code' => $this->js_on_open_text_dropdown_code,
        );
    }
}