<?php 
    namespace SEE\WC\CreativeSearch\Presentation\Onboarding\Processing;
    use SEE\WC\CreativeSearch\Presentation\Onboarding\OnboardingStep;
    use SEE\WC\CreativeSearch\Presentation\Onboarding\StepNames;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState;
    use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

class ProcessingScreenOnboarding implements OnboardingStep {
    private $utils;
    private $configuration_data;
    private $get_impresee_catalog_state;
    private $get_catalog_state;
    private $get_configuration;
    private $project;

    public function __construct(
        PluginUtils $utils, 
        GetImpreseeConfiguration $get_impresee_configuration,
        GetImpreseeConfigurationStatus $get_impresee_configuration_status,
        GetImpreseeCatalogState $get_catalog_state,
        Project $project
    ){
        $this->utils = $utils;
        $this->get_impresee_configuration_status = $get_impresee_configuration_status;
        $this->get_impresee_configuration = $get_impresee_configuration;
        $this->get_catalog_state = $get_catalog_state;
        $this->get_configuration = $get_impresee_configuration;
        $this->project = $project;
    }

    private function getProcessingUrl(){
        $has_error = FALSE;
        $store_data = $this->utils->getStore();
        $config_status;
        if($store_data == NULL){
            return FALSE;
        }
        $impresee_config_status_promise = $this->get_impresee_configuration_status->execute($store_data);
        $impresee_config_status_either = $impresee_config_status_promise->wait();
        $impresee_config_status_either->either(
            function($failure) use (&$has_error){
                $has_error = TRUE;
            },
            function($status) use (&$config_status){
                $config_status = $status;
            }
        );
        if($has_error){
            return FALSE;
        }
        return $config_status->last_catalog_update_url;
    }

    public function build(){
        $main_css = $this->utils->getCssUrl('onboarding/impresee_onboarding.min.css');
        $glide_core_css = $this->utils->getAssertUrl('glide-3.4.1/dist/css/glide.core.min.css');
        $glide_theme_css = $this->utils->getAssertUrl('glide-3.4.1/dist/css/glide.theme.min.css');
        $indexation_css = $this->utils->getCssUrl('onboarding/product_indexation.css');
        $update_js = $this->utils->getAssertUrl('js/onboarding/update_catalog.js');
        $glide_js = $this->utils->getAssertUrl('glide-3.4.1/dist/glide.min.js');

        $gif_loading = $this->utils->getImageUrl('onboarding/gif_load.gif');
        $success_image = $this->utils->getImageUrl('onboarding/check.svg');
        $warning_image = $this->utils->getImageUrl('onboarding/warning.svg');
        $welcome_image_url = $this->utils->getImageUrl('onboarding/welcome.jpg');
        
        $url = $this->getProcessingUrl();
        if (!$url){
            wp_redirect(admin_url("admin.php?page={$page_id}&step=error"));
            return;
        }
        $processing_url = $url;
        $complete_processing ='false';
        $step = StepNames::PROCESSING;
        $slider_image_base_path = $this->utils->getImageUrl('onboarding/imagenessliders/slider');
        $messages = array(
    "It has been proven that <span style='font-weight: bold;'> visitors who use a site's search bar have a 300% higher chance of buying</span> in eCommerce.",
    "If your store doesn’t have a search bar, don’t worry! <span style='font-weight: bold;'>You can easily add one</span>, just by pasting a short piece of code into your template.",
    "<span style='font-weight: bold;'>Increase your sales by customizing \"Synonyms and Filters\"</span> for your site search. Your customers will easily find what they’re looking for.",
    "\"Creative Search Bar\" allows you to <span style='font-weight: bold;'>know what your visitors are looking for. Click on DASHBOARD PRO</span> to get insights about  your clients: Geolocation of searches, uploaded images, number of recurrent visitors, among others!",
    "<span style='font-weight: bold;'>Increase by 3 times the session duration of your visitors</span>,with a fun and easy search experience. Make your store memorable by including our unique search by drawing! ",
    "<span style='font-weight: bold;'>Personalize the search experience for your clients.</span> You can change the style and set different features of your search bar. If you have any doubt or special requirements, you <span style='font-weight: bold;'>can always contact us!"
        );
        $debug = $this->project->getIsDebug();
        include 'wc-processing-screen-onboarding.php';
    }

    public function finishOnboarding(){
        $page_id = $this->utils->getPluginPageId();
        $store = $this->utils->getStore();
        $impresee_data_promise = $this->get_configuration->execute($store);
        $impresee_data = $impresee_data_promise->wait()
            ->either(
                function($failure){ return null; },
                function($data){ return $data; }
            );
        if ($impresee_data != null) {
            $catalog_status_promise = $this->get_catalog_state->execute($impresee_data, $store);
            $catalog_status = $catalog_status_promise->wait()
                ->either(
                    function($failure){ return null; },
                    function($data){ return $data; }
                );
        }
        
        wp_redirect(admin_url("admin.php?page={$page_id}"));
    }
}