<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

class GetInstalledPluginVersionInformation{
    private $config_repository;

    public function __construct(ImpreseeConfigurationRepository $config_repository){
        $this->config_repository = $config_repository;
    }

    public function execute(Store $store){
        return $this->config_repository->getStoredPluginVersion($store);
    }
}