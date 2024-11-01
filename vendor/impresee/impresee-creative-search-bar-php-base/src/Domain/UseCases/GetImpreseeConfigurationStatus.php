<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

class GetImpreseeConfigurationStatus{
    private $impresee_repository;

    public function __construct(ImpreseeConfigurationRepository $impresee_repository){
        $this->impresee_repository = $impresee_repository;
    }


    public function execute(Store $store){
        return $this->impresee_repository->getConfigurationStatus($store);
    }
}