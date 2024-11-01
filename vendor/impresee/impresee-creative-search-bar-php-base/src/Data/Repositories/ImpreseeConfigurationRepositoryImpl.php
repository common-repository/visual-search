<?php 
    namespace Impresee\CreativeSearchBar\Data\Repositories;
    use Impresee\CreativeSearchBar\Data\Repositories\BaseRepository;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeConfigurationModel2ImpreseeSearchBarConfig;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeConfigurationModel2ImpreseeConfigurationStatus;
    use Impresee\CreativeSearchBar\Data\Mappers\IndexationConfigurationModel2CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeSubscriptionStatusModel2ImpreseeSubscriptionStatusMapper;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeSubscriptionDataModel2ImpreseeSubscriptionDataMapper;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeCreateAccountUrlModel2ImpreseeCreateAccountUrl;
    use Impresee\CreativeSearchBar\Data\Mappers\PluginVersionModel2PluginVersion;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\InvalidMarket;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\EmptyImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\StoreLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Models\{ErrorEmailModel, InformationEmailModel};
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Core\Errors\{NoDataException, CouldNotStoreDataException, FailedAtRemovingDataFailure, 
        CouldNotRemoveDataException, CouldNotRemoveStoreCodeException
    };
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Errors\NoImpreseeConfigurationDataFailure;
    use Impresee\CreativeSearchBar\Core\Errors\NoImpreseeConfigurationStatusFailure;
    use Impresee\CreativeSearchBar\Core\Errors\FailureNoStoredPluginVersionData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureAtStoringPluginVersionData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureCreateOwner;
    use Impresee\CreativeSearchBar\Core\Errors\FailureCreateCatalog;
    use Impresee\CreativeSearchBar\Core\Errors\FailureInvalidError;
    use Impresee\CreativeSearchBar\Core\Errors\FailureStoreOwnerData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureStoreImpreseeData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureDataAlreadyExists;
    use Impresee\CreativeSearchBar\Core\Errors\FailureInvalidMarket;
    use Impresee\CreativeSearchBar\Core\Errors\FailureUpdateIndexationData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureNoSubscriptionStatusStored;
    use Impresee\CreativeSearchBar\Core\Errors\FailureCouldNotUpdateSubscriptionStatus;
    use Impresee\CreativeSearchBar\Core\Errors\FailureCouldNotObtainSubscriptionData;
    use Impresee\CreativeSearchBar\Core\Constants\ExceptionCodes;
    use Impresee\CreativeSearchBar\Core\Constants\DestinationGroups;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;
    use GuzzleHttp\Promise\FulfilledPromise;

class ImpreseeConfigurationRepositoryImpl extends BaseRepository implements ImpreseeConfigurationRepository {
    private $local_datasource;
    private $remote_datasource;
    private $store_datasource;
    private $mapper;
    private $mapper_indexation_configuration;
    private $mapper_status;
    private $mapper_subscription_data;
    private $mapper_subscription_status;
    private $mapper_create_account_data;
    private $mapper_plugin_version;


    public function __construct(ImpreseeLocalDataSource $local_datasource,
        ImpreseeRemoteDataSource $remote_datasource, 
        EmailDataSource $email_datasource,
        StoreLocalDataSource $store_datasource,
        Project $project
    ){
        parent::__construct($email_datasource, $project);
        $this->local_datasource = $local_datasource;
        $this->remote_datasource = $remote_datasource; 
        $this->store_datasource = $store_datasource;
        $this->mapper_status = new ImpreseeConfigurationModel2ImpreseeConfigurationStatus;
        $this->mapper = new ImpreseeConfigurationModel2ImpreseeSearchBarConfig;
        $this->mapper_indexation_configuration = new IndexationConfigurationModel2CatalogIndexationConfiguration;
        $this->mapper_subscription_data = new ImpreseeSubscriptionDataModel2ImpreseeSubscriptionDataMapper;
        $this->mapper_subscription_status = new ImpreseeSubscriptionStatusModel2ImpreseeSubscriptionStatusMapper;
        $this->mapper_create_account_data = new ImpreseeCreateAccountUrlModel2ImpreseeCreateAccountUrl;
        $this->mapper_plugin_version = new PluginVersionModel2PluginVersion;
    }


