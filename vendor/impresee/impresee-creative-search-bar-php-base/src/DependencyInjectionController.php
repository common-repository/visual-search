<?php 
    namespace Impresee\CreativeSearchBar;
    use DI\ContainerBuilder;
    use DI\Container;
    use ImpreseeGuzzleHttp\HandlerStack;
    use ImpreseeGuzzleRetry\GuzzleRetryMiddleware;
//TODO: Add not implemented error for not implemented interfaces
final class DependencyInjectionController {
    private $container;
    private static $instance;

    protected function __construct() {
        $this->buildContainer();
    }

    public static function getInstance(): DependencyInjectionController {
        if (static::$instance == null){
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function getContainer() : \DI\Container {
        return $this->container;
    }

    protected function buildContainer(){
        $builder = new ContainerBuilder();
        // We add all necessary definitions
        $builder->addDefinitions([
            \ImpreseeGuzzleHttp\Client::class => function(Container $c){
                $stack = HandlerStack::create();
                $stack->push(GuzzleRetryMiddleware::factory([
                    'max_retry_attempts' => 3,
                    'retry_on_timeout'   => true,
                    'connect_timeout'    => 20.0,
                    'timeout'            => 50.0
                ]));
                return new \ImpreseeGuzzleHttp\Client(['handler' => $stack]);
            },
            \Impresee\CreativeSearchBar\Core\Utils\CodesGenerator::class => \DI\create(\Impresee\CreativeSearchBar\Core\Utils\CodesGenerator::class),
            \Impresee\CreativeSearchBar\Data\DataSources\CodesDataSource::class => function(
                Container $c
            ) {
                $generator = $c->get(\Impresee\CreativeSearchBar\Core\Utils\CodesGenerator::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\CodesDataSourceImpl($generator);
            },
            // Data Source
            \Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class => function(Container $c) {
                $client = $c->get(\ImpreseeGuzzleHttp\Client::class);
                $log_handler = $c->get(\Impresee\CreativeSearchBar\Core\Utils\LogHandler::class);
                $services = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Services::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\EmailDataSourceImpl($client, $log_handler, $services);
            },
            \Impresee\CreativeSearchBar\Data\DataSources\ProductsCatalogXMLDataSource::class => function(Container $c) {
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\ProductsCatalogXMLDataSourceImpl($project);
            },
            \Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSource::class => function(Container $c) {
                $storage = $c->get(\Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage::class);
                $storage_codes = $c->get(\Impresee\CreativeSearchBar\Core\Constants\StorageCodes::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSourceImpl($storage, $storage_codes);
            },
            \Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource::class => function(Container $c) {
                $storage = $c->get(\Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage::class);
                $storage_codes = $c->get(\Impresee\CreativeSearchBar\Core\Constants\StorageCodes::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSourceImpl($storage, $storage_codes, $project);
            },
            \Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource::class => function(Container $c) {
                $client = $c->get(\ImpreseeGuzzleHttp\Client::class);
                $log_handler = $c->get(\Impresee\CreativeSearchBar\Core\Utils\LogHandler::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                $services = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Services::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSourceImpl($client, $log_handler, $project, $services);
            },
            \Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource::class => function(Container $c) {
                $storage = $c->get(\Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage::class);
                $rest_utils = $c->get(\Impresee\CreativeSearchBar\Core\Utils\RestInterface::class);
                $storage_codes = $c->get(\Impresee\CreativeSearchBar\Core\Constants\StorageCodes::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSourceImpl($storage, $rest_utils, $storage_codes);
            },
            \Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSource::class => function(Container $c) {
                $storage = $c->get(\Impresee\CreativeSearchBar\Core\Utils\KeyValueStorage::class);
                $storage_codes = $c->get(\Impresee\CreativeSearchBar\Core\Constants\StorageCodes::class);
                return new \Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSourceImpl($storage, $storage_codes);
            },
            // Repository
            \Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository::class  => function(
                Container $c
            ) {
                $email_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class);
                $local_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSource::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\Repositories\SearchBarDisplayConfigurationRepositoryImpl(
                    $local_datasource, 
                    $email_datasource,
                    $project
                );
            },
            \Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository::class  => function(
                Container $c
            ) {
                $remote_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource::class);
                $email_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class);
                $store_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource::class);
                $local_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource::class);
                $products_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\ProductsDataSource::class);
                $catalog_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\ProductsCatalogXMLDataSource::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\Repositories\ImpreseeCatalogRepositoryImpl(
                    $remote_datasource, 
                    $email_datasource, 
                    $store_datasource,
                    $local_datasource,
                    $products_datasource,
                    $catalog_datasource,
                    $project
                );
            },
            \Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class => function(
                Container $c
            ) {
                $local_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource::class);
                $remote_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource::class);
                $email_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class);
                $store_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\Repositories\ImpreseeConfigurationRepositoryImpl(
                    $local_datasource,
                    $remote_datasource, 
                    $email_datasource, 
                    $store_datasource,
                    $project
                );
            },
            \Impresee\CreativeSearchBar\Domain\Repositories\StoreRepository::class => function(
                Container $c
            ) {
                $store_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource::class);
                $codes_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\CodesDataSource::class);
                $email_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\Repositories\StoreRepositoryImpl(
                    $store_datasource,
                    $codes_datasource,
                    $email_datasource,
                    $project
                );
            },
            \Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository::class => function(
                Container $c
            ) {
                $holiday_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSource::class);
                $email_datasource = $c->get(\Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class);
                $project = $c->get(\Impresee\CreativeSearchBar\Core\Constants\Project::class);
                return new \Impresee\CreativeSearchBar\Data\Repositories\HolidayConfigurationRepositoryImpl(
                    $holiday_datasource,
                    $email_datasource,
                    $project
                );
            },
            // Use Cases
            \Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration::class => function(
                Container $c
            ) {
                $holiday_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetHolidayConfiguration(
                    $holiday_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\UpdateHolidayConfiguration::class => function(
                Container $c
            ) {
                $holiday_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\UpdateHolidayConfiguration(
                    $holiday_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState::class => function(
                Container $c
            ) {
                $catalog_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeCatalogState(
                    $catalog_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeProductsCatalog::class => function(
                Container $c
            ) {
                $catalog_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeProductsCatalog(
                    $catalog_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetStoreInformation::class => function(
                Container $c
            ) {
                $store_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\StoreRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetStoreInformation(
                    $store_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeConfiguration::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeConfiguration(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeProject::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\RegisterImpreseeProject(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\RemoveAllImpreseeRelatedData::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                $search_bar_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository::class);
                $holiday_repo = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\RemoveAllImpreseeRelatedData(
                    $config_repository,
                    $search_bar_repository,
                    $holiday_repo
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog::class => function(
                Container $c
            ) {
                $catalog_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog(
                    $catalog_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\UpdateIndexationConfiguration::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\UpdateIndexationConfiguration(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetIndexationConfiguration::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetIndexationConfiguration(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\UpdatePluginStatus::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\UpdatePluginStatus(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionData::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionData(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionStatus::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionStatus(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetInstalledPluginVersionInformation::class => function(
                Container $c
            ) {
                $config_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetInstalledPluginVersionInformation(
                    $config_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration::class => function(
                Container $c
            ) {
                $search_bar_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetSnippetConfiguration(
                    $search_bar_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration::class => function(
                Container $c
            ) {
                $search_bar_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\UpdateSnippetConfiguration(
                    $search_bar_repository
                );
            },
            \Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration::class => function(
                Container $c
            ) {
                $search_bar_repository = $c->get(\Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository::class);
                return new \Impresee\CreativeSearchBar\Domain\UseCases\GetCustomCodeConfiguration(
                    $search_bar_repository
                );
            },

        ]);
        $this->container = $builder->build();
    }
}
