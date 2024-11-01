<?php
    namespace SEE\WC\CreativeSearch\Presentation\Uninstallation;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
    use Impresee\CreativeSearchBar\Domain\UseCases\RemoveAllImpreseeRelatedData;
    use Impresee\CreativeSearchBar\Domain\Entities\EmptyImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Core\Errors\NoImpreseeConfigurationDataFailure;

class ImpreseeUninstaller {
    private $remove_impresee_data;
    private $get_impresee_config;
    private $utils;


    public function __construct(
        RemoveAllImpreseeRelatedData $remove_impresee_data,
        PluginUtils $utils

    ) {
        $this->remove_impresee_data = $remove_impresee_data;
        $this->utils = $utils;
    }

    public function removeAllData(){
        $this->utils->deleteAllOldAndFrontendOptions();
        $store_data = $this->utils->getStore();
        if($store_data == null){
            return;
        }
        $remove_data_promise = $this->remove_impresee_data->execute($store_data);
        $remove_data_promise->wait();
    }
}