    public function getImpreseeConfiguration(Store $store){
        try {
            $impresee_data = $this->local_datasource->getRegisteredImpreseeData($store);
            $mapped_data = $this->mapper->mapFrom($impresee_data);
            return new FulfilledPromise(Either::of($mapped_data));
        } catch (NoDataException $e){
            // Send error email and return
            $store_name = $this->store_datasource->getStoreUrl();
            if ($this->store_datasource->finishedOnboarding($store_name)){
                $email_data = new ErrorEmailModel(
                    $store_name,
                    'Error while retrieving Impresee data from Wordpress options', 
                    'Could\'t recover saved data for '.$store_name,
                    $this->project->getProjectName()
                );
                $this->email_datasource->sendErrorEmail($email_data);     
            }
            return new FulfilledPromise(new Left(new NoImpreseeConfigurationDataFailure));
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function registerOwner(Store $store){
        try{
            $owner = $this->local_datasource->getRegisteredOwner($store);
            return new FulfilledPromise(Either::of($owner));
        } catch (NoDataException $e){
            return $this->remote_datasource->registerOwner($store)
                ->then(
                    function($owner) use ($store) {
                        try {
                            $this->local_datasource->registerLocalOwner($store, $owner);
                            if(!$this->project->getIsDebug()) {
                                $store_name = $store->getStoreName();
                                $info_email = new InformationEmailModel(
                                    $store_name,
                                    DestinationGroups::SALES,
                                    $store_name . " has called install service for the ".$this->project->getProjectName()." plugin",
                                    "Shop has installed our plugin " . date("Y.m.d") . " at " . date("h:i:sa") . " " .date_default_timezone_get().' with email: '.$store->shop_email,
                                    $this->project->getProjectName()
                                );
                                $this->email_datasource->sendInformationEmail($info_email);
                            }
                            return Either::of($owner);    
                        } catch(CouldNotStoreDataException $e){
                            $error_code = 'Couldn\'t store owner data';
                            $store_name = $this->store_datasource->getStoreUrl();
                            $email_data = new ErrorEmailModel(
                                $store_name,
                                'Error while storing owner data for '.$store_name, 
                                $error_code,
                                $this->project->getProjectName()
                            );
                            $this->email_datasource->sendErrorEmail($email_data);
                            return new Left(new FailureStoreOwnerData);
                        }    
                    },
                    function($reason) {
                        if ($reason->getMessage() == ExceptionCodes::CREATE_OWNER_ERROR) {
                                return new Left(new FailureCreateOwner);
                        }
                        throw $reason;
                    }
                );
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }  
    }

    private function registerImpreseeCatalogData(OwnerModel $owner, CatalogMarket $market, Store $store){
        try {
            $catalog_url = $this->store_datasource->getCreateCatalogUrl($store->catalog_generation_code);
            return $this->remote_datasource
                ->registerCatalog($owner, $market, $store, $catalog_url)
                ->then(
                    function($impresee_data) use ($store){
                        //register impresee data
                        try {
                           $this->local_datasource->registerImpreseeLocalData($store, $impresee_data); 
                           return $impresee_data;
                        } catch(CouldNotStoreDataException $e){
                            $error_code = 'Couldn\'t store impresee data';
                            $store_name = $this->store_datasource->getStoreUrl();
                            $email_data = new ErrorEmailModel(
                                $store_name,
                                'Error while storing Impresee data for '.$store_name, 
                                $error_code,
                                $this->project->getProjectName()
                            );
                            $this->email_datasource->sendErrorEmail($email_data); 
                            throw new \Exception(ExceptionCodes::COULD_NOT_STORE_IMPRESEE_DATA);
                        }
                    },
                    function($reason) {
                        throw $reason;
                    }
                );
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    public function registerImpreseeConfiguration(Store $store, CatalogMarket $market){
        if ($market instanceof InvalidMarket){
            $error_code = 'Invalid market: '.$market->toString();
            $store_name = $this->store_datasource->getStoreUrl();
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while creating Impresee data for '.$store_name, 
                $error_code,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(new Left(new FailureInvalidMarket)); 
        }
        try {
            // we don't register the data if it already has been registered
            $impresee_data = $this->local_datasource->getRegisteredImpreseeData($store);
            // Send error email and return
            $store_name = $this->store_datasource->getStoreUrl();
            $email_data = new ErrorEmailModel(
                $store_name,
                'Trying to register data impresee data again!', 
                'Could\'t register data for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data); 
            return new FulfilledPromise(Either::of(
                $this->mapper->mapFrom(
                  $impresee_data
                )
            ));
        } catch (NoDataException $e){
            $stored_owner = array();
            $owner_promise = $this->registerOwner($store);
            $impresee_data_promise = $owner_promise->then(
                function($owner_either) use ($store, $market) {
                    return $owner_either->either(
                        function($failure) {
                            if ($failure instanceof FailureCreateOwner) {
                                throw new \Exception(ExceptionCodes::CREATE_OWNER_ERROR);
                            }
                            else if ($failure instanceof FailureStoreOwnerData) {
                                throw new \Exception(ExceptionCodes::COULD_NOT_STORE_OWNER_DATA);   
                            }
                        },
                        function($owner) use ($store, $market) {
                            return $this->registerImpreseeCatalogData($owner,$market, $store);
                        }
                    );   
                },
                function($reason) {
                    throw $reason;
                }
            );
            $return_promise = $impresee_data_promise->then(
                    function($impresee_data) use ($store){
                        $this->local_datasource->setCreatedImpreseeData($store);
                        return Either::of($this->mapper->mapFrom($impresee_data));
                    },
                    function($reason){
                        $error_code = $reason->getMessage();
                        $store_name = $this->store_datasource->getStoreUrl();
                        $email_data = new ErrorEmailModel(
                            $store_name,
                            'Error while creating Impresee data for '.$store_name, 
                            $error_code,
                            $this->project->getProjectName(),
                            $reason->getTraceAsString()
                        );
                        $this->email_datasource->sendErrorEmail($email_data); 
                        switch ($error_code) {
                            case ExceptionCodes::CREATE_OWNER_ERROR:
                                return new FulfilledPromise(new Left(new FailureCreateOwner));
                            case ExceptionCodes::CREATE_CATALOG_ERROR:
                                return new FulfilledPromise(new Left(new FailureCreateCatalog));
                            case ExceptionCodes::COULD_NOT_STORE_OWNER_DATA:
                                return new FulfilledPromise(new Left(
                                    new FailureStoreOwnerData
                                ));
                            case ExceptionCodes::COULD_NOT_STORE_IMPRESEE_DATA:
                                return new FulfilledPromise(new Left(
                                    new FailureStoreImpreseeData
                                ));
                            default:
                                return new FulfilledPromise(new Left(new FailureInvalidError));
                        }
                    }
                );
            return $return_promise;
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    private function removeLocalData($store){
        $store_name = $this->store_datasource->getStoreUrl();
        $return_value = Either::of(NULL); 
        try {
            $this->local_datasource->removeAllLocalData($store);
        } catch(CouldNotRemoveDataException $e){
            $error_code = 'Couldn\'t remove local impresee data';
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while removing Impresee data for '.$store_name, 
                $error_code,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data); 
            $return_value = new Left(new FailedAtRemovingDataFailure);
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
        try {
            $this->store_datasource->removeStoreData($store_name);
        } catch(CouldNotRemoveStoreCodeException $e){
            $error_code = 'Couldn\'t remove store catalog generation code data';
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while removing code data for '.$store_name, 
                $error_code,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data); 
            $return_value = new Left(new FailedAtRemovingDataFailure);
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
        if(!$this->project->getIsDebug()) {
            $info_email = new InformationEmailModel(
            $store_name,
            DestinationGroups::SALES,
            $store_name . " has uninstalled the ".$this->project->getProjectName()." plugin",
            "Shop has uninstalled our plugin " . date("Y.m.d") . " at " . date("h:i:sa") . " " .date_default_timezone_get().' with email: '.$store->shop_email,
            $this->project->getProjectName()
            );
            $this->email_datasource->sendInformationEmail($info_email);
        }
        return $return_value;
    }

    public function removeAllData(Store $store){
        try {
            $owner = $this->local_datasource->getRegisteredOwner($store);
            return $this->remote_datasource
                ->removeData($owner)
                ->then(
                    function() use ($store){
                        return $this->removeLocalData($store);
                    },
                    function($reason) {
                        $error_code = $reason->getMessage();
                        $store_name = $this->store_datasource->getStoreUrl();
                        $email_data = new ErrorEmailModel(
                            $store_name,
                            'Error while removing Impresee data in cloud for '.$store_name, 
                            $error_code,
                            $this->project->getProjectName(),
                            $reason->getTraceAsString()
                        );
                        $this->email_datasource->sendErrorEmail($email_data);
                        return new Left(new FailedAtRemovingDataFailure);
                    }
                );
        }
        catch (NoDataException $e)
        {
            return new FulfilledPromise($this->removeLocalData($store));
        }
        catch(\Throwable $t){
            print_r($t->getMessage());
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    public function getConfigurationStatus(Store $store){
        try {
            $impresee_data = $this->local_datasource->getRegisteredImpreseeData($store);
            $mapped_data = $this->mapper_status->mapFrom($impresee_data);
            return new FulfilledPromise(Either::of($mapped_data));
        } catch(NoDataException $e){
            $store_name = $this->store_datasource->getStoreUrl();
            if ($this->store_datasource->finishedOnboarding($store_name)){
                $email_data = new ErrorEmailModel(
                    $store_name,
                    'Error while retrieving Impresee status data from Wordpress options', 
                    'Could\'t recover saved data for '.$store_name,
                    $this->project->getProjectName()
                );
                $this->email_datasource->sendErrorEmail($email_data); 
            }
            return new FulfilledPromise(new Left(new NoImpreseeConfigurationStatusFailure));
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    public function getIndexationConfiguration(Store $store){
        try {
            $model = $this->local_datasource->getIndexationConfiguration($store);
            return new FulfilledPromise(
                Either::of(
                    $this->mapper_indexation_configuration
                        ->mapFrom($model)
                ));    
        } catch(NoDataException $e){
            $default_value = new CatalogIndexationConfiguration;
            $default_value->show_products_with_no_price = TRUE;
            $default_value->index_only_in_stock_products = FALSE;
            return new FulfilledPromise(
                Either::of($default_value)
            );    
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name,
                $this->project->getProjectName()
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
        
    }

    public function updateIndexationConfiguration(Store $store, CatalogIndexationConfiguration $configuration){
        $model = $this->mapper_indexation_configuration->mapTo($configuration);
        try {
            $stored_model = $this->local_datasource->updateIndexationConfiguration($store, $model);
            return new FulfilledPromise(
                Either::of(
                    $this->mapper_indexation_configuration
                        ->mapFrom($stored_model)
                ));        
        } catch(CouldNotStoreDataException $e){
            $store_name = $this->store_datasource->getStoreUrl();
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while updating Indexation configuration', 
                'Could\'t update indexation configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data); 
             return new FulfilledPromise(
                new Left(new FailureUpdateIndexationData)
            );
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function getStoredSubscriptionStatus(Store $store){
        try {
            $stored_model = $this->local_datasource->getLocalSubscriptionStatusData($store);
            return new FulfilledPromise(
                Either::of(
                    $this->mapper_subscription_status
                        ->mapFrom($stored_model)
                ));        
        } catch(NoDataException $e){
             return new FulfilledPromise(
                new Left(new FailureNoSubscriptionStatusStored)
            );
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee stored subscription status from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }
    public function updateStoredSubscriptionStatus(Store $store){
        try {
            $owner_model = $this->local_datasource->getRegisteredOwner($store);
            return $this->remote_datasource->isSuspended($owner_model)
                ->then(
                    function($subscription_status) use ($store) {
                        $this->local_datasource->updateLocalSubscriptionStatusData($store, $subscription_status);
                        return Either::of(NULL);
                    },
                    function($reason) {
                        $store_name = $this->store_datasource->getStoreUrl();
                        $this->sendErrorEmail($reason,
                         'Error while updating Impresee subscription status ', $store_name); 
                        return new Left(new FailureCouldNotUpdateSubscriptionStatus);
                    }
                );  
        } catch(NoDataException $e){
             return new FulfilledPromise(
                new Left(new NoImpreseeConfigurationDataFailure)
            );    
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee subscription status from server', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function getSubscriptionData(Store $store){
        try {
            $owner_model = $this->local_datasource->getRegisteredOwner($store);
            return $this->remote_datasource->obtainSubscriptionData($owner_model)
                ->then(
                    function($subscription_data) use ($store) {
                        $subscription = $this->mapper_subscription_data->mapFrom($subscription_data);
                        return Either::of($subscription);
                    },
                    function($reason) {
                        $store_name = $this->store_datasource->getStoreUrl();
                        $this->sendErrorEmail($reason,
                         'Error while obtaining Impresee subscription data ', $store_name); 
                        return new Left(new FailureCouldNotObtainSubscriptionData);
                    }
                );  
        } catch(NoDataException $e){
             return new FulfilledPromise(
                new Left(new NoImpreseeConfigurationDataFailure)
            );    
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                 'Error while obtaining Impresee subscription data ', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function notifyChangeInEnableStatus(Store $store, bool $is_enabled){
        try {
            $owner_model = $this->local_datasource->getRegisteredOwner($store);
            return $this->remote_datasource->notifyChangeInActivationState($owner_model, $is_enabled)
                ->then(
                    function() use ($store, $is_enabled) {
                        if(!$this->project->getIsDebug()) {
                            $store_name = $this->store_datasource->getStoreUrl();
                            $enabled_string = $is_enabled ? "enabled" : "disabled";
                            $info_email = new InformationEmailModel(
                                $store_name,
                                DestinationGroups::SALES,
                                $store_name . " has uninstalled the ".$this->project->getProjectName()." plugin",
                                "Shop has ".$enabled_string." our plugin " . date("Y.m.d") . " at " . date("h:i:sa") . " " .date_default_timezone_get().' with email: '.$store->shop_email,
                                $this->project->getProjectName()
                            );
                            $this->email_datasource->sendInformationEmail($info_email);
                        }
                        return Either::of(NULL);
                    },
                    function($reason) {
                        $store_name = $this->store_datasource->getStoreUrl();
                        $this->sendErrorEmail($reason,
                        'Error while notifying change in impresee plugin status ', $store_name); 
                        return new Left(new FailureCouldNotObtainSubscriptionData);
                    }
                );  
        } catch(NoDataException $e){
             return new FulfilledPromise(
                new Left(new NoImpreseeConfigurationDataFailure)
            );    
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while notifying change in impresee plugin status  ', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function getCreateAccountUrl(Store $store, String $redirect_type){
        try {
            $owner_model = $this->local_datasource->getRegisteredOwner($store);
            return $this->remote_datasource->getCreateAccountUrl($owner_model, $redirect_type)
                ->then(
                    function($account_data) use ($store) {
                        $account_url = $this->mapper_create_account_data->mapFrom($account_data);
                        return Either::of($account_url);
                    },
                    function($reason) {
                        $store_name = $this->store_datasource->getStoreUrl();
                        $this->sendErrorEmail($reason,
                        'Error while obtaining Impresee create account url ', $store_name); 
                        return new Left(new FailureCouldNotObtainSubscriptionData);
                    }
                );  
        } catch(NoDataException $e){
             return new FulfilledPromise(
                new Left(new NoImpreseeConfigurationDataFailure)
            );    
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while obtaining Impresee create account url ', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function updateStoredPluginVersion(Store $store){
        try {
           $plugin_version_model = $this->local_datasource->updateStoredPluginVersion($store);
            return new FulfilledPromise(
                Either::of($this->mapper_plugin_version->mapFrom($plugin_version_model))
            ); 
        } catch(CouldNotStoreDataException $e){
             return new FulfilledPromise(
                new Left(new FailureAtStoringPluginVersionData)
            );    
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while storing plugin version ', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }

    public function getStoredPluginVersion(Store $store){
        try {
            $plugin_version_model = $this->local_datasource->getStoredPluginVersion($store);
            return new FulfilledPromise(
                Either::of($this->mapper_plugin_version->mapFrom($plugin_version_model))
            ); 
        } catch(NoDataException $e){
             return $this->updateStoredPluginVersion($store);
        } catch(\Throwable $t){
            $store_name = $this->store_datasource->getStoreUrl();
            $this->sendErrorEmail($t,
                'Error while obtaining stored plugin version ', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }
}
