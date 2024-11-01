<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogMarket;

class RegisterImpreseeConfiguration{
    private $impresee_repository;

    public function __construct(ImpreseeConfigurationRepository $impresee_repository){
        $this->impresee_repository = $impresee_repository;
    }


    public function execute(Store $store, CatalogMarket $market){
        return $this->impresee_repository->registerImpreseeConfiguration($store, $market);
    }
}