<?php
namespace SEE\WC\CreativeSearch\Presentation\Integration\Snippet;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionStatus;
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use Impresee\CreativeSearchBar\Domain\Entities\{HomeDecorMarket, ClothesMarket, OtherMarket, InvalidMarket};
use Impresee\CreativeSearchBar\Domain\Entities\{ImpreseeSearchByPhoto, ImpreseeSearchBySketch, ImpreseeSearchByText, HolidayThemeConfiguration};


if (! defined('ABSPATH')){
    exit;
}

class ImpreseeSnippet {
    private $base_snippet_version = 'v5.5';
    private $santa_snippet_version = 'v4.4-navidad-1';
    private $neutral_snippet_version = 'v4.4-navidad-2';
    private $get_configuration;
    private $get_snippet_configuration;
    private $get_custom_code_config;
    private $get_configuration_status;
    private $get_holiday_configuration;
    private $get_impresee_subscription_status;
    private $snippet_script_key;
    private $utils;

    public function __construct(GetImpreseeConfiguration $get_configuration, 
        GetSnippetConfiguration $get_snippet_configuration, PluginUtils $utils,
        GetCustomCodeConfiguration $get_custom_code_config,
        GetImpreseeConfigurationStatus $get_configuration_status,
        GetHolidayConfiguration $get_holiday_configuration,
        GetImpreseeSubscriptionStatus $get_impresee_subscription_status
    ) {
        $this->button_container_class = 'impresee-christmas-letter-button-container'; 
        add_action('woocommerce_after_add_to_cart_button', function(){
            echo "<div class='$this->button_container_class' style='padding: 10px 0;'></div>";
        });
        $this->get_configuration = $get_configuration;
        $this->get_snippet_configuration = $get_snippet_configuration;
        $this->get_custom_code_config = $get_custom_code_config;
        $this->get_configuration_status = $get_configuration_status;
        $this->get_holiday_configuration = $get_holiday_configuration;
        $this->get_impresee_subscription_status = $get_impresee_subscription_status;
        $this->snippet_script_key = 'see-wccs-impresee-snippet';
        $this->utils = $utils;
    }

