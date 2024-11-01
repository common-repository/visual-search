<?php
namespace Impresee\CreativeSearchBar\Domain\Entities;

class HolidayThemeConfiguration {
    const ACCENT_THEME = 'ACCENT_THEME';
    const NEUTRAL_THEME = 'NEUTRAL_THEME';
    public $is_mode_active;
    public $theme;
    public $automatic_popup;
    public $add_style_to_search_bar;
    public $store_logo_url;
}