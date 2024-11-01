<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;

class UpdateHolidayConfiguration { 
    private $repository;

    public function __construct(HolidayConfigurationRepository $repository){
        $this->repository = $repository;
    }

    public function execute(Store $store, HolidayConfiguration $configuration){
        return $this->repository->updateHolidayConfiguration($store, $configuration);
    }
}