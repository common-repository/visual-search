<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;


class UpdateSnippetConfiguration {
    private $repository;

    public function __construct(SearchBarDisplayConfigurationRepository $repository){
        $this->repository = $repository;
    }

    public function execute(Store $store, ImpreseeSnippetConfiguration $configuration){
        return $this->repository->updateImpreseeSnippetConfiguration($store, $configuration);
    }
}