    /**
    * Method in charge of generating the snippet, given all the configuration and connection to Impresee
    */
    public function generate_snippet(){
        $has_valid_subscription_promise = $this->get_impresee_subscription_status->execute($this->utils->getStore());
        $has_valid_subscription_either = $has_valid_subscription_promise->wait();
        $has_valid_subscription = $has_valid_subscription_either->either(
            function($failure){ return true; },
            function($subscription_status) { return !$subscription_status->suspended; }
        );

        if (!$has_valid_subscription) {
            $current_user = wp_get_current_user();
            if (user_can( $current_user, 'administrator' )) {
                $html_to_add = '<div style="margin-top: 25px; position:relative; z-index: 9999999; background-color: white;"><span style="font-weight: 900;">To continue using <span style="color: red;">Impresee Creative Search Bar</span> you need to subscribe to a plan.</span><br><span style="font-weight: 900;">Please visit Impresee configuration panel, and click on "Subscribe now" </span></div>';
                echo $html_to_add;
            }
            return;
        }

        $configuration_status_promise = $this->get_configuration_status->execute($this->utils->getStore());
        $configuration_status_either = $configuration_status_promise->wait();
        $configuration_status = $configuration_status_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        );
        if($configuration_status == NULL || !$configuration_status->catalog_processed_once) {
            return;
        }
        $holiday_config_promise = $this->get_holiday_configuration->execute($this->utils->getStore());
        $holiday_config_either = $holiday_config_promise->wait();
        $holiday_config = $holiday_config_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        );
        $impresee_data_promise = $this->get_configuration->execute($this->utils->getStore());
        $impresee_data_either = $impresee_data_promise->wait();
        $impresee_data = $impresee_data_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        );
        $snippet_configuration_promise = $this->get_snippet_configuration->execute($this->utils->getStore());
        $snippet_configuration_either = $snippet_configuration_promise->wait();
        $snippet_configuration = $snippet_configuration_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        ); 
        $custom_code_promise = $this->get_custom_code_config->execute($this->utils->getStore());
        $custom_code_either = $custom_code_promise->wait();
        $custom_code = $custom_code_either->either(
            function($failure){ return NULL; },
            function($impresee_data) { return $impresee_data; }
        ); 
        if ($impresee_data == NULL || $snippet_configuration == NULL || $custom_code == NULL || $holiday_config == NULL){
            // TODO: add logging
            return;
        }
        $applications = $impresee_data->applications;
        $catalog = $impresee_data->catalog;
        if ($catalog->catalog_market instanceOf InvalidMarket){
            // TODO: add logging
            return;
        }
        $use_detection = 'false';
        if($catalog->catalog_market instanceOf ClothesMarket || $catalog->catalog_market instanceOf HomeDecorMarket){
            $use_detection = 'true';
        }
        $impresee_photo;
        $impresee_sketch;
        $impresee_text;
        foreach ($applications as $index => $application) {
            if ($application->search_type instanceOf ImpreseeSearchBySketch){
                $impresee_sketch = $application->code;
            } else if($application->search_type instanceOf ImpreseeSearchByPhoto){
                $impresee_photo = $application->code;
            } else if($application->search_type instanceOf ImpreseeSearchByText) {
                $impresee_text = $application->code;
            }
        }
        if (!$impresee_text){
            $impresee_text = $impresee_photo;
        }
        if ( !$impresee_photo || !$impresee_sketch || !$impresee_text ){
            //Do not add script if the apps don't exist
            return "";
        }
        $holiday_theme = $holiday_config->config_theme;
        $holiday_labels = $holiday_config->labels_configuration;
        $general_settings = $snippet_configuration->general_configuration;
        $labels_settings = $snippet_configuration->labels_configuration;
        $search_by_text = $snippet_configuration->search_by_text_configuration;

        $snippet_version = $this->base_snippet_version;
        //Start: Holiday Theme Config
        if($holiday_theme->is_mode_active){
            switch ($holiday_theme->theme) {
                case HolidayThemeConfiguration::ACCENT_THEME:
                    $snippet_version = $this->santa_snippet_version;
                    break;
                case HolidayThemeConfiguration::NEUTRAL_THEME:
                    $snippet_version = $this->neutral_snippet_version;
                    break;
                default:
                    $snippet_version = $this->santa_snippet_version;
                    break;
            }
        }

        $add_christmas_button = $holiday_theme->is_mode_active ? 'true' : 'false';
        $enable_christmas_popup = $holiday_theme->is_mode_active && $holiday_theme->automatic_popup ? 'true' : 'false';
        $change_search_bar_christmas_style = $holiday_theme->is_mode_active && $holiday_theme->add_style_to_search_bar ? 'true' : 'false';
        $store_url_logo = $holiday_theme->store_logo_url; 
        //End: Holiday Theme Config

        $load_after_page_render = $general_settings->load_after_page_render ? 'true' : 'false';
        $add_search_data_to_url = $general_settings->add_search_data_to_url ? 'true' : 'false';
        $disable_crop = $general_settings->disable_image_crop ? 'true' : 'false';
        $images_only_loaded_from_camera = $general_settings->images_only_loaded_from_camera ? 'true' : 'false';
        $use_photo_search = $general_settings->use_photo_search ? 'true' : 'false';
        $use_sketch_search = $general_settings->use_sketch_search ? 'true' : 'false';

        $use_instant_full_search = $search_by_text->use_instant_full_search ? 'true' : 'false';
        $use_text = $search_by_text->use_text ? 'true' : 'false';
        $use_floating_search_bar_button = $search_by_text->use_floating_search_bar_button ? 'true' : 'false';
        $use_search_suggestions = $search_by_text->use_search_suggestions ? 'true' : 'false';
        $mobile_instant_as_grid = $search_by_text->mobile_instant_as_grid ? 'true' : 'false';
        // Obtained directly from woo
        $currency_pos = get_option( 'woocommerce_currency_pos' );
        $currency_symbol_at_the_end = strpos($currency_pos, 'right') !== false ? 'true' : 'false';
        $symbol_at_start_space = strpos($currency_pos, 'left_space') !== false ? ' ' : '';
        $symbol_at_end_space = strpos($currency_pos, 'right_space') !== false ? ' ' : '';
        $thousands_separator = wc_get_price_thousand_separator();
        $decimal_separator = wc_get_price_decimal_separator();
        $price_fraction_digit_number = wc_get_price_decimals();
        $currency_symbol = $symbol_at_end_space . mb_convert_encoding(get_woocommerce_currency_symbol(), 'UTF-8', 'HTML-ENTITIES') . $symbol_at_start_space;

        $search_photo_button = $holiday_theme->is_mode_active ? $holiday_labels->search_photo_button : $labels_settings->search_by_photo_label;
        $search_sketch_button = $holiday_theme->is_mode_active ? $holiday_labels->search_drawing_button : $labels_settings->search_by_sketch_label;
        $search_button_label = $holiday_theme->is_mode_active ? $holiday_labels->search_button_canvas : $labels_settings->search_button_label;
        $search_results_title = $holiday_theme->is_mode_active ? $holiday_labels->search_results_title : $labels_settings->search_results_title;
        $results_title_for_text_search = $holiday_theme->is_mode_active ? $holiday_labels->results_title_for_text_search : $labels_settings->result_title_search_by_text;

        $woocommerce_permalinks = get_option('woocommerce_permalinks');
        $product_base_permalink = isset($woocommerce_permalinks['product_base']) ? $woocommerce_permalinks['product_base'] : '/product/'; 
        $clean_permalink = trim($product_base_permalink, "/");
        $clean_permalink_no_cat = str_replace('%product_cat%', "([^/])*", $clean_permalink);
        $product_url_regex = "(/$clean_permalink_no_cat/([^/])*/)|(product=.*)";
        $hide_icons = '';
        if ($holiday_theme->is_mode_active && $holiday_theme->add_style_to_search_bar){
            $hide_icons = '.impresee-icon,.impresee-submit-button {display:none !important;}';
        }
        $snippet_base_url = SEE_WCCS()->base_snippet;
        $snippet_string = <<<EOD
