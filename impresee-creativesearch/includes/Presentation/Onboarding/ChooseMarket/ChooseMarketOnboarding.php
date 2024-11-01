<?php 
    namespace SEE\WC\CreativeSearch\Presentation\Onboarding\ChooseMarket;
    use SEE\WC\CreativeSearch\Presentation\Onboarding\OnboardingStep;
    use SEE\WC\CreativeSearch\Presentation\Onboarding\StepNames;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
    use Impresee\CreativeSearchBar\Core\Constants\CatalogMarketCodes;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog;
    use Impresee\CreativeSearchBar\Core\Factories\CatalogMarketFactory;
    use Impresee\CreativeSearchBar\Domain\Entities\InvalidMarket;

class ChooseMarketOnboarding implements OnboardingStep {
    private $utils;
    private $configuration_data;
    private $register_impresee;
    private $update_impresee;
    private $project;

    public function __construct(
        PluginUtils $utils, 
        RegisterImpreseeConfiguration $register_impresee,
        UpdateImpreseeCatalog $update_impresee,
        Project $project
    ){
        $this->utils = $utils;
        $this->register_impresee = $register_impresee;
        $this->update_impresee = $update_impresee;
        $this->project = $project;
        
    }

    public function build(){
        $fashion_image_url = $this->utils->getImageUrl('onboarding/fashion.jpg');
        $homedecor_image_url = $this->utils->getImageUrl('onboarding/homedecor.jpg');
        $other_image_url = $this->utils->getImageUrl('onboarding/dropshippers.jpg');
        $main_css = $this->utils->getCssUrl('onboarding/impresee_onboarding.min.css');
        $select_market_css = $this->utils->getCssUrl('onboarding/select_product_type.min.css');
        $page_id = $this->utils->getPluginPageId();
        $step = StepNames::SELECT_PRODUCT_TYPE;
        $apparel_code = CatalogMarketCodes::APPAREL;
        $home_decor_code = CatalogMarketCodes::HOME_DECOR;
        $other_code = CatalogMarketCodes::OTHER;
        $debug = $this->project->getIsDebug();
        include 'wc-choose-market-onboarding.php';
    }


    public function registerImpresee(){
        $store_data;
        $impresee_data;
        $page_id = $this->utils->getPluginPageId();
        $error_url = admin_url("admin.php?page={$page_id}&step=error");
        $store_data = $this->utils->getStore();
        if($store_data == NULL){
            wp_redirect($error_url);
            return;
        }
        $market_code = $_POST['type_catalog'] == NULL ? '' : $_POST['type_catalog'];
        $market = CatalogMarketFactory::createMarket($market_code);
        $register_promise = $this->register_impresee->execute($store_data, $market);
        $register_data = $register_promise->wait();
        $impresee_data = $register_data->either(
            function($failure) {
                return NULL;
            },
            function($configuration) {
                return $configuration;
            }
        );
        if ($impresee_data == NULL){
            wp_redirect($error_url);
            return;
        }

        $update_data_promise = $this->update_impresee->execute($impresee_data, $store_data);
        $update_data = $update_data_promise->wait();
        $update_data->either(
            function($failure) {
                wp_redirect($error_url);
            },
            function() use ($page_id) {
                $next_step = StepNames::PROCESSING;
                wp_redirect(admin_url("admin.php?page={$page_id}&step={$next_step}"));
            }
        ); 
    }
}