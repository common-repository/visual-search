<?php 
    namespace SEE\WC\CreativeSearch;
    use DI\ContainerBuilder;
    use DI\Container;
    use GuzzleHttp\HandlerStack;
    use GuzzleRetry\GuzzleRetryMiddleware;
    use Impresee\CreativeSearchBar\DependencyInjectionController;

final class WooDependencyInjectionController {
    private $dependency_injector;
    private $container;
    private static $instance;

    protected function __construct() {
        $this->dependency_injector = DependencyInjectionController::getInstance();
        $this->container = $this->dependency_injector->getContainer();
        $this->buildContainer();
    }

    public static function getInstance(): WooDependencyInjectionController {
        if (static::$instance == null){
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function getContainer() : \DI\Container {
        return $this->container;
    }

    protected function buildContainer(){
        // Constants
        $this->container->set(\Impresee\CreativeSearchBar\Core\Constants\Project::class,
            \DI\create(\SEE\WC\CreativeSearch\Presentation\Utils\WooProject::class)
        );
        $this->container->set(\Impresee\CreativeSearchBar\Core\Constants\Services::class,
            function(Container $c){
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \SEE\WC\CreativeSearch\Presentation\Utils\WooServices($project);
            }
        );
        $this->container->set(\Impresee\CreativeSearchBar\Core\Constants\StorageCodes::class,
            \DI\create(\SEE\WC\CreativeSearch\Presentation\Utils\WooStorageCodes::class)
        );
        // Utils
        $this->container->set(\Impresee\CreativeSearchBar\Core\Utils\LogHandler::class,
            function(Container $c){
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \SEE\WC\CreativeSearch\Presentation\Utils\WordpressLogHandler($project);
            }
        );
        $this->container->set(\Impresee\CreativeSearchBar\Core\Utils\RestInterface::class,
            function(Container $c){
                $services = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Services::class);
                return new \SEE\WC\CreativeSearch\Presentation\Utils\WordpressRestWrapper($services);
            }
        );
        $this->container->set(\Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage::class,
            function(Container $c) {
                $storage_codes = $c->get(\Impresee\CreativeSearchBar\Core\Constants\StorageCodes::class);
                return new \SEE\WC\CreativeSearch\Presentation\Utils\WordpressOptionsWrapper($storage_codes);
            });
        // Data Source
        $this->container->set(\Impresee\CreativeSearchBar\Data\DataSources\ProductsDataSource::class,
            function(Container $c) {
                $log_handler = $c->get(\Impresee\CreativeSearchBar\Core\Utils\LogHandler::class);
                return new \SEE\WC\CreativeSearch\Presentation\Integration\Catalog\ProductsDataSourceImpl($log_handler);
            });
        // Presentation
        // General Settings
        $this->container->set(\SEE\WC\CreativeSearch\Presentation\Settings\General\GeneralSettings::class,
            function(
                Container $c
            ){
                $get_impresee_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class);
                $update_indexation_repository = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateIndexationConfiguration::class);
                $get_indexation_repository = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetIndexationConfiguration::class);
                $get_create_account_url = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetCreateImpreseeAccountUrl::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\General\GeneralSettings(
                    $get_impresee_configuration,
                    $update_indexation_repository,
                    $get_indexation_repository,
                    $get_create_account_url,
                    $plugin_utils,
                    $callbacks
                );
            });
        // Labels Settings
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Settings\Labels\LabelsSettings::class,
            function(
                Container $c
            ){
                $update_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration::class);
                $get_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\Labels\LabelsSettings(
                    $update_snippet_configuration,
                    $get_snippet_configuration,
                    $plugin_utils,
                    $callbacks
                );
            });
        // Theme Settings
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Settings\Theme\ThemeSettings::class,
            function(
                Container $c
            ){
                $update_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration::class);
                $get_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\Theme\ThemeSettings(
                    $update_snippet_configuration,
                    $get_snippet_configuration,
                    $plugin_utils,
                    $callbacks
                );
            }
        );
        // Search by text
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Settings\SearchByText\SearchByTextSettings::class,
            function(
                Container $c
            ){
                $update_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration::class);
                $get_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\SearchByText\SearchByTextSettings(
                    $update_snippet_configuration,
                    $get_snippet_configuration,
                    $plugin_utils,
                    $callbacks
                );
            }
        );
        // Datafeed settings
        $this->container->set(\SEE\WC\CreativeSearch\Presentation\Settings\Datafeed\DatafeedSettings::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\Datafeed\DatafeedSettings(
                    $plugin_utils
                );
            }
        );
        // Advanced Settings
        $this->container->set(\SEE\WC\CreativeSearch\Presentation\Settings\Advanced\AdvancedSettings::class,
            function(
                Container $c
            ){
                $update_custom_code_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateCustomCodeConfiguration::class);
                $get_custom_code_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\Advanced\AdvancedSettings(
                    $update_custom_code_configuration,
                    $get_custom_code_configuration,
                    $plugin_utils,
                    $callbacks
                );
            }
        );
        // Search buttons
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Settings\SearchButtons\SearchButtonsSettings::class,
            function(
                Container $c
            ){
                $get_impresee_configuration_status = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus::class);
                $get_snippet_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\SearchButtons\SearchButtonsSettings(
                    $get_impresee_configuration_status,
                    $get_snippet_configuration,
                    $plugin_utils,
                    $callbacks
                );
            }
        );
        // Christmas
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Settings\Christmas\ChristmasSettings::class,
            function(
                Container $c
            ){
                $update_christmas_config = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateHolidayConfiguration::class);
                $get_christmas_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\Christmas\ChristmasSettings(
                    $update_christmas_config,
                    $get_christmas_configuration,
                    $plugin_utils,
                    $callbacks
                );
            }
        );
        // Settings
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Settings\Settings::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $get_impresee_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class);
                $get_impresee_configuration_status = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus::class);
                $get_create_account_url = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetCreateImpreseeAccountUrl::class);
                $get_impresee_subscription_data = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionData::class);
                $update_catalog = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog::class);
                $callbacks = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\Callbacks::class);
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $services = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Services::class);
                return new \SEE\WC\CreativeSearch\Presentation\Settings\Settings(
                    $plugin_utils, 
                    $get_impresee_configuration,
                    $get_impresee_configuration_status,
                    $get_create_account_url,
                    $get_impresee_subscription_data,
                    $update_catalog,
                    $callbacks,
                    $plugin_utils,
                    $services
                );
            }
        );
        // Onboarding
        // Welcome screen
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Onboarding\WelcomeScreen\WelcomeScreenOnboarding::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \SEE\WC\CreativeSearch\Presentation\Onboarding\WelcomeScreen\WelcomeScreenOnboarding($plugin_utils, $project);
            }
        );
        // Choose Market
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Onboarding\ChooseMarket\ChooseMarketOnboarding::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $register_impresee  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeConfiguration::class);
                $update_catalog  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \SEE\WC\CreativeSearch\Presentation\Onboarding\ChooseMarket\ChooseMarketOnboarding(
                    $plugin_utils,
                    $register_impresee,
                    $update_catalog,
                    $project
                );
            }
        );
        // Processing
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Onboarding\Processing\ProcessingScreenOnboarding::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $get_impresee_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class);
                $get_impresee_configuration_status  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus::class);
                $get_catalog_state  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState::class); 
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \SEE\WC\CreativeSearch\Presentation\Onboarding\Processing\ProcessingScreenOnboarding(
                    $plugin_utils,
                    $get_impresee_configuration,
                    $get_impresee_configuration_status,
                    $get_catalog_state,
                    $project
                );
            }
        );
        // GENERIC Error Screen
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Errors\GenericError\ErrorScreen::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                return new \SEE\WC\CreativeSearch\Presentation\Errors\GenericError\ErrorScreen(
                    $plugin_utils
                );
            }
        );
        $this->container->set(\SEE\WC\CreativeSearch\Presentation\Errors\InvalidHostError\ErrorScreen::class,
            function(
                Container $c
            ){
                $plugin_utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                return new \SEE\WC\CreativeSearch\Presentation\Errors\InvalidHostError\ErrorScreen(
                    $plugin_utils
                );
            }
        );
        // Uninstaller
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Uninstallation\ImpreseeUninstaller::class,
            function(
                Container $c
            ){
                $remove_impresee_data  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\RemoveAllImpreseeRelatedData::class);
                $utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                return new \SEE\WC\CreativeSearch\Presentation\Uninstallation\ImpreseeUninstaller(
                    $remove_impresee_data,
                    $utils
                );
            }
        );
        // Snippet
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Integration\Snippet\ImpreseeSnippet::class,
            function(
                Container $c
            ){
                $get_impresee_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class);
                $get_snippet_configuration  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration::class);
                $utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $get_custom_code_configuration  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration::class);
                $get_impresee_configuration_status = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus::class);
                $get_holiday_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration::class);
                $get_impresee_subscription_status = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionStatus::class);
                
                return new \SEE\WC\CreativeSearch\Presentation\Integration\Snippet\ImpreseeSnippet(
                    $get_impresee_configuration,
                    $get_snippet_configuration,
                    $utils,
                    $get_custom_code_configuration,
                    $get_impresee_configuration_status,
                    $get_holiday_configuration,
                    $get_impresee_subscription_status
                );
            }
        );
        // Get catalog state
        $this->container->set(
            \SEE\WC\CreativeSearch\Presentation\Utils\CatalogStatusGetter::class,
            function(
                Container $c
            ){
                $utils = $c->get(\SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils::class);
                $get_impresee_configuration = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class);
                $get_catalog_state  = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState::class);
                $update_catalog = $c->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog::class);
                
                return new \SEE\WC\CreativeSearch\Presentation\Utils\CatalogStatusGetter(
                    $utils,
                    $get_impresee_configuration,
                    $get_catalog_state,
                    $update_catalog
                );
            }
        );
    }
}