<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Models\CustomCodeModel;
    use Impresee\CreativeSearchBar\Data\Models\SearchBarDisplayConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSnippetConfigurationModel;

interface SearchBarDisplayLocalDataSource {

    public function getLocalCustomCodeConfiguration(Store $store);
    public function updateLocalCustomCodeConfiguration(Store $store, CustomCodeModel $configuration);
    public function getLocalImpreseeSnippetConfiguration(Store $store);
    public function updateLocalImpreseeSnippetConfiguration(Store $store, ImpreseeSnippetConfigurationModel $configuration);
    public function removeCustomCodeLocalData(Store $store);
    public function removeSnippetLocalData(Store $store);
}