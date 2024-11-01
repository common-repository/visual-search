<?php
/**
 * Plugin Name:    Impresee’s Smart Search Bar
 * Plugin URI:     https://impresee.com/woocommerce-2/
 * Description:    Implement an advanced and actionable search engine, powered by AI
 * Version:        5.3.0
 * Author:         Impresee Inc.
 * Author URI:     https://impresee.com
 * Developer:      Impresee Inc.
 * Developer URI:  https://impresee.com
 * License:        MIT
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 * WC requires at least: 2.2
 * WC tested up to: 6.6.1
 */

// Loading dependencies from composer
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}
use Impresee\CreativeSearchBar\Data\Models\ErrorEmailModel;
use SEE\WC\CreativeSearch\Presentation\Settings\ActionNames;
use SEE\WC\CreativeSearch\Presentation\Onboarding\StepNames;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Utils\WooProject;
use SEE\WC\CreativeSearch\Presentation\Utils\WooServices;
use SEE\WC\CreativeSearch\Presentation\Settings\SearchButtons\SearchButtonsSettings;
use Impresee\CreativeSearchBar\Core\Constants\SearchTypes;
use ImpreseeGuzzleHttp\Client;
use ImpreseeGuzzleRetry\GuzzleRetryMiddleware;
use ImpreseeGuzzleHttp\HandlerStack;
use Impresee\Psr\Http\Message\ResponseInterface;
use ImpreseeGuzzleHttp\Exception\RequestException;
// It must be defined if we're accesing the file via wordpress
if (! defined('ABSPATH'))
    exit;


if ( !class_exists( 'SEE_WCCS' ) ) :
class SEE_WCCS {

    public $version = WooProject::VERSION;
    public $plugin_basename;
    public $impresee_search_page_id;
    public $uri_catalog;
    public $uri_update_catalog;
    public $uri_register_impresee;
    public $impresee_base;
    public $base_snippet;
    public $email_url;
    public $owner_managment_url;
    public $store;
    

    protected static $_instance = null;
    private $impresee_connector;
    private $container;
    private $labels_settings;
    private $general_settings;
    private $advanced_settings;
    private $search_by_text_settings;
    private $theme_settings;
    private $datafeed_settings;
    private $search_buttons_settings;
    private $christmas_settings;
    private $http_client;
    private $log_handler;

