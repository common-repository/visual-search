<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\IndexationConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionStatusModel;

interface ImpreseeLocalDataSource {
    
    public function registerImpreseeLocalData(Store $store, ImpreseeConfigurationModel $impresee_data);
    public function setCatalogProcessedOnce(Store $store);
    public function getRegisteredImpreseeData(Store $store);
    public function registerLocalOwner(Store $store, OwnerModel $owner_model);
    public function getRegisteredOwner(Store $store);
    public function removeAllLocalData(Store $store);
    public function setCreatedImpreseeData(Store $store);
    public function setSentCatalogToUpdate(Store $store);
    public function setLastCatalogUpdateProcessUrl(Store $store, String $url); 
    public function getIndexationConfiguration(Store $store);
    public function updateIndexationConfiguration(Store $store, IndexationConfigurationModel $configuration);
    public function getLocalSubscriptionStatusData(Store $store);
    public function updateLocalSubscriptionStatusData(Store $store, ImpreseeSubscriptionStatusModel $subscription_status);
    public function updateStoredPluginVersion(Store $store);
    public function getStoredPluginVersion(Store $store);
}   