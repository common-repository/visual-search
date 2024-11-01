<?php 
    namespace Impresee\CreativeSearchBar\Domain\Repositories;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

interface ImpreseeCatalogRepository  {
    public function getCatalogState(ImpreseeCatalog $catalog, String $owner_code, Store $store);
    public function updateCatalog(ImpreseeCatalog $catalog, String $owner_code, Store $store);
    public function getProductsCatalog(Store $store, CatalogIndexationConfiguration $config);
}