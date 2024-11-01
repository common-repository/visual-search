<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\Christmas;
use Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateHolidayConfiguration;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use Impresee\CreativeSearchBar\Domain\Entities\{HolidayConfiguration, HolidayLabelsConfiguration, HolidayThemeConfiguration};
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;

if (! defined('ABSPATH')){
    exit;
}

class ChristmasSettings extends BaseSettings{
    private $update_holiday_config;
    private $get_holiday_config;
    private $plugin_utils;

    function __construct(
        UpdateHolidayConfiguration $update_holiday_config, 
        GetHolidayConfiguration $get_holiday_config,
        PluginUtils $plugin_utils,
        Callbacks $callbacks
    ) {
        parent::__construct(SettingsNames::CHRISTMAS, $callbacks);
        $this->update_holiday_config =  $update_holiday_config;
        $this->get_holiday_config = $get_holiday_config;
        $this->plugin_utils = $plugin_utils;
    }

    private function getSettingsOrDefault(){
        $config_data_promise = $this->get_holiday_config->execute($this->plugin_utils->getStore());
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
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

                return $empty_configuration;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        return $config_data;
    }

    public function get( ){
        $holiday_config = $this->getSettingsOrDefault();
        $theme = $holiday_config->config_theme;
        $labels = $holiday_config->labels_configuration;
        $config_array = array(
            'is_mode_active' => $theme->is_mode_active,
            'theme' => $theme->theme,
            'automatic_popup' => $theme->automatic_popup,
            'store_logo_url' => $theme->store_logo_url,
            'add_style_to_search_bar' => $theme->add_style_to_search_bar,
            'pop_up_title' => $labels->pop_up_title,
            'pop_up_text' => $labels->pop_up_text,
            'searchbar_placeholder' => $labels->searchbar_placeholder,
            'search_drawing_button' => $labels->search_drawing_button,
            'search_photo_button' => $labels->search_photo_button,
            'search_dropdown_label' => $labels->search_dropdown_label,
            'to_label_letter' => $labels->to_label_letter,
            'from_label_letter' => $labels->from_label_letter,
            'placeholder_message_letter' => $labels->placeholder_message_letter,
            'title_canvas' => $labels->title_canvas,
            'search_button_canvas' => $labels->search_button_canvas,
            'button_in_product_page' => $labels->button_in_product_page,
            'search_results_title' => $labels->search_results_title,
            'results_title_for_text_search' => $labels->results_title_for_text_search,
            'christmas_letter_share_message' => $labels->christmas_letter_share_message,
            'christmas_letter_share' => $labels->christmas_letter_share,
            'christmas_letter_receiver_button' => $labels->christmas_letter_receiver_button,
        );
        $data_array = array(
            $this->config_section_id => $config_array
        );
        return $data_array;
    }

    public function save($data) {
        $sanitized_post = sanitize_post($data, 'db');
        $new_settings = $sanitized_post[$this->config_section_id];
        $theme_config = new HolidayThemeConfiguration;
        $theme_config->is_mode_active = isset($new_settings["is_mode_active"]) ? TRUE : FALSE;
        $theme_config->theme = $new_settings["theme"];
        $theme_config->automatic_popup = isset($new_settings["automatic_popup"]) ? TRUE : FALSE;
        $theme_config->add_style_to_search_bar = isset($new_settings["add_style_to_search_bar"]) ? TRUE : FALSE;
        $theme_config->store_logo_url = isset($new_settings['store_logo_url']) ? stripslashes($new_settings['store_logo_url']) : "";
        $labels_config = new HolidayLabelsConfiguration;
        $labels_config->pop_up_title = stripslashes($new_settings["pop_up_title"]);
        $labels_config->pop_up_text = stripslashes($new_settings["pop_up_text"]);
        $labels_config->searchbar_placeholder = stripslashes($new_settings["searchbar_placeholder"]);
        $labels_config->search_drawing_button = stripslashes($new_settings["search_drawing_button"]);
        $labels_config->search_photo_button = stripslashes($new_settings["search_photo_button"]);
        $labels_config->search_dropdown_label = stripslashes($new_settings["search_dropdown_label"]);
        $labels_config->to_label_letter = stripslashes($new_settings["to_label_letter"]);
        $labels_config->from_label_letter = stripslashes($new_settings["from_label_letter"]);
        $labels_config->placeholder_message_letter = stripslashes($new_settings["placeholder_message_letter"]);
        $labels_config->title_canvas = stripslashes($new_settings["title_canvas"]);
        $labels_config->search_button_canvas = stripslashes($new_settings["search_button_canvas"]);
        $labels_config->button_in_product_page = stripslashes($new_settings["button_in_product_page"]);
        $labels_config->search_results_title = stripslashes($new_settings["search_results_title"]);
        $labels_config->results_title_for_text_search = stripslashes($new_settings["results_title_for_text_search"]);
        $labels_config->christmas_letter_share_message = stripslashes($new_settings["christmas_letter_share_message"]);
        $labels_config->christmas_letter_share = stripslashes($new_settings["christmas_letter_share"]);
        $labels_config->christmas_letter_receiver_button = stripslashes($new_settings["christmas_letter_receiver_button"]);
        $holiday_config = new HolidayConfiguration;
        $holiday_config->config_theme = $theme_config;
        $holiday_config->labels_configuration = $labels_config;
         // Update the data
        $update_promise = $this->update_holiday_config->execute(
            $this->plugin_utils->getStore(), 
            $holiday_config
        );
        $update_either = $update_promise->wait();
        $success = $update_either->either(
            function ($failure) { return FALSE; },
            function ($impresee_data) { return TRUE; }
        );
        return $success;
    }

