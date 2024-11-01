<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;

interface ProductsDataSource {
    public function getFilteredStoreProducts(Store $store, CatalogIndexationConfiguration $config);
}