<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;

class HolidayConfigurationModel implements Serializable{
    const ACCENT = 1;
    const NEUTRAL = 2;
    public $is_mode_active;
    public $theme;
    public $automatic_popup;
    public $add_style_to_search_bar;
    public $store_logo_url;
    public $pop_up_title;
    public $pop_up_text;
    public $searchbar_placeholder;
    public $search_drawing_button;
    public $search_photo_button;
    public $search_dropdown_label;
    public $to_label_letter;
    public $from_label_letter;
    public $placeholder_message_letter;
    public $title_canvas;
    public $search_button_canvas;
    public $button_in_product_page;
    public $search_results_title;
    public $results_title_for_text_search;
    public $christmas_letter_share_message;
    public $christmas_letter_share;
    public $christmas_letter_receiver_button;


    public function toArray(){
        $holiday_data = array(
            'is_mode_active' => $this->is_mode_active,
            'theme' => $this->theme,
            'automatic_popup' => $this->automatic_popup,
            'add_style_to_search_bar' => $this->add_style_to_search_bar,
            'store_logo_url' => $this->store_logo_url,
            'pop_up_title' => $this->pop_up_title,
            'pop_up_text' => $this->pop_up_text,
            'searchbar_placeholder' => $this->searchbar_placeholder,
            'search_drawing_button' => $this->search_drawing_button,
            'search_photo_button' => $this->search_photo_button,
            'search_dropdown_label' => $this->search_dropdown_label,
            'to_label_letter' => $this->to_label_letter,
            'from_label_letter' => $this->from_label_letter,
            'placeholder_message_letter' => $this->placeholder_message_letter,
            'title_canvas' => $this->title_canvas,
            'search_button_canvas' => $this->search_button_canvas,
            'button_in_product_page' => $this->button_in_product_page,
            'search_results_title' => $this->search_results_title,
            'results_title_for_text_search' => $this->results_title_for_text_search,
            'christmas_letter_share_message' => $this->christmas_letter_share_message,
            'christmas_letter_share' => $this->christmas_letter_share,
            'christmas_letter_receiver_button' => $this->christmas_letter_receiver_button
        );
        return $holiday_data;
    }

    public function loadDataFromArray(Array $impresee_data){
        if (
            !isset($impresee_data['is_mode_active']) || !isset($impresee_data['theme']) ||
            !isset($impresee_data['automatic_popup']) || !isset($impresee_data['add_style_to_search_bar']) ||
            !isset($impresee_data['pop_up_title']) || !isset($impresee_data['pop_up_text']) ||
            !isset($impresee_data['searchbar_placeholder']) || !isset($impresee_data['search_drawing_button']) ||
            !isset($impresee_data['search_photo_button']) || !isset($impresee_data['search_dropdown_label']) ||
            !isset($impresee_data['to_label_letter']) || !isset($impresee_data['from_label_letter']) ||
            !isset($impresee_data['placeholder_message_letter']) || !isset($impresee_data['title_canvas']) ||
            !isset($impresee_data['search_button_canvas']) || !isset($impresee_data['button_in_product_page']) ||
            !isset($impresee_data['search_results_title']) || !isset($impresee_data['results_title_for_text_search']) ||
            !isset($impresee_data['christmas_letter_share_message']) || !isset($impresee_data['christmas_letter_share']) ||
            !isset($impresee_data['christmas_letter_receiver_button']) 
        ){
            throw new NoDataException;
        }
        $this->is_mode_active = $impresee_data['is_mode_active'];
        $this->theme = $impresee_data['theme'];
        $this->automatic_popup = $impresee_data['automatic_popup'];
        $this->store_logo_url = isset($impresee_data['store_logo_url']) ? $impresee_data['store_logo_url'] : ""; 
        $this->add_style_to_search_bar = $impresee_data['add_style_to_search_bar'];
        $this->pop_up_title = $impresee_data['pop_up_title'];
        $this->pop_up_text = $impresee_data['pop_up_text'];
        $this->searchbar_placeholder = $impresee_data['searchbar_placeholder'];
        $this->search_drawing_button = $impresee_data['search_drawing_button'];
        $this->search_photo_button = $impresee_data['search_photo_button'];
        $this->search_dropdown_label = $impresee_data['search_dropdown_label'];
        $this->to_label_letter = $impresee_data['to_label_letter'];
        $this->from_label_letter = $impresee_data['from_label_letter'];
        $this->placeholder_message_letter = $impresee_data['placeholder_message_letter'];
        $this->title_canvas = $impresee_data['title_canvas'];
        $this->search_button_canvas = $impresee_data['search_button_canvas'];
        $this->button_in_product_page = $impresee_data['button_in_product_page'];
        $this->search_results_title = $impresee_data['search_results_title'];
        $this->results_title_for_text_search = $impresee_data['results_title_for_text_search'];
        $this->christmas_letter_share_message = $impresee_data['christmas_letter_share_message'];
        $this->christmas_letter_share = $impresee_data['christmas_letter_share'];
        $this->christmas_letter_receiver_button = $impresee_data['christmas_letter_receiver_button'];
    }
}