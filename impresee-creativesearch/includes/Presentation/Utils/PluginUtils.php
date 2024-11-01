<?php 
    namespace SEE\WC\CreativeSearch\Presentation\Utils;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetStoreInformation;

class PluginUtils {
    const PLUGIN_SEARCH_PAGE_ID = 'woo_impresee_creativesearch_settings_page';
    const PLUGIN_URI_CATALOG = 'impresee/v1/catalog/';
    const IMSEE_SEARCH_ICON_URL = IMSEE_PLUGIN_URL.'/impresee-creativesearch/includes/assets/icons/search.svg';
    const IMSEE_DEFAULT_PHOTO_ICON_URL = IMSEE_PLUGIN_URL.'/impresee-creativesearch/includes/assets/icons/camera_grey.svg';
    const IMSEE_DEFAULT_SKETCH_ICON_URL = IMSEE_PLUGIN_URL.'/impresee-creativesearch/includes/assets/icons/pencil_grey.svg';
    const IMSEE_DEFAULT_PHOTO_BUTTON_CLASS = 'impresee-photo';
    const IMSEE_DEFAULT_SKETCH_BUTTON_CLASS = 'impresee-sketch';
    const IMSEE_DEFAULT_BUTTONS_HEIGHT = '30px';

    private $store;
    private $impresee_search_page_id;

    public function __construct(GetStoreInformation $get_store_info){
        $this->impresee_search_page_id = 'woo_impresee_creativesearch_settings_page';
        $store_promise = $get_store_info->execute();
        $store_either = $store_promise->wait();
        $this->store = $store_either->either(
            function($left){
                return NULL;
            },
            function($store){
                return $store;
            }
        );
    }


    public function getStore(){
        return $this->store;
    }

    public function getPluginUrl(){
        return IMSEE_PLUGIN_URL;
    }

    public function getPluginPath() {
        return IMSEE_PLUGIN_PATH;
    }

    public function getImageUrl(String $imageName){
        return $this->getPluginUrl().'/impresee-creativesearch/includes/assets/images/'.$imageName;
    }

    public function getCssUrl(String $cssFile){
        return $this->getPluginUrl().'/impresee-creativesearch/includes/assets/css/'.$cssFile;
    }

    public function getAssertUrl(String $assetPath){
        return $this->getPluginUrl().'/impresee-creativesearch/includes/assets/'.$assetPath;
    }
    public function getPluginPageId(){
        return PluginUtils::PLUGIN_SEARCH_PAGE_ID;
    }
    public function getUriCatalog(){
        return PluginUtils::PLUGIN_URI_CATALOG;
    }

    public function deleteAllOldAndFrontendOptions(){
        delete_option('see_wccs_sb_display_selection_'.$this->store->getStoreName());
         // Data stored in the old format
        delete_option('see_wccs_impresee_data');
        delete_option('see_wccs_connected_to_impresee');
        delete_option('see_wccs_catalog_processed_once');
        delete_option('see_wccs_verification_code');
        delete_option('see_wccs_settings_search_buttons');
        delete_option('see_wccs_settings_general');
        delete_option('see_wccs_settings_display');
        delete_option('see_wccs_settings_advanced');
        delete_option('see_wccs_entered_before');
        delete_option('see_wccs_catalog_generation_code');
        delete_option('see_wccs_impresee_catalog_code');
        delete_option('see_wccs_owner_code');
        delete_option('see_wccs_photo_app_uuid');
        delete_option('see_wccs_photo_clothing_app_uuid');
        delete_option('see_wccs_sketch_app_uuid');
    }
}