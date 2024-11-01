<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository;

class UpdateImpreseeCatalog {
    private $catalog_repository;

    public function __construct(ImpreseeCatalogRepository $catalog_repository){
        $this->catalog_repository = $catalog_repository;
    }

    public function execute(ImpreseeSearchBarConfiguration $configuration, Store $store){
        return $this->catalog_repository->updateCatalog($configuration->catalog, $configuration->owner_code, $store);
    }
}