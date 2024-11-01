<?php 
    namespace SEE\WC\CreativeSearch\Presentation\Onboarding\WelcomeScreen;
    use SEE\WC\CreativeSearch\Presentation\Onboarding\OnboardingStep;
    use SEE\WC\CreativeSearch\Presentation\Onboarding\StepNames;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
    use Impresee\CreativeSearchBar\Core\Constants\Project;

class WelcomeScreenOnboarding implements OnboardingStep {
    private $utils;
    private $project;

    public function __construct(PluginUtils $utils, Project $project){
        $this->utils = $utils;
        $this->project = $project;
    }

    public function build(){
        $welcome_image_url = $this->utils->getImageUrl('onboarding/welcome.jpg');
        $welcome_css = $this->utils->getCssUrl('onboarding/impresee_onboarding.min.css');
        $page_id = $this->utils->getPluginPageId();
        $next_step = StepNames::SELECT_PRODUCT_TYPE;
        $destination = "?page={$page_id}&step={$next_step}";
        $debug = $this->project->getIsDebug();
        include 'wc-welcome-screen-onboarding.php';
    }
}