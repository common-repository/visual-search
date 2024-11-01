<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\SearchButtons;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus;
use Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;

if (! defined('ABSPATH')){
    exit;
}

class SearchButtonsSettings  extends BaseSettings {
    const IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR = 11;
    const IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE = 13;
    const IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE_WHOLE_BAR = 12;
    private $search_bar_option_name;
    private $get_configuration_status;
    private $get_snippet_configuration;
    private $plugin_utils;
    private $widget_js_key;
    private $widget_js_key_variables;
    private $widget_css_key;

    function __construct(GetImpreseeConfigurationStatus $get_configuration_status,
        GetSnippetConfiguration $get_snippet_configuration,
        PluginUtils $plugin_utils, Callbacks $callbacks) {
        parent::__construct(SettingsNames::SEARCH_BUTTONS, $callbacks);
        $this->get_configuration_status = $get_configuration_status;
        $this->get_snippet_configuration = $get_snippet_configuration;
        $this->plugin_utils = $plugin_utils;
        $store = $this->plugin_utils->getStore();
        $this->widget_js_key_variables = 'see-wccs-impresee-widget-variables';
        $this->widget_js_key = 'see-wccs-impresee-widget';
        $this->widget_css_key = 'see-wccs-impresee-widget-css';
        $this->search_bar_option_name = "see_wccs_sb_display_selection_".$store->getStoreName();
        add_action( 'see_wccs_add_css_tab_buttons', array( $this, 'add_css_tab_buttons' ));
        add_action( 'see_wccs_add_js_tab_buttons', array( $this, 'add_js_tab_buttons' ));
        add_shortcode( 'impreseesearch', array( $this, 'impreseesearchVisualSearchShortcodeFunction' ) );
        add_shortcode( 'impreseesearchfullsearchbar', array( $this, 'impreseefullsearchbarShortcodeFunction' ) );
    }

