<?php 
    namespace Impresee\CreativeSearchBar\Data\Repositories;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Repositories\BaseRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\StoreRepository;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\CodesDataSource;
    use Impresee\CreativeSearchBar\Data\Models\StoreModel;
    use Impresee\CreativeSearchBar\Data\Mappers\StoreModel2StoreMapper;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\NoStoreUrlFailure;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\FailureStoreCatalogGenerationCode;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;


class StoreRepositoryImpl extends BaseRepository implements StoreRepository {
    private $store_datasource;
    private $code_datasource;
    private $store_mapper;

    public function __construct(StoreLocalDataSource $store_datasource,
        CodesDataSource $code_datasource,
        EmailDataSource $email_datasource, Project $project){
        parent::__construct($email_datasource, $project);
        $this->code_datasource = $code_datasource;
        $this->store_datasource = $store_datasource;
        $this->store_mapper = new StoreModel2StoreMapper;
    }

    public function getStoreInformation(){
        try {
            try {
                $store_url = $this->store_datasource->getStoreUrl();    
            } catch (NoDataException $e) { 
                return new FulfilledPromise(new Left(new NoStoreUrlFailure));
            }
            $admin_email = $this->store_datasource->getStoreAdminData();
            $language_code = $this->store_datasource->getLanguage();
            $site_title = $this->store_datasource->getSiteTitle();
            $timezone = $this->store_datasource->getTimezone();
            try {
                $catalog_generation_code = $this->store_datasource
                    ->getCurrentCatalogGenerationCode($store_url);    
            } catch (NoDataException $e) {
                $new_code = $this->code_datasource->generateNewCode();
                try {
                    $this->store_datasource->storeCatalogGenerationCode($store_url, $new_code);
                     $catalog_generation_code = $new_code;    
                }
                catch(CouldNotStoreDataException $e){
                    return new FulfilledPromise(new Left(new FailureStoreCatalogGenerationCode));
                }
            } 
            $store_model = new StoreModel;
            $store_model->url = $store_url;
            $store_model->site_title = $site_title;
            $store_model->admin_email = $admin_email;
            $store_model->timezone = $timezone;
            $store_model->language = $language_code;
            $store_model->catalog_generation_code = $catalog_generation_code;
            $store = $this->store_mapper->mapFrom($store_model);
            return new FulfilledPromise(Either::of($store));
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }
}
