<?php 
    namespace Impresee\CreativeSearchBar\Domain\Repositories;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;

interface ImpreseeConfigurationRepository  {
    public function getImpreseeConfiguration(Store $store);
    public function registerImpreseeConfiguration(Store $store, CatalogMarket $market);
    public function removeAllData(Store $store);
    public function getConfigurationStatus(Store $store);
    public function getIndexationConfiguration(Store $store);
    public function updateIndexationConfiguration(Store $store, CatalogIndexationConfiguration $configuration);
    public function getStoredSubscriptionStatus(Store $store);
    public function updateStoredSubscriptionStatus(Store $store);
    public function getSubscriptionData(Store $store);
    public function notifyChangeInEnableStatus(Store $store, bool $is_enabled);
    public function getCreateAccountUrl(Store $store, String $redirect_type);
    public function updateStoredPluginVersion(Store $store);
    public function getStoredPluginVersion(Store $store);
    public function registerOwner(Store $store);
}