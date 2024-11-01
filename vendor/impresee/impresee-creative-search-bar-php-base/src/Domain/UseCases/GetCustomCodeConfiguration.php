<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;


class GetCustomCodeConfiguration {
    private $repository;

    public function __construct(SearchBarDisplayConfigurationRepository $repository){
        $this->repository = $repository;
    }

    public function execute(Store $store){
        return $this->repository->getSearchBarCustomCodeConfiguration($store);
    }
}