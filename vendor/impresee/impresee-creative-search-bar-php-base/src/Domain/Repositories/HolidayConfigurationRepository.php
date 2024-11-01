<?php 
    namespace Impresee\CreativeSearchBar\Domain\Repositories;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;

interface HolidayConfigurationRepository  {
    public function getHolidayConfiguration(Store $store);
    public function updateHolidayConfiguration(Store $store, HolidayConfiguration $config);
    public function removeHolidayConfiguration(Store $store);
}