<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalogStatus;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;

interface ImpreseeRemoteDataSource {
    
    public function registerOwner(Store $store);
    public function registerCatalog(OwnerModel $owner_code, CatalogMarket $catalog_market, Store $store, String $catalog_url);
    public function updateCatalog(ImpreseeCatalog $catalog, String $owner_code);
    public function getCatalogState(ImpreseeCatalog $catalog, String $owner_code);
    public function removeData(OwnerModel $configuration);
    public function obtainSubscriptionData(OwnerModel $owner_code);
    public function isSuspended(OwnerModel $owner_code);
    public function notifyChangeInActivationState(OwnerModel $owner, bool $is_active);
    public function getCreateAccountUrl(OwnerModel $owner, String $redirect_type);
}