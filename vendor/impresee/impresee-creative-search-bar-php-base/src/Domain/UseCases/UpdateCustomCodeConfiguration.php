<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;

class UpdateCustomCodeConfiguration { 
    private $repository;

    public function __construct(SearchBarDisplayConfigurationRepository $repository){
        $this->repository = $repository;
    }

    public function execute(Store $store, CustomCodeConfiguration $configuration){
        return $this->repository->updateCustomCodeConfiguration($store, $configuration);
    }
}