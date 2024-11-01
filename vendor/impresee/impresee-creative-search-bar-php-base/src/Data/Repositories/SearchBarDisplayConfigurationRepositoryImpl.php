<?php 
    namespace Impresee\CreativeSearchBar\Data\Repositories;
    use Impresee\CreativeSearchBar\Data\Repositories\BaseRepository;
    use Impresee\CreativeSearchBar\Domain\Repositories\SearchBarDisplayConfigurationRepository;
    use Impresee\CreativeSearchBar\Domain\Entities\Store;
    use Impresee\CreativeSearchBar\Data\Mappers\CustomCodeModel2CustomCodeConfigurationMapper;
    use Impresee\CreativeSearchBar\Data\Mappers\SearchBarDisplayConfigurationModel2SearchBarDisplayConfigurationMapper;
    use Impresee\CreativeSearchBar\Data\Mappers\ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Data\DataSources\SearchBarDisplayLocalDataSource;
    use Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource;
    use Impresee\CreativeSearchBar\Data\Models\ErrorEmailModel;
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarDisplayConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\SearchBarInFormConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\CustomCodeConfiguration; 
        use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetGeneralConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetLabelsConfiguration;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSnippetSearchByTextConfiguration;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotStoreDataException;
    use Impresee\CreativeSearchBar\Core\Errors\CouldNotRemoveDataException;
    use Impresee\CreativeSearchBar\Core\Errors\FailureUpdateCustomCodeData;
    use Impresee\CreativeSearchBar\Core\Errors\FailureUpdateImpreseeSnippetData;
    use Impresee\CreativeSearchBar\Core\Errors\FailedAtRemovingDataFailure;
    use Impresee\CreativeSearchBar\Core\Errors\UnknownFailure;
    use Impresee\CreativeSearchBar\Core\Constants\Project;
    use GuzzleHttp\Promise\FulfilledPromise;
    use PhpFp\Either\Either;
    use PhpFp\Either\Constructor\Left;


class SearchBarDisplayConfigurationRepositoryImpl extends BaseRepository implements SearchBarDisplayConfigurationRepository {
    private $local_datasource;
    private $mapper_custom_code;
    private $mapper_search_bar_display;
    private $mapper_snippet;

    public function __construct(
        SearchBarDisplayLocalDataSource $datasource,
        EmailDataSource $email_datasource,
        Project $project
    ){
        parent::__construct($email_datasource, $project);
        $this->local_datasource = $datasource;
        $this->mapper_custom_code = new CustomCodeModel2CustomCodeConfigurationMapper;
        $this->mapper_snippet = new ImpreseeSnippetConfigurationModel2ImpreseeSnippetConfiguration;
    }

    public function getSearchBarCustomCodeConfiguration(Store $store){
        try {
            $custom_code_model = $this->local_datasource->getLocalCustomCodeConfiguration($store);
            $mapped_custom_code = $this->mapper_custom_code->mapFrom($custom_code_model);
            return new FulfilledPromise(
                Either::of($mapped_custom_code)
            );    
        } catch(NoDataException $e){
            $empty_config = new CustomCodeConfiguration;
            $empty_config->js_add_buttons = '';
            $empty_config->css_style_buttons = '';
            $empty_config->js_after_load_results_code = '';
            $empty_config->js_before_load_results_code = '';
            $empty_config->js_search_failed_code = '';
            $empty_config->js_press_see_all_code = '';
            $empty_config->js_close_text_results_code = '';
            $empty_config->js_on_open_text_dropdown_code = '';
            return new FulfilledPromise(
                Either::of($empty_config)
            );    
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }    
    }

