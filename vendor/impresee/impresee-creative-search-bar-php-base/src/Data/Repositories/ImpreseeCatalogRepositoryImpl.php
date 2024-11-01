<?php 
    namespace Impresee\CreativeSearchBar\Data\Repositories;
    use Impresee\CreativeSearchBar\Data\Repositories\BaseRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeCatalogRepository;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeProductsCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ProductsCatalogXMLDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ProductsDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Models\ErrorEmailModel;
    use Impresee\CreativeSearchBar\Data\Mappers\CatalogStatusModel2ImpreseeCatalogStatus;
    use Impresee\CreativeSearchBar\Data\Mappers\UpdateCatalogModel2ImpreseeCatalogStatus;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Errors\ErrorObtainingProducts;
    use Impresee\CreativeSearchBar\Core\Errors\ErrorBuildingCatalog;
    use Impresee\CreativeSearchBar\Core\Errors\FailureAtObtainingProducts;
    use Impresee\CreativeSearchBar\Core\Errors\FailureAtCreatingCatalog;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;
    use GuzzleHttp\Promise\FulfilledPromise;


class ImpreseeCatalogRepositoryImpl extends BaseRepository implements ImpreseeCatalogRepository {
    private $remote_data_source;
    private $local_data_source;
    private $store_datasource;
    private $catalog_status_model_mapper;
    private $update_catalog_model_mapper;
    private $products_datasource;
    private $catalog_datasource;

    public function __construct(
        ImpreseeRemoteDataSource $remote_data_source,
        EmailDataSource $email_data_source, 
        StoreLocalDataSource $store_data_source,
        ImpreseeLocalDataSource $local_data_source,
        ProductsDataSource $products_datasource,
        ProductsCatalogXMLDataSource $catalog_datasource,
        Project $project
    ){  
        parent::__construct($email_data_source, $project);
        $this->local_data_source = $local_data_source;
        $this->store_datasource = $store_data_source;
        $this->catalog_status_model_mapper = new CatalogStatusModel2ImpreseeCatalogStatus;
        $this->update_catalog_model_mapper = new UpdateCatalogModel2ImpreseeCatalogStatus;
        $this->remote_data_source = $remote_data_source;
        $this->products_datasource = $products_datasource;
        $this->catalog_datasource = $catalog_datasource;
    }

    public function getCatalogState(ImpreseeCatalog $catalog, String $owner, Store $store){
        try {
            return $this->remote_data_source->getCatalogState($catalog, $owner)
            ->then(
                function($catalog_status) use ($store) {
                    if(!$catalog_status->processing){
                        $this->local_data_source->setCatalogProcessedOnce($store);
                        $this->store_datasource->setFinishedOnboarding($store->url);
                    }
                    $mapped_catalog_status = $this->catalog_status_model_mapper
                        ->mapFrom($catalog_status);
                    return new FulfilledPromise(Either::of($mapped_catalog_status));
                })
                ->then(
                    null,
                    function($reason) {
                        $store_name = $this->store_datasource->getStoreUrl();
                        $this->sendErrorEmail($reason, 'Error while getting Impresee catalog status for ', $store_name);
                        $mapped_catalog_status = $this->catalog_status_model_mapper
                            ->mapFromException($reason);
                        return Either::of($mapped_catalog_status);
                    }
            );
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t, 'Error while getting Impresee catalog status for ', $store_name);
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
        
    }
    public function updateCatalog(ImpreseeCatalog $catalog, String $owner, Store $store){
        try{
            return $this->remote_data_source->updateCatalog($catalog, $owner)
                ->then(
                    function($update_information) use ($store) {
                        $this->local_data_source->setSentCatalogToUpdate($store);
                        $this->local_data_source->setLastCatalogUpdateProcessUrl(
                            $store,
                            $update_information->update_url
                        );
                        $mapped_catalog_status = $this->update_catalog_model_mapper
                            ->mapFrom($update_information);
                        return Either::of($mapped_catalog_status);
                    },
                    function($reason) {
                        $store_name = $this->store_datasource->getStoreUrl();
                        $this->sendErrorEmail($reason,
                         'Error while sending Impresee catalog to update for ', $store_name); 
                        $mapped_catalog_status = $this->update_catalog_model_mapper
                            ->mapFromException($reason);
                        return Either::of($mapped_catalog_status);
                    }
                );
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t, 'Error while sending Impresee catalog to update for ', $store_name);
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    public function getProductsCatalog(Store $store, CatalogIndexationConfiguration $config){
        // Obtain products
        try {
            $products = $this->products_datasource->getFilteredStoreProducts($store, $config);
        } catch(ErrorObtainingProducts $e){
            $store_name = $store->getStoreName();
            $this->sendErrorEmail($e, 'Error while obtaining products ', $store_name);
            return new FulfilledPromise(new Left(new FailureAtObtainingProducts)); 
        } catch (\Throwable $t){
            $store_name = $store->getStoreName();
            $this->sendErrorEmail($t, 'Unknown error while obtaining products ', $store_name);
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
        // Build catalog
        try {
            $catalog_version = $this->catalog_datasource->getCatalogVersion();
            $catalog_string = $this->catalog_datasource->generateXmlFromProducts($products);
            $catalog_data = new ImpreseeProductsCatalog;
            $catalog_data->impresee_catalog_version = $catalog_version;
            $catalog_data->impresee_catalog_string = $catalog_string;
            return new FulfilledPromise(Either::of($catalog_data));
        } catch(ErrorBuildingCatalog $e){
            $store_name = $store->getStoreName();
            $this->sendErrorEmail($e, 'Error while creating catalog ', $store_name);
            return new FulfilledPromise(new Left(new FailureAtCreatingCatalog)); 
        } catch (\Throwable $t){
            $store_name = $store->getStoreName();
            $this->sendErrorEmail($t, 'Unknown error while creating catalog ', $store_name);
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }
}