    private function isCatalogReady(){
        $configuration_status_promise = $this->get_configuration_status->execute($this->plugin_utils->getStore());
        $configuration_status_either = $configuration_status_promise->wait();
        $configuration_status = $configuration_status_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        );
        if($configuration_status == NULL || !$configuration_status->catalog_processed_once) {
            return FALSE;
        }
        return TRUE;
    }

    private function getStoredSearchBarDisplayValue($default = SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR){
        $current_selected_display_option = get_option($this->search_bar_option_name, -1);
        if ($current_selected_display_option == -1){
            // we try to get the old version
            $buttons_settings = get_option( 'see_wccs_settings_search_buttons', array() );
            $current_selected_display_option = isset($buttons_settings['display_mode']) ? $buttons_settings['display_mode'] : $default;
            
        }
        return $current_selected_display_option;
    }

    public function get( ){
        $current_selected_display_option = $this->getStoredSearchBarDisplayValue();
        return array(
            $this->config_section_id => $current_selected_display_option
        );
    }

    public function save($data) {
        $sanitized_post = sanitize_post($data, 'db');
        $new_settings = $sanitized_post[$this->config_section_id];
        $new_selected_option = $new_settings['display_mode'];
        $current_selected_display_option = $this->getStoredSearchBarDisplayValue(-1);
        $success = TRUE;
        if ($current_selected_display_option != $new_selected_option){
            $success = update_option($this->search_bar_option_name, $new_selected_option);
        }
        return $success;
    }

    public function saveFormAndRedirect( ){
        $error_update = "";
        $success = $this->save($_POST);
        $page_id = $this->plugin_utils->getPluginPageId();
        $tab = SettingsNames::SEARCH_BUTTONS;
        if(!$success){
            $error_update = urlencode("We could not update your configuration. Please try again later.");
        }
        wp_redirect(admin_url("admin.php?page={$page_id}&tab={$tab}&error={$error_update}"));
    }

    private function parseShortcodeAttributes( $attrs ){
        // we parse the given attributes and assign the default values when needed
        $parsed_attrs = shortcode_atts( array(
            'enable_photo' => true,
            'enable_sketch' => true,
            'disable_sketch' => false,
            'disable_photo' => false,
            'photo_icon' => PluginUtils::IMSEE_DEFAULT_PHOTO_ICON_URL,
            'sketch_icon' => PluginUtils::IMSEE_DEFAULT_SKETCH_ICON_URL,
            'photo_class' => PluginUtils::IMSEE_DEFAULT_PHOTO_BUTTON_CLASS,
            'sketch_class' => PluginUtils::IMSEE_DEFAULT_SKETCH_BUTTON_CLASS,
            'buttons_height' => PluginUtils::IMSEE_DEFAULT_BUTTONS_HEIGHT
        
        ), $attrs );
        $enable_photo = true;
        if ($parsed_attrs['enable_photo'] === 'false') $enable_photo = false;
        $disable_photo = false;
        if ($parsed_attrs['disable_photo'] === 'true') $disable_photo = true;

        $enable_sketch = true;
        if ($parsed_attrs['enable_sketch'] === 'false') $enable_sketch = false;
        $disable_sketch = false;
        if ($parsed_attrs['disable_sketch'] === 'true') $disable_sketch = true;

        return array(
            'use_photo' => $enable_photo && !$disable_photo,
            'use_sketch' => $enable_sketch && !$disable_sketch,
            'photo_icon' => $parsed_attrs['photo_icon'],
            'sketch_icon' => $parsed_attrs['sketch_icon'],
            'photo_class' => $parsed_attrs['photo_class'],
            'sketch_class' => $parsed_attrs['sketch_class'],
            'buttons_height' => $parsed_attrs['buttons_height'], 
        );
    }

    function impreseefullsearchbarShortcodeFunction( $attrs ){
        $current_selected_display_option = $this->getStoredSearchBarDisplayValue();
        if(!$this->isCatalogReady() || $current_selected_display_option != SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE_WHOLE_BAR){
            return;
        }
        $search_icon = PluginUtils::IMSEE_SEARCH_ICON_URL;
        $parsed_attrs = $this->parseShortcodeAttributes($attrs);
        $buttons_height = $parsed_attrs['buttons_height'];
        $photo_button = "";
        $sketch_button = "";
        if ($parsed_attrs['use_photo']){
           $photo_button = ('<div class="' . $parsed_attrs['photo_class'] .'" style="display: inline-block;height:' . $buttons_height . ';"> <img style="height:100%;" src="' .  $parsed_attrs['photo_icon'] . '"></div>');
        }
        if ($parsed_attrs['use_sketch']) {
            $sketch_button = ('<div class="' . $parsed_attrs['sketch_class'] .'" style="display: inline-block;margin-right:10px;height:' . $buttons_height . ';"> <img style="height:100%;" src="' .  $parsed_attrs['sketch_icon'] . '"></div>');
        }
        $impresee_search_bar = <<<EOD
<div class="impresee-form" style="position:relative;padding: 0 10px; margin: 5px 0;">
  <style>
  .impresee-desktop {
    display: none;
  }
  .impresee-form input:focus {
    outline: none;
  }
  @media only screen and (min-width: 768px) {
    .impresee-form,.impresee-form input {
      min-width: 200px;
    }
    .impresee-form {
      width: 100%;
    }
    .impresee-desktop {
      display: inline-block;
    }
  }
  </style>
  <input autocomplete="off" style="text-indent: calc($buttons_height + 15px); width: 100%; background-color: white; border-color: rgb(204, 204, 204);height:calc($buttons_height + 10px);" type="text" name="q">
  <div class="impresee-icon" style="display: flex; position: absolute; right: 5px; top: 5px;">
    $sketch_button
    $photo_button
  </div>
  <button class="impresee-submit-button" type="button" style="position:absolute; left: 12px; top:5px; border:none; background-color:transparent;height:$buttons_height;padding:0;padding-left:5px;">
    <img src="$search_icon" style="height:100%;">
  </button>
</div>
EOD;
        return $impresee_search_bar;
    }



    /*
    * Handler function for the impreseesearch shortcode
    */
    function impreseesearchVisualSearchShortcodeFunction( $attrs ){
        $current_selected_display_option = $this->getStoredSearchBarDisplayValue();
        if(!$this->isCatalogReady() || $current_selected_display_option != SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE){
            return;
        }
        $parsed_attrs = $this->parseShortcodeAttributes($attrs);
        // we generate the html
        $buttons = '<div style="height:' . $parsed_attrs['buttons_height'] . ';display: flex;" class="impresee-icon">';
        if ($parsed_attrs['use_photo']){
            $buttons .= ('<div style="height:100%" class="' . $parsed_attrs['photo_class'] .'" style="display: inline-block;margin-right:10px;"> <img style="height:100%;" src="' .  $parsed_attrs['photo_icon'] . '"></div>');
        }
        if ($parsed_attrs['use_sketch']) {
            $buttons .= ('<div style="height:100%" class="' . $parsed_attrs['sketch_class'] .'" style="display: inline-block;"> <img style="height:100%;" src="' .  $parsed_attrs['sketch_icon'] . '"></div>');
        }
        $buttons .= '</div>';
        return $buttons;
    }


    /*
    * We add the css that places the tab button correctly, only when user has selected to use tab buttons
    */
    public function add_css_tab_buttons(){
        $current_selected_display_option = $this->getStoredSearchBarDisplayValue();
        if(!$this->isCatalogReady() || $current_selected_display_option != SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR){
            return;
        }
        wp_enqueue_style( $this->widget_css_key, $this->plugin_utils->getPluginUrl().'/impresee-creativesearch/includes/assets/impresee-widget/widget.css' );
    }


    /*
    * We add the js that makes the tab button work correctly, only when user has selected to use tab buttons
    */
    public function add_js_tab_buttons() {
        $current_selected_display_option = $this->getStoredSearchBarDisplayValue();
        $snippet_configuration_promise = $this->get_snippet_configuration->execute($this->plugin_utils->getStore());
        $snippet_configuration_either = $snippet_configuration_promise->wait();
        $snippet_configuration = $snippet_configuration_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        ); 
        if(!$this->isCatalogReady() || $current_selected_display_option != SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR || $snippet_configuration == NULL){
            return;
        }
        $search_text = $snippet_configuration->search_by_text_configuration;
        // We only add the widget when people intend to use the visual search only
        if ($search_text->use_text){
            return;
        }
        $general_settings = $snippet_configuration->general_configuration;
        $labels_settings = $snippet_configuration->labels_configuration;
        $default_photo_icon = PluginUtils::IMSEE_DEFAULT_PHOTO_ICON_URL;
        $default_sketch_icon = PluginUtils::IMSEE_DEFAULT_SKETCH_ICON_URL;
        $widget_vars = <<<EOD