    public function updateCustomCodeConfiguration(Store $store, CustomCodeConfiguration $configuration){
        $mapped_model = $this->mapper_custom_code->mapTo($configuration);
        try{
            $stored_model = $this->local_datasource->updateLocalCustomCodeConfiguration($store, $mapped_model);
            $mapped_configuration = $this->mapper_custom_code->mapFrom($stored_model);
            return new FulfilledPromise(
                Either::of($mapped_configuration)
            );    
        } catch(CouldNotStoreDataException $e){
            $store_name = $store->url;
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while updating custom code configuration', 
                'Could\'t update custom code configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(
                new Left(new FailureUpdateCustomCodeData)
            );
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }   
    }


    public function getLocalImpreseeSnippetConfiguration(Store $store) {
        try {
            $snippet_model = $this->local_datasource->getLocalImpreseeSnippetConfiguration($store);
            $mapped_snippet_config = $this->mapper_snippet->mapFrom($snippet_model);
            return new FulfilledPromise(
                Either::of($mapped_snippet_config)
            );    
        } catch(NoDataException $e){
            return new FulfilledPromise(
                Either::of($this->getDefaultSnippetConfig())
            );    
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        } 
    }

    private function getDefaultSnippetConfig(){
        $general_config = new ImpreseeSnippetGeneralConfiguration;
        $general_config->load_after_page_render = FALSE;
        $general_config->container_selector = '';
        $general_config->main_color = '#9CD333';
        $general_config->add_search_data_to_url = FALSE;
        $general_config->images_only_loaded_from_camera = FALSE;
        $general_config->disable_image_crop = FALSE;
        $general_config->price_fraction_digit_number = 2;
        $general_config->currency_symbol_at_the_end = FALSE;
        $general_config->on_sale_label_color = '#FF0000';
        $general_config->decimal_separator = ',';
        $general_config->search_by_photo_icon_url =  '';
        $general_config->search_by_sketch_icon_url = '';
        $general_config->use_photo_search = TRUE;
        $general_config->use_sketch_search = TRUE;
        $label_config = new ImpreseeSnippetLabelsConfiguration;
        $label_config->search_results_title = 'Search results';
        $label_config->search_button_label = 'Search';
        $label_config->oops_exclamation = 'Oops...';
        $label_config->error_title = "We didn't expect this at all.";
        $label_config->error_message = 'It seems our system is overheating, please try again later.';
        $label_config->drag_and_drop_image_title = 'Drag & Drop an image or just click here';
        $label_config->drag_and_drop_image_body = "Upload the image you'd like to use to search";
        $label_config->custom_crop_label = 'Custom search';
        $label_config->start_writing_label = 'Start typing to search';
        $label_config->currency_symbol = '$';
        $label_config->search_by_photo_label = 'Search by photo';
        $label_config->search_by_sketch_label = 'Search by drawing';
        $label_config->see_all_results_label = 'See all results';
        $label_config->no_matching_results = "We couldn't find any results for:";
        $label_config->on_sale_label = 'On sale';
        $label_config->result_title_search_by_text = 'Search results for';
        $label_config->number_of_results_label_desktop = 'Displaying {1} results';
        $label_config->number_of_results_label_mobile = 'Displaying {1} results for "{2}"';
        $label_config->filters_title_label_mobile = 'Filters';
        $label_config->clear_filters_label = 'Clear filters';
        $label_config->sort_by_label = 'Sort by';
        $label_config->apply_filters_label_mobile = 'Apply';
        $label_config->try_searching_again_label = "Why don't you try drawing or taking a picture of what you want?";
        $label_config->search_suggestions_label = "Popular searches";
        $label_config->search_recommendations_label = "Recommended products";
        $text_config = new ImpreseeSnippetSearchByTextConfiguration;
        $text_config->use_text = TRUE;
        $text_config->search_delay_millis = 300;
        $text_config->full_text_search_results_container = 'body';
        $text_config->compute_results_top_position_from = 'header';
        $text_config->use_instant_full_search = TRUE;
        $text_config->use_floating_search_bar_button = TRUE;
        $text_config->search_bar_selector = "input[name=q],input[name=s]";
        $text_config->use_search_suggestions = TRUE;
        $text_config->mobile_instant_as_grid = FALSE;
        $text_config->floating_button_location = ImpreseeSnippetSearchByTextConfiguration::BOTTOM_LEFT;
        $snippet_config = new ImpreseeSnippetConfiguration;
        $snippet_config->general_configuration = $general_config;
        $snippet_config->labels_configuration = $label_config;
        $snippet_config->search_by_text_configuration = $text_config;
        return $snippet_config;
    }

    public function updateImpreseeSnippetConfiguration(Store $store, ImpreseeSnippetConfiguration $config){
        $mapped_model = $this->mapper_snippet->mapTo($config);
        try{
            $stored_model = $this->local_datasource->updateLocalImpreseeSnippetConfiguration($store, $mapped_model);
            $mapped_configuration = $this->mapper_snippet->mapFrom($stored_model);
            return new FulfilledPromise(
                Either::of($mapped_configuration)
            );    
        } catch(CouldNotStoreDataException $e){
            $store_name = $store->url;
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while updating Impresee snippet configuration', 
                'Could\'t update Impresee snippet configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(
                new Left(new FailureUpdateImpreseeSnippetData)
            );
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }

    public function removeCustomCodeConfiguration(Store $store){
        try{
            $this->local_datasource->removeCustomCodeLocalData($store);
            return new FulfilledPromise(
                Either::of(NULL)
            );    
        } catch(CouldNotRemoveDataException $e){
            $store_name = $store->url;
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while removing custom code configuration', 
                'Could\'t remove custom code configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(
                new Left(new FailedAtRemovingDataFailure)
            );
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }


    public function removeSnippetConfiguration(Store $store){
        try{
            $this->local_datasource->removeSnippetLocalData($store);
            return new FulfilledPromise(
                Either::of(NULL)
            );    
        } catch(CouldNotRemoveDataException $e){
            $store_name = $store->url;
            $email_data = new ErrorEmailModel(
                $store_name,
                'Error while removing impresee snippet configuration', 
                'Could\'t remove impresee snippet configuration for '.$store_name,
                $this->project->getProjectName()
            );
            $this->email_datasource->sendErrorEmail($email_data);
            return new FulfilledPromise(
                new Left(new FailedAtRemovingDataFailure)
            );
        } catch(\Throwable $t){
            $store_name = $store->url;
            $this->sendErrorEmail($t,
                'Error while retrieving Impresee data from Wordpress options', 
                $store_name
            );
            return new FulfilledPromise(new Left(new UnknownFailure));
        }
    }
}

