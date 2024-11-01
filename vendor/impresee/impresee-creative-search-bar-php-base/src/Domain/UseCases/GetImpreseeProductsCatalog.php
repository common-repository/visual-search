<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository;


class GetImpreseeProductsCatalog {
    private $repository;

    public function __construct(ImpreseeCatalogRepository $repository){
        $this->repository = $repository;
    }

    public function execute(Store $store, CatalogIndexationConfiguration $config){
        return $this->repository->getProductsCatalog($store, $config);
    }
}