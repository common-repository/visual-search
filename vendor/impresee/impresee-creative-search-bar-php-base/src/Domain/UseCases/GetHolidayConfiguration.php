<?php 
    namespace Impresee\CreativeSearchBar\Domain\UseCases;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;


class GetHolidayConfiguration {
    private $repository;

    public function __construct(HolidayConfigurationRepository $repository){
        $this->repository = $repository;
    }

    public function execute(Store $store){
        return $this->repository->getHolidayConfiguration($store);
    }
}