<?php
namespace SEE\WC\CreativeSearch\Presentation\Utils;
use Impresee\CreativeSearchBar\Core\Constants\StorageCodes;

class WooStorageCodes implements StorageCodes {
    const LOCAL_IMPRESEE_OLD_DATA = 'see_wccs_impresee_data';
    const LOCAL_IMPRESEE_DATA_PREFIX = 'see_wccs_impresee_data_';
    const STORE_CATALOG_CODE_PREFIX = 'see_wccs_store_catalog_code_';
    const STORE_FINISHED_ONBOARDING = 'see_wccs_store_finished_onboarding_';
    const LOCAL_INDEXATION_CONFIG_DATA_PREFIX = 'see_wccs_index_';
    const LOCAL_GENERAL_SETTINGS_KEY = 'see_wccs_settings_general';
    const LOCAL_CUSTOM_CODE_CONFIG_DATA_PREFIX = 'see_wccs_cc_';
    const LOCAL_ADVANCED_SETTINGS_KEY = 'see_wccs_settings_advanced';
    const LOCAL_SEARCH_BAR_DISPLAY_CONFIG_DATA_PREFIX = 'see_wccs_sb_display_';
    const LOCAL_BUTTONS_SETTINGS_KEY = 'see_wccs_settings_search_buttons';
    const LOCAL_SNIPPET_CONFIG_DATA_PREFIX = 'see_wccs_snippet_';
    const LOCAL_SNIPPET_SETTINGS_KEY = 'see_wccs_settings_display';
    const LOCAL_HOLIDAY_CONFIG = 'see_wccs_holiday_config_';
    const LOCALE = 'LOCALE';
    const SITE_TITLE = 'SITE_TITLE';
    const HOME = 'home';
    const ADMIN_EMAIL = 'admin_email';
    const TIMEZONE = 'timezone_string';
    const GMT_OFFSET = 'gmt_offset';
    const PLUGIN_VERSION_STORAGE = 'see_wccs_installed_plugin_version_';

    public function getOldImpreseeLocalDataKey(){
        return WooStorageCodes::LOCAL_IMPRESEE_OLD_DATA;
    }
    public function getImpreseeLocalDataKeyPrefix(){
        return WooStorageCodes::LOCAL_IMPRESEE_DATA_PREFIX;
    }
    public function getStoreCatalogCodeKeyPrefix(){
        return WooStorageCodes::STORE_CATALOG_CODE_PREFIX;
    }
    public function getStoreFinishedOnboardingKeyPrefix(){
        return WooStorageCodes::STORE_FINISHED_ONBOARDING;
    }
    public function getLocalIndexationConfigKeyPrefix(){
        return WooStorageCodes::LOCAL_INDEXATION_CONFIG_DATA_PREFIX;
    }
    public function getOldLocalGeneralSettingsKey(){
        return WooStorageCodes::LOCAL_GENERAL_SETTINGS_KEY;
    }
    public function getLocalCustomCodeSettingsKeyPrefix(){
        return WooStorageCodes::LOCAL_CUSTOM_CODE_CONFIG_DATA_PREFIX;
    }
    public function getOldLocalAdvancedSettingsKey(){
        return WooStorageCodes::LOCAL_ADVANCED_SETTINGS_KEY;
    }
    public function getLocalSearchBarDsiplayConfigKeyPrefix(){
        return WooStorageCodes::LOCAL_SEARCH_BAR_DISPLAY_CONFIG_DATA_PREFIX;
    }
    public function getOldButtonsSettingsKey(){
        return WooStorageCodes::LOCAL_BUTTONS_SETTINGS_KEY;
    }
    public function getOldSnippetConfigKey(){
        return WooStorageCodes::LOCAL_SNIPPET_SETTINGS_KEY;
    }
    public function getLocalSnippetSettingsKeyPrefix(){
        return WooStorageCodes::LOCAL_SNIPPET_CONFIG_DATA_PREFIX;
    }
    public function getLocalHolidayConfigKeyPrefix(){
        return WooStorageCodes::LOCAL_HOLIDAY_CONFIG;
    }
    public function getLocaleKey(){
        return WooStorageCodes::LOCALE;
    }
    public function getSiteTitleKey(){
        return WooStorageCodes::SITE_TITLE;
    }
    public function getSiteHomeKey(){
        return WooStorageCodes::HOME;
    }
    public function getUserEmailKey(){
        return WooStorageCodes::ADMIN_EMAIL;
    }
    public function getTimezoneKey(){
        return WooStorageCodes::TIMEZONE;
    }
    public function getGMTOffset(){
        return WooStorageCodes::GMT_OFFSET;
    }
    public function getPluginVersionStorageKey(){
        return WooStorageCodes::PLUGIN_VERSION_STORAGE;
    }
}