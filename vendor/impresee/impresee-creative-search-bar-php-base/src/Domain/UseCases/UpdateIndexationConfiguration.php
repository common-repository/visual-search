<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;


class UpdateIndexationConfiguration {
    private $configuration_repository;

    public function __construct(ImpreseeConfigurationRepository $repository){
        $this->configuration_repository = $repository;
    }

    public function execute(Store $store, CatalogIndexationConfiguration $configuration){
        return $this->configuration_repository->updateIndexationConfiguration($store, $configuration);
    }
}