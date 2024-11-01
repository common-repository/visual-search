<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;

class RemoveAllImpreseeRelatedData{
    private $configuration_repository;
    private $search_bar_repository;
    private $holiday_config_repository;

    public function __construct(ImpreseeConfigurationRepository $configuration_repository,
        SearchBarDisplayConfigurationRepository $search_bar_repository,
        HolidayConfigurationRepository $holiday_config_repository
    ){
        $this->configuration_repository = $configuration_repository;
        $this->search_bar_repository = $search_bar_repository;
        $this->holiday_config_repository = $holiday_config_repository;
    }

    public function execute(Store $store){
        return $this->configuration_repository
            ->removeAllData($store)
            ->then(function() use ($store){
                return $this->search_bar_repository->removeCustomCodeConfiguration($store);
            })
            ->then(function() use ($store){
                return $this->search_bar_repository->removeSnippetConfiguration($store);
            })
            ->then(function() use ($store){
                return $this->holiday_config_repository->removeHolidayConfiguration($store);
            });
    }
}