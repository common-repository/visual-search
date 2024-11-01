<?php
    namespace Impresee\CreativeSearchBar\Data\DataSources;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Core\Utils\LogHandler;
    use Impresee\CreativeSearchBar\Domain\Entities\{CatalogMarket, ClothesMarket};
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCatalog;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSearchBarConfiguration;
    use Impresee\CreativeSearchBar\Data\DataSources\ImpreseeRemoteDataSource;
    use Impresee\CreativeSearchBar\Data\Models\OwnerModel;
    use Impresee\CreativeSearchBar\Data\Models\UpdateCatalogModel;
    use Impresee\CreativeSearchBar\Data\Models\CatalogStatusModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionStatusModel;
    use Impresee\CreativeSearchBar\Data\Models\{ImpreseeSubscriptionDataModel, ImpreseeCreateAccountUrlModel};
    use Impresee\CreativeSearchBar\Core\Constants\{Services, Project};
    use Impresee\CreativeSearchBar\Core\Errors\ImpreseeServerException;
    use Impresee\CreativeSearchBar\Core\Errors\ConnectionException;
    use Impresee\CreativeSearchBar\Core\Constants\CreateAccountUrlType;
    use ImpreseeGuzzleHttp\Client;
    use Impresee\Psr\Http\Message\ResponseInterface;
    use ImpreseeGuzzleHttp\Exception\RequestException;

class ImpreseeRemoteDataSourceImpl implements ImpreseeRemoteDataSource { 
    private $client;
    private $log_handler;
    private $project;
    private $services;

    public function __construct(Client $client, LogHandler $log_handler,
     Project $project, Services $services){
        $this->client = $client;
        $this->log_handler = $log_handler;
        $this->project = $project;
        $this->services = $services;
    }

    private function getJsonResponse(ResponseInterface $response){
        $json_response = json_decode($response->getBody(), true);
        return $json_response;   
    }