if (typeof(impresee) == "undefined") {
    function _wsseDocumentReady(fn) {
      // see if DOM is already available
      if (
        document.readyState === "complete" ||
        document.readyState === "interactive"
      ) {
        // call on next available tick
        setTimeout(fn, 1);
      } else {
        document.addEventListener("DOMContentLoaded", fn);
      }
    }
    function impreseeRegisterData(items, eventType, action){
        var urlParams = new URLSearchParams(window.location.search || '?' + window.location.hash.split('?')[1]);
        var endpoint = 'https://api.impresee.com/ImpreseeSearch/api/v3/search/register_woocommerce/';
        var storeCode = window._wssee_store_url;
        var appUuid = window._wssee_store_app_code;
        var from_impresee_text = urlParams.get('source_impresee') || "";
        var from_impresee_visual = urlParams.get('seecd') || "";
        var id = [];
        var price = [];
        var quantity = [];
        var sku = [];
        var varId = [];
        for (var key in Array.from(items)){
            var item = items[key];
            id.push(item.id_product || '');
            price.push(item.price || '');
            quantity.push(item.quantity);
            sku.push(item.sku || '');
            varId.push(item.variation_id || '');
        }

        // View product data
        var data = 'store=' +  encodeURIComponent(storeCode);
        data += '&a=' + encodeURIComponent(action);
        data += '&evt=' + encodeURIComponent(eventType);
        data += '&fi=' + encodeURIComponent(from_impresee_text);
        data += '&fiv=' + encodeURIComponent(from_impresee_visual);
        data += '&pid=' + encodeURIComponent(id.join('|'));
        data += '&p=' + encodeURIComponent(price.join('|'));
        data += '&qty=' + encodeURIComponent(quantity.join('|'));
        data += '&sku=' + encodeURIComponent(sku.join('|'));
        data += '&vid=' + encodeURIComponent(varId.join('|'));
        
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.withCredentials = true;
        xmlHttp.open( "GET", endpoint + appUuid + '?' + data, true );
        xmlHttp.send( null );
    }
    function see_delegate(el, evt, sel, handler) {
        el.addEventListener(evt, function(event) {
            var t = event.target;
            while (t && t !== this) {
                if (t.matches(sel)) {
                    handler.call(t, event);
                }
                t = t.parentNode;
            }
        });
    }
    function see_find(outers, selector) {
        var found_elements = [];

        for(var i=0; i<outers.length; i++) {
            var elements_in_outer = outers[i].querySelectorAll(selector);
            elements_in_outer = Array.prototype.slice.call(elements_in_outer);
            found_elements = found_elements.concat(elements_in_outer);
        } 
        return found_elements;  
    }
    _wsseDocumentReady(function(){
        if(document.querySelector('.single-product') !== null){
            data = {};
            thisbutton = document.querySelector('.single_add_to_cart_button'),
            form = thisbutton ? thisbutton.closest('form.cart'): null;
            id = thisbutton ? thisbutton.value : null;
            skuElement = document.querySelector('.sku');
            data.sku = '';
            if (skuElement){
                data.sku = skuElement.innerHTML.trim();
            }
            let qty = see_find(form ? [form]: [], 'input[name=quantity]')[0];
            data.quantity = (qty ? qty.value : 1) || 1;
            let idProduct = see_find(form ? [form]: [],'input[name=product_id]')[0];
            data.id_product = (idProduct ? idProduct.value : id) || id;
            let varId = see_find(form ? [form]: [],'input[name=variation_id]');
            data.variation_id = (varId ? varId.value : 0) || 0;
            data.price = 0;
            
            if (window._wssee_store_platform === 'woocommerce'){
                impreseeRegisterData([data], 'woocommerce_1_0', "VIEW_PRODUCT");
            }
        }
    });
    see_delegate(document, 'click', '.single_add_to_cart_button', function(e) {
        data = {};
        thisbutton = e.target,
        form = thisbutton ? thisbutton.closest('form.cart'): null;
        id = thisbutton ? thisbutton.value: null,
        data.sku = '';
        let qty = see_find(form ? [form]: [], 'input[name=quantity]')[0];
        data.quantity = (qty ? qty.value : 1) || 1;
        let idProduct = see_find(form ? [form]: [],'input[name=product_id]')[0];
        data.id_product = (idProduct ? idProduct.value : id) || id;
        let varId = see_find(form ? [form]: [],'input[name=variation_id]');
        data.variation_id = (varId ? varId.value : 0) || 0;
        data.price = 0;
        
        if (window._wssee_store_platform === 'woocommerce'){
            impreseeRegisterData([data], 'woocommerce_1_0', "ADD_TO_CART");
        }
    });
    
    window._wssee_store_url = window.location.origin;
    window._wssee_store_app_code = "$impresee_text";
    window._wssee_store_platform="woocommerce";
    _wsseDocumentReady(function(  ) {
        if($use_text) {
            var searchForms = document.querySelectorAll('form.search-form,form[role="search"]');
            for (searchForm of Array.from(searchForms)) {
                searchForm.addEventListener('submit',function(event){event.stopPropagation();event.preventDefault();return false}, true);
            }
        }
    });

    window._wssee = window._wssee || [];
    window._wssee.push({
        setup: {
          // start christmas
          addButtonToProductPage: $add_christmas_button,
          enableChristmasPopUp: $enable_christmas_popup,
          enableChristmasStyleSearchBar: $change_search_bar_christmas_style,
          // end christmas
          loadAfterPageRender: $load_after_page_render,
          useDetection: $use_detection,
          addSearchDataToUrl: $add_search_data_to_url,
          photoButton: "impresee-photo",
          sketchButton: "impresee-sketch",
          photoApp: "$impresee_photo",
          sketchApp: "$impresee_sketch",
          useSearchByPhoto: $use_photo_search,
          useSearchBySketch: $use_sketch_search,
          mainColor: "$general_settings->main_color",
          disableImageCrop: $disable_crop,
          onlyCameraAsInput: $images_only_loaded_from_camera,
          numberFractionDigits: $price_fraction_digit_number,
          decimalSeparator: "$decimal_separator",
          thousandsSeparator: "$thousands_separator",
          currencySymbolAtTheEnd: $currency_symbol_at_the_end,
          colorOnSale: "$general_settings->on_sale_label_color",
          container: "$general_settings->container_selector",
          regexProductPage: "$product_url_regex",
          productPageButtonContainer: ".$this->button_container_class",
          afterLoadResults: function (searchType, query) { $custom_code->js_after_load_results_code },
          beforeLoadResults: function (searchType, query) { $custom_code->js_before_load_results_code },
          onSearchFailed: function () { $custom_code->js_search_failed_code },
          storeImage: "$store_url_logo",
          searchByPhotoIconURL: "$general_settings->search_by_photo_icon_url",
          searchBySketchIconURL: "$general_settings->search_by_sketch_icon_url",
        },
        texts: {
            searchResultsTitle: "$search_results_title",
            searchButtonLabel: "$search_button_label",
            oops: "$labels_settings->oops_exclamation",
            errorTitle: "$labels_settings->error_title",
            errorDescription: "$labels_settings->error_message",
            dragAndDropImageTitle: "$labels_settings->drag_and_drop_image_title",
            dragAndDropImageMessage: "$labels_settings->drag_and_drop_image_body",
            customSelectionSearchLabel: "$labels_settings->custom_crop_label",
            startWriting: "$labels_settings->start_writing_label",
            currencySymbol: "$currency_symbol",
            searchByPhoto: "$search_photo_button",
            searchBySketch: "$search_sketch_button",
            seeAllResults: "$labels_settings->see_all_results_label",
            noMatchingResult: "$labels_settings->no_matching_results",
            onSale: "$labels_settings->on_sale_label",
            resultsTitleforTextSearch: "$results_title_for_text_search",
            numberResultsTitle: '$labels_settings->number_of_results_label_desktop',
            resultsTitleForMobile: '$labels_settings->number_of_results_label_mobile',
            filtersTitle: "$labels_settings->filters_title_label_mobile",
            clearFilters: "$labels_settings->clear_filters_label",
            sortBy: "$labels_settings->sort_by_label",
            applyFilters: "$labels_settings->apply_filters_label_mobile",
            tryAgainWhenNoResults: "$labels_settings->try_searching_again_label",
            // Start: Christmas Popup
            popUpTitle: "$holiday_labels->pop_up_title",
            popUpText: "$holiday_labels->pop_up_text",
            // End: Christmas Popup
            // Start: Search dropdown
            dropDownTitle: "$holiday_labels->search_dropdown_label",
            // End: Search dropdown
            // Start: Search bar
            searchBarPlaceholder: "$holiday_labels->searchbar_placeholder",
            // End: Search bar
            // Start: Product page
            productPageButtonText: "$holiday_labels->button_in_product_page",
            // End: Product page
            // Start: Christmas letter
            christmasLetterShareMessage: "$holiday_labels->christmas_letter_share_message",
            christmasLetterFrom: "$holiday_labels->from_label_letter",
            christmasLetterTo: "$holiday_labels->to_label_letter",
            christmasLetterPlaceholder: "$holiday_labels->placeholder_message_letter",
            christmasLetterShare: "$holiday_labels->christmas_letter_share",
            christmasLetterReceiverButton: "$holiday_labels->christmas_letter_receiver_button",
            // End: Christmas letter
            // Start: Canvas
            drawingCanvasTitle: "$holiday_labels->title_canvas",
            searchSuggestions: "$labels_settings->search_suggestions_label",
            searchRecommendedProducts: "$labels_settings->search_recommendations_label",
            // End: Canvas
        },
        searchByText: {
            instantFull: $use_instant_full_search,
            displayMobileDropdownAsGrid: $mobile_instant_as_grid,
            useSearchSuggestions: $use_search_suggestions,
            useText: $use_text,
            searchDelayMilis: $search_by_text->search_delay_millis,
            fullTextSearchContainerSelector: "$search_by_text->full_text_search_results_container",
            computeTopFromElement: "$search_by_text->compute_results_top_position_from",
            searchBarSelector: "$search_by_text->search_bar_selector",
            useButtonAsSearchBar: $use_floating_search_bar_button,
            textSearchApp: "$impresee_text",
            extraPressSeeAllButton: ".impresee-search-button",
            buttonSearchBarLocation: "$search_by_text->floating_button_location",
            pressSeeAll: function() { $custom_code->js_press_see_all_code },
            onCloseResults: function() { $custom_code->js_close_text_results_code },
            onOpenDropDownResults: function() { $custom_code->js_on_open_text_dropdown_code }
        }
    });
    var impresee = document.createElement("script");
    impresee.type = "text/javascript";
    impresee.async = true;
    impresee.src = "$snippet_base_url/snippet/$snippet_version/impresee.min.js";
    var first = document.getElementsByTagName("script")[0];
    first.parentNode.insertBefore(impresee, first);
}
EOD;
        wp_register_script( $this->snippet_script_key, '' );
        wp_enqueue_script( $this->snippet_script_key );
        wp_add_inline_script( $this->snippet_script_key, $snippet_string);
        wp_register_style( $this->snippet_script_key.'-css', '' );
        wp_enqueue_style( $this->snippet_script_key.'-css' );
        wp_add_inline_style( $this->snippet_script_key.'-css', ".ImpreseeOnProductButton{margin-top:10px !important;} ".$hide_icons);  
    }

}
