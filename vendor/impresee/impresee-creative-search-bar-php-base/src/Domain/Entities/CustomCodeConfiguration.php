<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarConfiguration;

class CustomCodeConfiguration implements SearchBarConfiguration {
    public $js_add_buttons;
    public $css_style_buttons;
    public $js_after_load_results_code;
    public $js_before_load_results_code;
    public $js_search_failed_code;
    public $js_press_see_all_code;
    public $js_close_text_results_code;
    public $js_on_open_text_dropdown_code;
}