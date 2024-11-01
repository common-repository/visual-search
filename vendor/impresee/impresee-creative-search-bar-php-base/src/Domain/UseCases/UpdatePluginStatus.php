<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

class UpdatePluginStatus{
    private $impresee_repository;

    public function __construct(ImpreseeConfigurationRepository $impresee_repository){
        $this->impresee_repository = $impresee_repository;
    }


    public function execute(Store $store, bool $isEnabled){
        return $this->impresee_repository->notifyChangeInEnableStatus($store, $isEnabled);
    }
}