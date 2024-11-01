<?php
    namespace Impresee\CreativeSearchBar\Core\Constants;

interface StorageCodes {

    public function getOldImpreseeLocalDataKey();
    public function getImpreseeLocalDataKeyPrefix();
    public function getStoreCatalogCodeKeyPrefix();
    public function getStoreFinishedOnboardingKeyPrefix();
    public function getLocalIndexationConfigKeyPrefix();
    public function getOldLocalGeneralSettingsKey();
    public function getLocalCustomCodeSettingsKeyPrefix();
    public function getOldLocalAdvancedSettingsKey();
    public function getLocalSearchBarDsiplayConfigKeyPrefix();
    public function getOldButtonsSettingsKey();
    public function getOldSnippetConfigKey();
    public function getLocalSnippetSettingsKeyPrefix();
    public function getLocalHolidayConfigKeyPrefix();
    public function getLocaleKey();
    public function getSiteTitleKey();
    public function getSiteHomeKey();
    public function getUserEmailKey();
    public function getTimezoneKey();
    public function getGMTOffset();
    public function getPluginVersionStorageKey();
}