    /**
     * Main Plugin Instance
     *
     * Ensures only one instance of plugin is loaded or can be loaded.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        define( 'IMSEE_PLUGIN_PATH', untrailingslashit(plugin_dir_path( __FILE__ ) ) ) ;
        define( 'IMSEE_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
        // Send an email when there's an error
        set_error_handler(function($errno, $errstr, $errfile, $errline){
            // If the error comes from an outside file
            if (strpos(IMSEE_PLUGIN_PATH, $errfile) == false){
                return;
            }
            $error_string = "[$errno] $errstr.\n Fatal error on line $errline in file $errfile\n";
            $error_string .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
            $error_email = new ErrorEmailModel(
                $this->store->url,
                $errstr, 
                $error_string
            );
            $email_sender = $this->container->get(\Impresee\CreativeSearchBar\Data\DataSources\EmailDataSource::class);
            $email_sender->sendErrorEmail($error_email);
        });
        $dependency_injector = SEE\WC\CreativeSearch\WooDependencyInjectionController::getInstance();
        $this->container = $dependency_injector->getContainer();
        $this->log_handler = $this->container->get(\Impresee\CreativeSearchBar\Core\Utils\LogHandler::class);
        $get_store_information = $this->container->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetStoreInformation::class);
        $store_promise = $get_store_information->execute();
        $store_either = $store_promise->wait();
        $store_either->either(
            function($left){
            },
            function($store){
                $this->store = $store;
            }
        );
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'max_retry_attempts' => 3,
            'retry_on_timeout'   => true,
            'connect_timeout'    => 20.0,
            'timeout'            => 50.0
        ]));
        $this->http_client = new \ImpreseeGuzzleHttp\Client(['handler' => $stack]);
        $this->transient_activation = 'see_wccs_impresee_data_show_notice_installation';
        $this->email_url = 'https://contact.impresee.com/Contact/api/v1/send/event_message';
        $this->uri_catalog = 'impresee/v1/catalog/';
        $this->uri_catalog_status = 'impresee/v1/catalog-status/';
        $this->uri_update_catalog = 'impresee/v1/update-catalog/';
        $this->uri_register_impresee = 'impresee/v1/register-impresee/';
        $this->impresee_search_page_id = 'woo_impresee_creativesearch_settings_page';
        $this->plugin_basename = plugin_basename(__FILE__);
        $this->impresee_base = 'https://console.impresee.com';
        if (WooProject::DEBUG){
            $this->base_snippet = 'https://dev2.impresee.com';    
        } else {
            $this->base_snippet = 'https://cdn.impresee.com';
        }
        $this->owner_managment_url = $this->impresee_base . '/Console/api/v1/rest/platforms/';
        $this->destination_sales = "ventas";
        $this->project = "IMPRESEE_WOOCOMMERCE";
        $this->define( 'IMSEE_ACTIVATE_OWNER', 5);
        $this->define( 'IMSEE_DEACTIVATE_OWNER', 6);
        $this->define( 'IMSEE_DEBUG', WooProject::DEBUG );
        $this->define( 'IMSEE_CREATIVESEARCH_VERSION', $this->version );
        $this->define( 'IMSEE_CREATIVESEARCH_BLACK', 1 );
        $this->define( 'IMSEE_CREATIVESEARCH_WHITE', 2 );
        
        // Send email if the plugin has just been installed
        $entered_before = get_option('see_wccs_entered_before');
        if ( !$entered_before ){
            $site_url = $this->get_site_name();
            $post = array(
                "source_hostname" => $site_url,
                "source_project" => $this->project,
                "destination_group" => $this->destination_sales,
                "event_code" => $site_url . " has installed the woocommerce plugin",
                "event_details" => "Shop has installed our woocommerce plugin " . date("Y.m.d") . " at " . date("h:i:sa") . " " .date_default_timezone_get().' with email: '.$this->store->shop_email,
            );
            if (!WooProject::DEBUG){
                $this->send_email($post);    
            }
            update_option('see_wccs_entered_before', true);
        }
        $this->configure_routes_onboarding();
        $this->configure_routes_settings();

        // Snippet
        add_action('see_wccs_generate_snippet', function(){
            $snippet_generator = $this->container->get(SEE\WC\CreativeSearch\Presentation\Integration\Snippet\ImpreseeSnippet::class);
            $snippet_generator->generate_snippet();
        });
        // Installation
        add_action('admin_notices', function(){
            if( get_transient( $this->transient_activation ) ){
                $plugin_settins_url = 'admin.php?page='.$this->impresee_search_page_id;
                $activation_message = sprintf('<span style="font-weight:900;">Impresee CreativeSearch has been successfully installed and activated!</span> Now it\'s time for you to %sconfigure the plugin%s', "<a href=\"${plugin_settins_url}\">", '</a>' );

                $message = '<div class="notice-success notice is-dismissible"><p>' . $activation_message . '</p></div>';

                echo $message;
                delete_transient($this->transient_activation);
            }
        } );
        add_action( 'before_woocommerce_init', function() {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        } );
        // load classes after all plugins have been loaded
        add_action( 'plugins_loaded', array( $this, 'load_classes' ), 9 );
        add_action( 'rest_api_init', function () {
            $this->configure_routes_catalog();
            $this->configure_routes_get_config();
            $this->configure_routes_post_config();
        } );

        register_activation_hook( __FILE__,  array( $this, 'notify_activation' ) );
        register_deactivation_hook( __FILE__,  array( $this, 'notify_deactivation' ) );
        add_action('woocommerce_order_status_changed', array( $this, 'register_order' ), 10, 1);
        add_action('woocommerce_checkout_order_processed', array( $this, 'register_order' ), 10, 1);
    }
    private function parseCustomerData($order) { 
        if ($order == null) return;
        return 'cid='.urlencode($order->get_customer_id()).'&cfn='.urlencode($order->get_billing_first_name()).'&cln='.urlencode($order->get_billing_last_name()).'&cem='.urlencode($order->get_billing_email());

    }

    private function parseClientDataString($order) {
        return 'ip='.urlencode($order->get_customer_ip_address()).'&ua='.urlencode($order->get_customer_user_agent()).'&store='.urlencode($server_data['HTTP_HOST']);
    }

    private function callConversionUrl($app, $url_data) {

        $register_conversion_endpoint = 'https://api.impresee.com/ImpreseeSearch/api/v3/search/register_prestashop/';
        $conversion_url = $register_conversion_endpoint.$app.'?'.$url_data;
        $this->http_client->requestAsync('GET', 
            $conversion_url,
            []
        )->then(
            function(ResponseInterface $response) use ($conversion_url) {
            },
            function(RequestException $error){
            }
        )->wait();  
    }
    private function parseOrderDates($order) {
        $date_created = $order->get_date_created();
        $date_modified = $order->get_date_modified();
        $date_completed = $order->get_date_completed();
        $date_paid = $order->get_date_paid();
        return 'dcr='.urlencode($date_created).'&dmod='.urlencode($date_modified).'&dcom='.urlencode($date_completed).'&dpad='.urlencode($date_paid);
    }
    private function sendConversionInformation($order, $impresee_data) {
        $apps = $impresee_data->applications;
        $impresee_app = NULL;
        foreach( $apps as $app )
        {
            if($app->search_type->toString() == SearchTypes::TEXT)
            {
                $impresee_app = $app->code;
            }
        }
        if (!$impresee_app) return;
        $products = $order->get_items();
        $order_reference = $order->get_id();
        $order_number = $order->get_order_number();
        $action = 'CONVERSION';
        $event_type = 'woocommerce_1_0';
        $currency = $order->get_currency();
        $payment_method = $order->get_payment_method();
        $payment_method_string = $order->get_payment_method_title();
        $payment_transaction_id = $order->get_transaction_id();
        $order_status = $order->get_status();
        $products_string = $this->parseProductsToString($products);
        $customer_data_string = $this->parseCustomerData($order);
        $client_data_string = $this->parseClientDataString($order);
        $order_dates = $this->parseOrderDates($order);
        $url_data = 'a='.urlencode($action).'&ordsta='.urlencode($order_status).'&evt='.urlencode($event_type).'&curr='.urlencode($currency).'&ref='.urlencode($order_reference).'&onum='.urlencode($order_number).'&'.$products_string.'&'.$customer_data_string.'&'.$client_data_string.'&tdis='.urlencode($order->get_discount_total()).'&tord='.urlencode($order->get_total()).'&pmeth='.urlencode($payment_method).'&pmeths='.urlencode($payment_method_string).'&trid='.urlencode($payment_transaction_id).'&'.$order_dates;
        $this->callConversionUrl($impresee_app, $url_data);
    }
    private function parseProductsToString($products) {
        $variation_ids = array();
        $product_ids = array();
        $quantities = array();
        $totals = array();
        $subtotals = array();
        $subtotal_taxes = array();
        $tax_classes = array();
        $tax_status = array();
        $types = array();
        foreach ( $products as $item_id => $item ) {
            array_push($product_ids, $item->get_product_id());
            array_push($variation_ids, $item->get_variation_id());
            array_push($quantities, $item->get_quantity());
            array_push($totals, $item->get_subtotal());
            array_push($subtotals, $item->get_total());
            array_push($subtotal_taxes, $item->get_subtotal_tax());
            array_push($tax_classes, $item->get_tax_class());
            array_push($tax_status, $item->get_tax_status());
            array_push($types, $item->get_type());
        }
        return 'prodids='.urlencode(join('|', $product_ids)).'&varids='.urlencode(join('|', $variation_ids)).'&qtys='.urlencode(join('|', $quantities)).'&totals='.urlencode(join('|', $totals)).'&sutot='.urlencode(join('|', $subtotals)).'&sutotax='.urlencode(join('|', $subtotal_taxes)).'&taxc='.urlencode(join('|', $tax_classes)).'&taxst='.urlencode(join('|', $tax_status)).'&typ='.urlencode(join('|', $types));
    }
    public function register_order($order_id)
    {

        $order = new \WC_Order( $order_id );
        $get_impresee_data = $this->container->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration::class);
        $impresee_data_promise = $get_impresee_data->execute($this->store, $config_data);
        $impresee_data_either = $impresee_data_promise->wait();
        $impresee_data_data = $impresee_data_either->either(
            function($left){
                return NULL;
            },
            function($data){
                return $data;
            }
        );
        if($impresee_data_data != NULL && $order != null)
        {
            $this->sendConversionInformation($order, $impresee_data_data);
        }
    }

    private function configure_routes_onboarding(){
        // Onboarding
        add_action(ActionNames::VISIT_WELCOME_SCREEN, function(){
            $welcome_screen = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Onboarding\WelcomeScreen\WelcomeScreenOnboarding::class);
            $welcome_screen->build();
        });

        add_action(ActionNames::VISIT_CHOOSE_PRODUCTS, function(){
            $choose_market = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Onboarding\ChooseMarket\ChooseMarketOnboarding::class);
            $choose_market->build();
        });

        add_action(ActionNames::VISIT_PROCESSING_PRODUCTS, function(){
            $processing = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Onboarding\Processing\ProcessingScreenOnboarding::class);
            $processing->build();
        });
        add_action('admin_post_'.StepNames::SELECT_PRODUCT_TYPE, function(){
            $choose_market = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Onboarding\ChooseMarket\ChooseMarketOnboarding::class);
            $choose_market->registerImpresee();
            die();
        });
        add_action('admin_post_'.StepNames::PROCESSING, function(){
            $processing = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Onboarding\Processing\ProcessingScreenOnboarding::class);
            $processing->finishOnboarding();
            die();
        });
        add_action(ActionNames::REMOVE_ALL_DATA, function() {
            $uninstaller = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Uninstallation\ImpreseeUninstaller::class);
            $uninstaller->removeAllData();
        });
        add_action(ActionNames::SHOW_ERROR, function() {
            $error_screen = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Errors\GenericError\ErrorScreen::class);
            $error_screen->build();
        });
        add_action(ActionNames::SHOW_INVALID_HOST_ERROR, function() {
            $error_screen = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Errors\InvalidHostError\ErrorScreen::class);
            $error_screen->build();
        });

    }

    private function configure_routes_settings(){
        // Settings
        add_action('admin_post_'.SettingsNames::GENERAL, function() {
            $this->general_settings->saveFormAndRedirect();
            die();
        });
        add_action('admin_post_'.SettingsNames::LABELS, function() {
            $this->labels_settings->saveFormAndRedirect();
            die();
        });
        add_action('admin_post_'.SettingsNames::ADVANCED, function() {
            $this->advanced_settings->saveFormAndRedirect();
            die();
        });
        add_action('admin_post_'.SettingsNames::SEARCH_BY_TEXT, function() {
            $this->search_by_text_settings->saveFormAndRedirect();
            die();
        });
        add_action('admin_post_'.SettingsNames::THEME, function() {
            $this->theme_settings->saveFormAndRedirect();
            die();
        });
        add_action('admin_post_'.SettingsNames::SEARCH_BUTTONS, function() {
            $this->search_buttons_settings->saveFormAndRedirect();
            die();
        });
        add_action('admin_post_'.SettingsNames::CHRISTMAS, function() {
            $this->christmas_settings->saveFormAndRedirect();
            die();
        });
    }

    private function configure_routes_catalog(){
        register_rest_route('impresee/v1', '/catalog-status/(?P<owner>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $owner_code = $data['owner'];
                $status_getter = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Utils\CatalogStatusGetter::class);
                $status_array = $status_getter->getCatalogState($owner_code);
                $response = new \WP_REST_Response( $status_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/update-catalog/(?P<owner>[a-zA-Z0-9-]+)/(?P<catalog>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $owner_code = $data['owner'];
                $catalog_code = $data['catalog'];
                $status_getter = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Utils\CatalogStatusGetter::class);
                $status_array = $status_getter->updateCatalog($owner_code, $catalog_code);
                $response = new \WP_REST_Response( $status_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route( 'impresee/v1', '/catalog/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array( $this, 'create_catalog' ),
            'permission_callback' => '__return_true',
        ) );
    }

    private function verify_store_code($received_code){
        if ($this->store->catalog_generation_code != $received_code) {
            return FALSE;
        }
        return TRUE;
    }

    private function configure_routes_get_config(){
        register_rest_route('impresee/v1', '/general-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->general_settings->get();
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/advanced-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->advanced_settings->get();
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/labels-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->labels_settings->get();
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/search-by-text-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->search_by_text_settings->get();
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/theme-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->theme_settings->get();
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/search-buttons-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->search_buttons_settings->get();
                if(isset($data_array['see_wccs_search_buttons_settings'])){
                    switch ($data_array['see_wccs_search_buttons_settings']) {
                        case SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_IN_SEARCHBAR:
                            $data_array['see_wccs_search_buttons_settings'] = 'SEARCHBAR';
                            break;
                        case SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE:
                            $data_array['see_wccs_search_buttons_settings'] = 'SHORTCODE_VISUAL';
                            break;
                        case SearchButtonsSettings::IMSEE_CREATIVESEARCH_DISPLAY_VIA_SHORTCODE_WHOLE_BAR:
                            $data_array['see_wccs_search_buttons_settings'] = 'SHORTCODE_COMPLETE';
                            break;
                        default:
                            $data_array['see_wccs_search_buttons_settings'] = 'SEARCHBAR';
                            break;
                    }
                }
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/christmas-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $data_array = $this->christmas_settings->get();
                $response = new \WP_REST_Response( $data_array );
                $response->set_status( 200 );
                $response->header( 'Content-Type', 'application/json' );
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
    }

    private function parse_save_config_result($success){
        if ($success){
            $response = new \WP_REST_Response( array('status' => 0) );
        } else {
            $response = new \WP_REST_Response( array('status' => 1) );
        }
        $response->set_status( 200 );
        $response->header( 'Content-Type', 'application/json' );
        return $response;
    }

    private function configure_routes_post_config(){
        register_rest_route('impresee/v1', '/general-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $success = $this->general_settings->save($data);
                $response = $this->parse_save_config_result($success);
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/advanced-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $success = $this->advanced_settings->save($data);
                $response = $this->parse_save_config_result($success);
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/labels-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $success = $this->labels_settings->save($data);
                $response = $this->parse_save_config_result($success);
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/search-by-text-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $success = $this->search_by_text_settings->save($data);
                $response = $this->parse_save_config_result($success);
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/theme-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $success = $this->theme_settings->save($data);
                $response = $this->parse_save_config_result($success);
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
        register_rest_route('impresee/v1', '/christmas-settings/(?P<store_code>[a-zA-Z0-9-]+)', array(
            'methods' => 'POST',
            'callback' => function($data){
                $store_code = $data['store_code'];
                if(!$this->verify_store_code($store_code)){
                    return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
                }
                $success = $this->christmas_settings->save($data);
                $response = $this->parse_save_config_result($success);
                return $response;
            },
            'permission_callback' => '__return_true',
        ) );
    }

    public function create_catalog( $data ) {
        $received_catalog_code = isset($data['id']) ? $data['id'] : "";
        if ($this->store->catalog_generation_code != $received_catalog_code) {
            return new \WP_Error( 'invalid_catalog_code', 'Invalid catalog code', array( 'status' => 403 ) );
        }
        $get_indexation_configuration = $this->container->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetIndexationConfiguration::class);
        $config_data_promise = $get_indexation_configuration->execute($this->store);
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
                return NULL;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        if($config_data == NULL){
            return new \WP_Error(
                'Could not get indexation configuration', 
                'Could not get indexation configuration', 
                array( 'status' => 500 ) 
            );
        }
        $get_catalog = $this->container->get(\Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeProductsCatalog::class);
        $catalog_promise = $get_catalog->execute($this->store, $config_data);
        $catalog_either = $catalog_promise->wait();
        $catalog_data = $catalog_either->either(
            function($left){
                return NULL;
            },
            function($data){
                return $data;
            }
        );
        if($catalog_data == NULL){
            return new \WP_Error(
                'Error while creating catalog', 
                'Error while creating catalog', 
                array( 'status' => 500 ) 
            );
        }
        header( 'Content-Type:text/xml' );
        $catalog_text = $catalog_data->impresee_catalog_string;
        $clean_text = preg_replace( "/\r|\n/", " ", $catalog_text);
        echo $clean_text;
        exit();        
    }

    /**
     * Define constant if not already set
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
    * Send email notifying plugin activation
    */
    public function notify_activation() {
        if ( !IMSEE_DEBUG ){
            $site_url = $this->get_site_name();
            $post = array(
                "source_hostname" => $site_url,
                "source_project" => $this->project,
                "destination_group" => $this->destination_sales,
                "event_code" =>  $site_url . " has activated the woocommerce plugin",
                "event_details" => "Shop has activated our woocommerce plugin " . date("Y.m.d") . " at " . date("h:i:sa") . " " .date_default_timezone_get(),
            );
            $this->send_email($post);
        }

        $this->changeOsvaldoStatusToActive(true);
        set_transient( $this->transient_activation, true, 5 );
    }

