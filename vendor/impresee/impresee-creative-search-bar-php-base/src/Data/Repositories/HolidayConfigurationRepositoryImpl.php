<?php
    namespace Impresee\CreativeSearchBar\Data\Repositories;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Models\HolidayConfigurationModel;
    use Impresee\CreativeSearchBar\Data\Models\ErrorEmailModel;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayThemeConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\HolidayLabelsConfiguration;
    use Impresee\CreativeSearchBar\Data\Repositories\BaseRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\HolidayConfigurationRepository;
    use Impresee\CreativeSearchBar\Data\Mappers\HolidayConfigModel2HolidayConfigurationMapper;
    use Impresee\CreativeSearchBar\Data\DataSources\HolidayConfigurationLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveDataException;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Errors\FailureUpdateHolidayData;
    use Impresee\CreativeSearchBar\Core\Errors\FailedAtRemovingDataFailure;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;

class HolidayConfigurationRepositoryImpl extends BaseRepository implements HolidayConfigurationRepository {

    private $local_datasource;
    private $mapper_christmas_configuration;

    public function __construct(
        HolidayConfigurationLocalDataSource $datasource,
        EmailDataSource $email_datasource,
        Project $project
    ){
        parent::__construct($email_datasource, $project);
        $this->local_datasource = $datasource;
        $this->mapper_christmas_configuration = new HolidayConfigModel2HolidayConfigurationMapper;
    }

    public function getHolidayConfiguration(Store $store){
        try {
            $configuration_model = $this->local_datasource->getLocalHolidayConfiguration($store);
            $mapped_custom_code = $this->mapper_christmas_configuration->mapFrom($configuration_model);
            return new FulfilledPromise(
                Either::of($mapped_custom_code)
            );    
        } catch(NoDataException $e){
            $labels = new HolidayLabelsConfiguration;
            $labels->pop_up_title = "Merry Christmas";
            $labels->pop_up_text = "Enjoy a magical moment while picking your perfect Christmas present in our store";
            $labels->searchbar_placeholder = "Find the perfect gift";
            $labels->search_drawing_button = "A drawing of the product";
            $labels->search_photo_button = "An image of the product";
            $labels->search_dropdown_label = "Send to Santa";
            $labels->to_label_letter = "To";
            $labels->from_label_letter = "From";
            $labels->placeholder_message_letter = "Write a message...";
            $labels->title_canvas = "Draw your dream gift";
            $labels->search_button_canvas = "Make it real";
            $labels->button_in_product_page = "Add this product to a Christmas letter";
            $labels->search_results_title = "Similar products";
            $labels->results_title_for_text_search = "My perfect gift is a ";
            $labels->christmas_letter_share_message = "In this christmas I wish this:";
            $labels->christmas_letter_share = "Share your letter:";
            $labels->christmas_letter_receiver_button = "View product";
            $config = new HolidayThemeConfiguration;
            $config->is_mode_active = FALSE;
            $config->theme = HolidayThemeConfiguration::ACCENT_THEME;
            $config->automatic_popup = TRUE;
            $config->store_logo_url = "";
            $config->add_style_to_search_bar = TRUE;
            $empty_configuration = new HolidayConfiguration;
            $empty_configuration->config_theme = $config;
            $empty_configuration->labels_configuration = $labels;
            return new FulfilledPromise(
                Either::of($empty_configuration)
            );    
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Holiday Data data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }    
    }

    public function updateHolidayConfiguration(Store $store, HolidayConfiguration $config){
        $mapped_model = $this->mapper_christmas_configuration->mapTo($config);
        try{
            $stored_model = $this->local_datasource->updateLocalHolidayConfiguration($store, $mapped_model);
            $mapped_configuration = $this->mapper_christmas_configuration->mapFrom($stored_model);
            return new FulfilledPromise(
                Either::of($mapped_configuration)
            );    
        } catch(CouldNotStoreDataException $e){
            $store_name = $store->url;
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while updating Holiday configuration', 
                'Could\'t update Holiday configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(
                new Left(new FailureUpdateHolidayData)
            );
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while updating Impresee Holiday data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    public function removeHolidayConfiguration(Store $store){
        try{
            $this->local_datasource->removeLocalHolidayConfiguration($store);
            return new FulfilledPromise(
                Either::of(NULL)
            );    
        } catch(CouldNotRemoveDataException $e){
            $store_name = $store->url;
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while removing holiday configuration', 
                'Could\'t remove holiday configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(
                new Left(new FailedAtRemovingDataFailure)
            );
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while removing Holiday Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }
}
