<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;

class GetIndexationConfiguration {
    private $configuration_repository;

    public function __construct(ImpreseeConfigurationRepository $repository){
        $this->configuration_repository = $repository;
    }

    public function execute(Store $store){
        return $this->configuration_repository->getIndexationConfiguration($store);
    }
}