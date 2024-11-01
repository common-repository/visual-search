<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository;

class GetImpreseeCatalogState{
    private $catalog_repository;

    public function __construct(ImpreseeCatalogRepository $catalog_repository){
        $this->catalog_repository = $catalog_repository;
    }

    public function execute(ImpreseeSearchBarConfiguration $impresee_data, Store $store){
        return $this->catalog_repository->getCatalogState($impresee_data->catalog, $impresee_data->owner_code, $store);
    }
}