    public function saveFormAndRedirect( ){
        $success = $this->save($_POST);
        $page_id = $this->plugin_utils->getPluginPageId();
        $tab = SettingsNames::CHRISTMAS;
        $error_update = "";
        if (!$success){
            $error_update = urlencode("We could not update your configuration. Please try again later.");
        }
        wp_redirect(admin_url("admin.php?page={$page_id}&tab={$tab}&error={$error_update}"));
    }

    /**
    * Add general settings using add_settings_field
    */
    public function init_settings() {
        $holiday_config = $this->getSettingsOrDefault();
        $theme_config = $holiday_config->config_theme;
        $labels_config = $holiday_config->labels_configuration;
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'holiday_theme_settings',
                'title'     => 'Christmas theme settings',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'is_mode_active',
                'title'     => 'Enable Christmas mode',
                'callback'  => 'checkbox',
                'section'   => 'holiday_theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'is_mode_active',
                    'current' => $theme_config->is_mode_active,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'theme',
                'title'     => 'Christmas theme',
                'callback'  => 'select',
                'section'   => 'holiday_theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'theme',
                    'options'       => array(
                        HolidayThemeConfiguration::ACCENT_THEME   => 'Santa theme',
                        HolidayThemeConfiguration::NEUTRAL_THEME   => 'Neutral theme',
                    ),
                    'current' => $theme_config->theme,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'automatic_popup',
                'title'     => 'Enable exit intent Christmas PopUp',
                'callback'  => 'checkbox',
                'section'   => 'holiday_theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'automatic_popup',
                    'current' => $theme_config->automatic_popup,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'add_style_to_search_bar',
                'title'     => 'Give your search bar a Christmas style',
                'callback'  => 'checkbox',
                'section'   => 'holiday_theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'add_style_to_search_bar',
                    'current' => $theme_config->add_style_to_search_bar,

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'store_logo_url',
                'title'     => 'Store logo url',
                'callback'  => 'text_input',
                'section'   => 'holiday_theme_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'store_logo_url',
                    'current' => stripslashes($theme_config->store_logo_url),
                ),
            ),
            array(
                'type'      => 'section',
                'id'        => 'holiday_labels_settings',
                'title'     => 'Christmas labels settings',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'pop_up_title',
                'title'     => 'Exit intent PopUp title',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'pop_up_title',
                    'current' => stripslashes($labels_config->pop_up_title),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'pop_up_text',
                'title'     => 'Exit intent PopUp body text',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'pop_up_text',
                    'current' => stripslashes($labels_config->pop_up_text),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'searchbar_placeholder',
                'title'     => 'Placeholder of the Christmas-style search bar',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'searchbar_placeholder',
                    'current' => stripslashes($labels_config->searchbar_placeholder),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_dropdown_label',
                'title'     => 'Search dropdown title',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_dropdown_label',
                    'current' => stripslashes($labels_config->search_dropdown_label),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_drawing_button',
                'title'     => 'Search by drawing button label',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_drawing_button',
                    'current' => stripslashes($labels_config->search_drawing_button),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_photo_button',
                'title'     => 'Search by image button label',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_photo_button',
                    'current' => stripslashes($labels_config->search_photo_button),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'to_label_letter',
                'title'     => 'Label "To" in the Christmas letter',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'to_label_letter',
                    'current' => stripslashes($labels_config->to_label_letter),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'from_label_letter',
                'title'     => 'Label "From" in the Christmas letter',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'from_label_letter',
                    'current' => stripslashes($labels_config->from_label_letter),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'placeholder_message_letter',
                'title'     => 'Message placeholder in the Christmas letter',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'placeholder_message_letter',
                    'current' => stripslashes($labels_config->placeholder_message_letter),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'title_canvas',
                'title'     => 'Search by drawing canvas title',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'title_canvas',
                    'current' => stripslashes($labels_config->title_canvas),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_button_canvas',
                'title'     => 'Search button label in the search by drawing/image canvas',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_button_canvas',
                    'current' => stripslashes($labels_config->search_button_canvas),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'button_in_product_page',
                'title'     => 'Send letter button label (located in the product sheet)',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'button_in_product_page',
                    'current' => stripslashes($labels_config->button_in_product_page),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'search_results_title',
                'title'     => 'Search results title (Search by drawing/image)',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'search_results_title',
                    'current' => stripslashes($labels_config->search_results_title),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'results_title_for_text_search',
                'title'     => 'Search results title (Search by text)',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'results_title_for_text_search',
                    'current' => stripslashes($labels_config->results_title_for_text_search),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'christmas_letter_share_message',
                'title'     => 'Message sent when sharing the Christmas letter',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'christmas_letter_share_message',
                    'current' => stripslashes($labels_config->christmas_letter_share_message),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'christmas_letter_share',
                'title'     => '"Share" label that appears on the Christmas letter',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'christmas_letter_share',
                    'current' => stripslashes($labels_config->christmas_letter_share),
                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'christmas_letter_receiver_button',
                'title'     => 'Label of the "View product" button that shows when opening a shared Christmas zoletter',
                'callback'  => 'text_input',
                'section'   => 'holiday_labels_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'christmas_letter_receiver_button',
                    'current' => stripslashes($labels_config->christmas_letter_receiver_button),
                ),
            ),
        );
        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}
