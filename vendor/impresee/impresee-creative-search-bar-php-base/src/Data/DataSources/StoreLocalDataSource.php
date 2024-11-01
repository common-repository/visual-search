<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

interface StoreLocalDataSource {
    public function getStoreUrl();
    public function getStoreAdminData();
    public function getLanguage();
    public function getSiteTitle();
    public function getTimezone();
    public function getCurrentCatalogGenerationCode(String $store_url);
    public function storeCatalogGenerationCode(String $store_url, String $code);
    public function removeStoreData(String $store_url);
    public function finishedOnboarding(String $store_url);
    public function setFinishedOnboarding(String $store_url);
    public function getCreateCatalogUrl(String $catalog_code);
}