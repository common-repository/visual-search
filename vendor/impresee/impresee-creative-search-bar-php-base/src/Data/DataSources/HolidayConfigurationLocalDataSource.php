<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;

interface HolidayConfigurationLocalDataSource {

    public function getLocalHolidayConfiguration(Store $store);
    public function updateLocalHolidayConfiguration(Store $store, HolidayConfigurationModel $configuration);
    public function removeLocalHolidayConfiguration(Store $store);
}
