<?php
    namespace Impresee\CreativeSearchBar\Domain\Repositories;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarDisplayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;

interface SearchBarDisplayConfigurationRepository {
    public function getSearchBarCustomCodeConfiguration(Store $store);
    public function updateCustomCodeConfiguration(Store $store, CustomCodeConfiguration $config);
    public function getLocalImpreseeSnippetConfiguration(Store $store);
    public function updateImpreseeSnippetConfiguration(Store $store, ImpreseeSnippetConfiguration $configuration);
    public function removeCustomCodeConfiguration(Store $store);
    public function removeSnippetConfiguration(Store $store);
}