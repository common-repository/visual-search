<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;

class ImpreseeSnippetSearchByTextConfiguration  {
    const BOTTOM_LEFT = 'BOTTOM_LEFT';
    const TOP_LEFT = 'TOP_LEFT';
    const MIDDLE_LEFT = 'MIDDLE_LEFT';
    const BOTTOM_RIGHT = 'BOTTOM_RIGHT';
    const TOP_RIGHT = 'TOP_RIGHT';
    const MIDDLE_RIGHT = 'MIDDLE_RIGHT';
    
    public $use_text;
    public $search_delay_millis;
    public $full_text_search_results_container;
    public $compute_results_top_position_from;
    public $use_instant_full_search;
    public $use_floating_search_bar_button;
    public $floating_button_location;
    public $search_bar_selector;
    public $use_search_suggestions;
    public $mobile_instant_as_grid;
}