    /**
    * Send email notifying plugin deactivation
    */
    public function notify_deactivation() {
        if ( !IMSEE_DEBUG ){
            $site_url = $this->get_site_name();
            $post = array(
                "source_hostname" => $site_url,
                "source_project" => $this->project,
                "destination_group" => $this->destination_sales,
                "event_code" => $site_url . " has deactivated the woocommerce plugin",
                "event_details" => "Shop has deactivated our woocommerce plugin " . date("Y.m.d") . " at " . date("h:i:sa") . " " .date_default_timezone_get(),
            );
            $this->send_email($post);
        }

        $this->changeOsvaldoStatusToActive(false);
    }

    /**
     * Load the main plugin classes and functions
     */
    public function includes() {
        $this->labels_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\Labels\LabelsSettings::class);
        $this->general_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\General\GeneralSettings::class);
        $this->advanced_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\Advanced\AdvancedSettings::class);
        $this->search_by_text_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\SearchByText\SearchByTextSettings::class);
        $this->theme_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\Theme\ThemeSettings::class); 
        $this->datafeed_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\Datafeed\DatafeedSettings::class);
        $this->christmas_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\Christmas\ChristmasSettings::class);
        $this->search_buttons_settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\SearchButtons\SearchButtonsSettings::class);
        $this->settings = $this->container->get(\SEE\WC\CreativeSearch\Presentation\Settings\Settings::class);
        $this->container->get(\SEE\WC\CreativeSearch\Presentation\Utils\Assets::class);
    }


    /**
     * Instantiate classes when woocommerce is activated
     */
    public function load_classes() {
        if ( $this->is_woocommerce_activated() === false ) {
            add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
            return;
        }

        if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
            add_action( 'admin_notices', array ( $this, 'required_php_version' ) );
            return;
        }

        // all systems ready - GO!
        $this->includes();
    }


    /**
     * Check if woocommerce is activated
     */
    public function is_woocommerce_activated() {
        // get all active plugins, empty array if option is not found
        $blog_plugins = get_option( 'active_plugins', array() );
        $site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

        if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * WooCommerce not active notice.
     *
     * @return string Fallack notice.
     */

    public function need_woocommerce() {
        $error = sprintf('CreativeSearch for WooCommerce requires %sWooCommerce%s to be installed & activated!', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );

        $message = '<div class="error"><p>' . $error . '</p></div>';

        echo $message;
    }


    /**
     * Get the plugin url.
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
    * Send an email to Impresee's servers
    */
    public function send_email( $post_data ) {
        wp_remote_post($this->email_url, array(
          'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
          'body'        => json_encode($post_data),
          'method'      => 'POST',
          'data_format' => 'body',
        ));
    }

    /*
    * Change store status to enabled or disabled
    */
    private function changeOsvaldoStatusToActive( $new_status ) {

        $update_status = $this->container->get(\Impresee\CreativeSearchBar\Domain\UseCases\UpdatePluginStatus::class);
        $update_status_promise = $update_status->execute($this->store, $new_status);
        $update_status_either = $update_status_promise->wait();
        $update_status_either->either(
            function($left){
            },
            function($right){
            }
        );

    }

    public function get_site_name() {
        $site_url = home_url();
        $site_url = str_replace('https://', '', $site_url);
        $site_url = str_replace('http://', '', $site_url);
        return $site_url;
    }

} // class SEE_WCCS

endif; // class_exists

/**
* returns the main instance of SEE_WCCS as a singleton
* @return SEE_WCCS
*/
function SEE_WCCS() {
    return SEE_WCCS::instance();
}

//load plugin
SEE_WCCS();