    public function registerOwner(Store $store){
        $params = array(
                'store_url' => $store->url,
                'store_name' => $store->getStoreName(),
                'user_name' => $store->shop_title,
                'user_email' => $store->shop_email,
                'locale_code' => $store->language,
                'timezone_code' => $store->timezone,
                'trial_days' => strval($this->project->getTrialDays())
            );
        $this->log_handler->writeToLog(
            'Creating owner at: '.$this->services->getCreateOwnerUrl().' with parameters: '.print_r($params, TRUE),
            LogHandler::IMSEE_LOG_DEBUG
        );
        return $this->client->requestAsync('POST', 
            $this->services->getCreateOwnerUrl(),
            ['json' => $params]
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $owner_code = $json_response['owner_uuid'];
                $owner_model = new OwnerModel;
                $owner_model->owner_code = $owner_code;
                return $owner_model;
            },
            function(RequestException $error){
                $this->log_handler->writeToLog('Error creating owner! '.$this->services->getCreateOwnerUrl(), LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getCreateOwnerUrl());
            }
        );   
    }

    public function registerCatalog(OwnerModel $owner, CatalogMarket $catalog_market, Store $store, String $catalog_url){
        $params = array(
                'catalog_market' => $catalog_market->toString(),
                'catalog_url_download' => $catalog_url,
                'catalog_format' => $this->project->getCatalogFormat()
            );
        $request_url = $this->services->getCreateCatalogUrl().$owner->owner_code;
        $this->log_handler->writeToLog(
            'Creating catalog at: '.$request_url.' with parameters: '.print_r($params, TRUE),
            LogHandler::IMSEE_LOG_DEBUG
        );
        return $this->client->requestAsync('POST', 
            $request_url,
            ['json' => $params]
        )->then(
            function(ResponseInterface $response) use ($owner, $catalog_market){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $owner_model = new OwnerModel;
                $owner_model->owner_code = $owner->owner_code;
                $impresee_data = new ImpreseeConfigurationModel;
                $impresee_data->text_app_uuid = $json_response['application_uuid_text'];
                $impresee_data->sketch_app_uuid = $json_response['application_uuid_sketch'];
                $impresee_data->photo_app_uuid = $json_response['application_uuid_image'];
                $impresee_data->owner_model = $owner_model;
                $impresee_data->use_clothing = $catalog_market instanceof ClothesMarket;
                $impresee_data->catalog_processed_once = FALSE;
                $impresee_data->catalog_code = $json_response['catalog_code'];
                $impresee_data->created_data = TRUE;
                $impresee_data->send_catalog_to_update_first_time = FALSE;
                $impresee_data->last_catalog_update_url = '';
                $impresee_data->catalog_market = $catalog_market->toString();
                return $impresee_data;
            },
            function(RequestException $error) use ($owner){
                $this->log_handler->writeToLog('Error creating catalog! '.$this->services->getCreateCatalogUrl().$owner->owner_code,
                    LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getCreateCatalogUrl().$owner->owner_code);
            }
        );  
    }
    public function updateCatalog(ImpreseeCatalog $catalog, String $owner_code){
        $request_url = $this->services->getUpdateCatalogUrl().$owner_code.'/'.$catalog->catalog_code;
        $this->log_handler->writeToLog('Updating catalog: '.$request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('POST', 
            $request_url,
            []
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $update_url = $json_response['catalog_process_status_url'];
                $update_model = new UpdateCatalogModel;
                $update_model->update_url = $update_url;
                return $update_model;
            },
            function(RequestException $error) use ($owner_code, $catalog){
                $this->log_handler->writeToLog('Error updating catalog! '.$this->services->getUpdateCatalogUrl().$owner_code.'/'.$catalog->catalog_code,
                    LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getUpdateCatalogUrl().$owner_code.'/'.$catalog->catalog_code);
            }
        );   
    }
    public function getCatalogState(ImpreseeCatalog $catalog, String $owner_code){
        $request_url = $this->services->getCatalogStatusUrl().$owner_code.'/'.$catalog->catalog_code;
        $this->log_handler->writeToLog('Getting catalog state: '.$request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('GET', 
            $request_url,
            []
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $catalog_is_processing = $json_response['catalog_is_processing'];
                $update_url = $json_response['catalog_process_status_url'];
                $last_update = $json_response['catalog_last_successful_update_timestamp'];
                $status_model = new CatalogStatusModel;
                $status_model->processing = $catalog_is_processing;
                $status_model->last_successful_update = $last_update;
                $status_model->update_url = $update_url;
                return $status_model;
            },
            function(RequestException $error) use($owner_code, $catalog){
                $this->log_handler->writeToLog('Error getting catalog state! '.$this->services->getCatalogStatusUrl().$owner_code.'/'.$catalog->catalog_code, 
                    LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getCatalogStatusUrl().$owner_code.'/'.$catalog->catalog_code);
            }
        ); 
    }

    public function removeData(OwnerModel $owner_model){
        $request_url = $this->services->getRemoveDataUrl().$owner_model->owner_code;
        $this->log_handler->writeToLog('Uninstalling app: '.$request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('POST', 
            $request_url,
            []
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
            },
            function(RequestException $error) use($owner_model){
                $this->log_handler->writeToLog('Error uninstalling app!', LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getRemoveDataUrl().$owner_model->owner_code);
            }
        ); 
    }

    public function obtainSubscriptionData(OwnerModel $owner){
        $request_url = $this->services->getSubscriptionDataUrl().'/'.$owner->owner_code;
        $this->log_handler->writeToLog('Obtaining subscription data: '.$request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('GET', 
            $request_url,
            []
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $trial_days_left = $json_response['days_of_trial_left'];
                $is_subscribed = $json_response['is_subscribed'] ? TRUE : FALSE;
                $plan_name = $json_response['plan_name'];
                $plan_price = $json_response['plan_price'];
                $subscription_model = new ImpreseeSubscriptionDataModel;
                $subscription_model->trial_days_left = $trial_days_left;
                $subscription_model->is_subscribed = $is_subscribed;
                $subscription_model->plan_name = $plan_name;
                $subscription_model->plan_price = $plan_price;
                return $subscription_model;
            },
            function(RequestException $error) use($owner){
                $this->log_handler->writeToLog('Error subscription data', LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getSubscriptionDataUrl().$owner->owner_code);
            }
        ); 

    }
    public function isSuspended(OwnerModel $owner){
        $request_url = $this->services->getSubscriptionStatusUrl().'/'.$owner->owner_code;
        $this->log_handler->writeToLog('Obtaining subscription status: '.$request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('GET', 
            $request_url,
            []
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $suspended = $json_response['isSuspended'];
                $subscription_status_model = new ImpreseeSubscriptionStatusModel;
                $subscription_status_model->suspended = $suspended;
                return $subscription_status_model;
            },
            function(RequestException $error) use($owner){
                $this->log_handler->writeToLog('Error subscription status!', LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getSubscriptionStatusUrl().$owner->owner_code);
            }
        ); 
    }

    public function notifyChangeInActivationState(OwnerModel $owner, bool $is_active){
        $request_url = $this->services->getNotifyChangePluginStatusUrl();
        if($is_active)
        {
            $request_url.= ('enable/'.$owner->owner_code);
        }
        else 
        {
            $request_url.= ('disable/'.$owner->owner_code); 
        }
        $this->log_handler->writeToLog('Notifying new activation state: '.$request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('POST', 
            $request_url,
            []
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
            },
            function(RequestException $error) use($owner){
                $this->log_handler->writeToLog('Error notifying new activation state!', LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getNotifyChangePluginStatusUrl().$owner->owner_code);
            }
        ); 
    }

    private function getClientIp(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR']??'';
        }
        return $ip;
    }

    public function getCreateAccountUrl(OwnerModel $owner, String $redirect_type){
        if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])){
            $request_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";    
        } else {
            $request_url = '';
        }
        $create_account_request_url = $this->services->getCreateAccountUrl();
        $redirection_code = 'DEFAULT';
        switch ($redirect_type) {
            case CreateAccountUrlType::SUBSCRIBE:
                $redirection_code = 'CHOOSE_PLAN';
                break;
            case CreateAccountUrlType::MODIFY_PLAN:
                $redirection_code = 'SUBSCRIPTION';
                break;
            case CreateAccountUrlType::GO_TO_DASHBOARD:
                $redirection_code = 'HOME';
                break;
            default:
                break;
        }
        $this->log_handler->writeToLog('Obtaining new account url: '.$create_account_request_url, LogHandler::IMSEE_LOG_DEBUG);
        return $this->client->requestAsync('POST', 
            $create_account_request_url,
            ['json' => array(
                'owner_uuid'  => $owner->owner_code,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT']??'',
                'remote_ip'   => $this->getClientIp(),
                'request_url' => $request_url,
                'redirection_code' => $redirection_code
             )]
        )->then(
            function(ResponseInterface $response){
                $json_response = $this->getJsonResponse($response);
                if ($json_response['status'] == 1){
                    throw new ImpreseeServerException($json_response['error_message']);
                }
                $url = $json_response['signup_url'];
                $create_account_url_model = new ImpreseeCreateAccountUrlModel;
                $create_account_url_model->url = $url;
                return $create_account_url_model;
            },
            function(RequestException $error) use($owner){
                $this->log_handler->writeToLog('Error obtain account url!', LogHandler::IMSEE_LOG_ERROR);
                throw new ConnectionException($this->services->getCreateAccountUrl().$owner->owner_code);
            }
        ); 
    }
}