<script type="text/javascript">
var impreseeBarColor = "$general_settings->main_color";
var impreseeBarFontColor = "#FFFFFF";
var impreseeIconMainColor = "#FFFFFF";
var impreseeVisualSearchLabel = "$labels_settings->search_by_photo_label";
var impreseeCreativeSearchLabel = "$labels_settings->search_by_sketch_label";
var iconPhoto = "$default_photo_icon";
var iconSketch = "$default_sketch_icon";
var _wseeUsePhoto = true;
var _wseeUseSketch = true;
</script>
EOD;
        wp_register_script( $this->widget_js_key_variables, '' );
        wp_enqueue_script( $this->widget_js_key_variables );
        wp_add_inline_script( $this->widget_js_key_variables, $widget_vars);
        wp_enqueue_script( $this->widget_js_key, $this->plugin_utils->getPluginUrl().'/impresee-creativesearch/includes/assets/impresee-widget/widget.js' );
    }

    /*
    * Output the form content that will shown on the screen
    */
    public function addExtraElementsToSettings( ) {
        $default_photo_icon = PluginUtils::IMSEE_DEFAULT_PHOTO_ICON_URL;
        $default_sketch_icon = PluginUtils::IMSEE_DEFAULT_SKETCH_ICON_URL;
        $default_photo_class = PluginUtils::IMSEE_DEFAULT_PHOTO_BUTTON_CLASS;
        $default_sketch_class = PluginUtils::IMSEE_DEFAULT_SKETCH_BUTTON_CLASS;
        $default_buttons_height = PluginUtils::IMSEE_DEFAULT_BUTTONS_HEIGHT;
        include( 'wc-creative-search-buttons-shortcode-section.php' );
        include( 'wc-creative-searchbar-shortcode-section.php' );

        $use_shortcode = SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE;
        $use_shortcode_whole_bar = SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE_WHOLE_BAR;
        $use_search_bar = SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR;
        $change_button_type_script =  <<<EOT
<script type="text/javascript">
jQuery(document).ready(function($) {
function hide_section_by_value(value){
    switch(value){
    case "$use_shortcode":
        $('#shortcode-container').show();
        $('#shortcode-bar-container').hide();
        break;
    case "$use_shortcode_whole_bar":
        $('#shortcode-container').hide();
        $('#shortcode-bar-container').show();
        break;
    case "$use_search_bar":
        $('#shortcode-container').hide();
        $('#shortcode-bar-container').hide();
        break;
    default:
        break;
    }
}
function on_change_select() {
    var value = this.value;
    hide_section_by_value(value);
}
$('#display_mode').change(on_change_select);
hide_section_by_value($('#display_mode').val());
$(".click.copy").click(function(event){
    var tempElement = $("<input>");
    $("#shortcode").append(tempElement);
    tempElement.val($(this).closest(".click").find("span").text()).select();
    document.execCommand("Copy");
    tempElement.remove();
});
} );
</script>
EOT;
        echo $change_button_type_script;
    }

    /**
    * Add general settings using add_settings_field and register_setting
    */
    public function init_settings() {
        $selected_display_option = $this->getStoredSearchBarDisplayValue();
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'buttons_settings',
                'title'     => 'Search Buttons Settings',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'display_mode',
                'title'     => 'How would you like add the search buttons in your storefront?',
                'callback'  => 'select',
                'section'   => 'buttons_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'display_mode',
                    'options'       => array(
                        SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE_WHOLE_BAR => 'Via shortcode (the whole search bar)',
                        SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE => 'Via shortcode (only visual search buttons)',
                        SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR  => 'In the search bar',
                    ),
                    'current'       => $selected_display_option,
                )
            ),
        );

        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}