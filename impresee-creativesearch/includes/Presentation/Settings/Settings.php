<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings;

use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfigurationStatus;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateImpreseeCatalog;
use Impresee\CreativeSearchBar\Domain\UseCases\GetCreateImpreseeAccountUrl;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeSubscriptionData;
use SEE\WC\CreativeSearch\Presentation\Settings\ActionNames;
use SEE\WC\CreativeSearch\Presentation\Onboarding\StepNames;
use SEE\WC\CreativeSearch\Presentation\Settings\General\GeneralSettings;
use SEE\WC\CreativeSearch\Presentation\Models\PresentationStorageConstants;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use Impresee\CreativeSearchBar\Core\Constants\Services;
use Impresee\CreativeSearchBar\Core\Constants\CreateAccountUrlType;
use SEE\WC\CreativeSearch\Presentation\Utils\WooProject;

if (! defined('ABSPATH')){
    exit;
}

class Settings {
    private $add_menu_result;
    private $required_permission;
    private $callbacks;
    private $get_impresee_configuration;
    private $get_configuration_status;
    private $get_create_account_url;
    private $get_impresee_subscription_data;
    private $update_catalog;
    private $configuration_data;
    private $plugin_utils;
    private $services;

    function __construct(
        PluginUtils $configuration_data, 
        GetImpreseeConfiguration $get_impresee_configuration,
        GetImpreseeConfigurationStatus $get_configuration_status,
        GetCreateImpreseeAccountUrl $get_create_account_url,
        GetImpreseeSubscriptionData $get_impresee_subscription_data,
        UpdateImpreseeCatalog $update_catalog,
        Callbacks $callbacks,
        PluginUtils $plugin_utils,
        Services $services
    ){
        $this->configuration_data = $configuration_data;
        $this->update_catalog = $update_catalog;
        $this->get_configuration_status = $get_configuration_status;
        $this->get_impresee_configuration = $get_impresee_configuration;
        $this->get_create_account_url = $get_create_account_url;
        $this->get_impresee_subscription_data = $get_impresee_subscription_data;
        $this->required_permission = 'manage_woocommerce';
        $this->callbacks = $callbacks;
        $this->plugin_utils = $plugin_utils;
        $this->services = $services;
        // we use the add_menu action to add our plugin config to the menu, we give it low priority so it is added last
        add_action( 'admin_menu', array( $this, 'add_menu' ), 999 );
        // Links on plugin page
        add_filter( 'plugin_action_links_'.SEE_WCCS()->plugin_basename, array( $this, 'add_settings_link' ) );
        // TODO: add support links
        add_filter( 'option_page_capability_' . $this->configuration_data->getPluginPageId(), array( $this, 'get_required_permission' ) );

        // show settings page content
        add_action( 'see_wccs_show_settings_page', array( $this, 'show_setting_page_content' ) );
    }

    /**
    * Adds CreativeSearch configuration menu to the main WooCommerce menu
    */
    public function add_menu() {
        $icon = $this->configuration_data->getPluginUrl().'/impresee-creativesearch/includes/assets/icons/impresee_icon_pluginx20.png';
        // we add our configuration menu as a submenu of WooCommerce's menu
        // capability: manage_woocommerce
        // function to generate the output of the setting page: callable this->settings_page
        $this->add_menu_result = add_menu_page(
            'Creative Search Bar & Filters',
            'Impresee\'s Smart Search Bar',
            $this->required_permission,
            $this->configuration_data->getPluginPageId(),
            array( $this, 'settings_page' ),
            $icon

        );
    }

    public function get_required_permission( ){
        return $this->required_permission;
    }

    /**
    * Outputs the settings page for CreativeSearch
    */
    public function settings_page() {
        $store = $this->configuration_data->getStore();
        if ($store == NULL){
            do_action(ActionNames::SHOW_ERROR);
            return;
        }
        if(!$store->hasValidUrl() && !WooProject::DEBUG){
            do_action(ActionNames::SHOW_INVALID_HOST_ERROR);
            return;
        }
        $connected_to_impresee = get_option('see_wccs_connected_to_impresee');
        $current_impresee_data = get_option('see_wccs_impresee_data', array());
        // Retrocompatibility with those who have the plugin already working
        if ($connected_to_impresee || (isset($current_impresee_data['owner_code']) && isset($current_impresee_data['impresee_catalog_code']))){
            $this->show_setting_page_content();
            return;
        }
        $configuration_status_promise = $this->get_configuration_status->execute($store);
        $configuration_status_either = $configuration_status_promise->wait();
        $configuration_status_either->either(
            function($failure){
                // This means that we haven't registered any data => we start onboarding process
                $active_step = isset($_GET[ 'step' ]) && sanitize_key($_GET[ 'step' ]) != NULL ? sanitize_key( $_GET[ 'step' ] ) : StepNames::WELCOME;
                switch ($active_step) {
                    case StepNames::WELCOME:
                        do_action(ActionNames::VISIT_WELCOME_SCREEN);
                        break;
                    case StepNames::SELECT_PRODUCT_TYPE:
                        do_action(ActionNames::VISIT_CHOOSE_PRODUCTS);
                        break;
                    case StepNames::PROCESSING:
                        do_action(ActionNames::VISIT_PROCESSING_PRODUCTS);
                        break;
                    default:
                        do_action(ActionNames::SHOW_ERROR);
                        break;
                }
            },
            function($status) use($store){
                $active_step_error = isset($_GET[ 'step' ]) && sanitize_key($_GET[ 'step' ]) == 'error';
                if ($active_step_error){
                    do_action(ActionNames::SHOW_ERROR);
                    return;
                }
                if (!$status->catalog_processed_once && $status->sent_catalog_to_update){
                    do_action(ActionNames::VISIT_PROCESSING_PRODUCTS);
                } else if(!$status->sent_catalog_to_update){
                    $impresee_data_promise = $this->get_impresee_configuration->execute($store);
                    $impresee_either = $impresee_data_promise->wait();
                    $impresee_data = $impresee_either->either(
                        function($failure){
                            return NULL;
                        }, 
                        function($data){
                            return $data;
                        }
                    );
                    if(!$impresee_data){
                        do_action(ActionNames::SHOW_ERROR);
                        return;
                    }
                    $update_data_promise = $this->update_catalog->execute($impresee_data, $store);
                    $update_data = $update_data_promise->wait();
                    $update_data->either(
                        function($failure){},
                        function(){
                             do_action(ActionNames::VISIT_PROCESSING_PRODUCTS);
                        }
                    ); 
                }
                else {
                    $this->show_setting_page_content();
                }
            }
        );
    }

    private function getTrialDaysLeftText($trial_days_left){
        if ($trial_days_left < 0){
            return '0 trial days left';
        } else if ($trial_days_left == 1) {
            return '1 trial day left';
        }
        return $trial_days_left.' trial days left';
    }

    /**
    * Adds a settings link aside the deactivate link in the plugin page
    */
    public function add_settings_link( $links ) {
        $action_links = array(
            'settings' => '<a href="admin.php?page=' . $this->configuration_data->getPluginPageId() . '">Settings</a>',
        );

        return array_merge( $action_links, $links );
    }


    /**
    * Shows the contents of the settings page, this happens when the store is connected to Impresee
    */
    public function show_setting_page_content() {
        $settings_tabs = array (
            SettingsNames::GENERAL   => 'General',
            SettingsNames::LABELS    => 'Labels',
            SettingsNames::ADVANCED  => 'Advanced',
            SettingsNames::SEARCH_BY_TEXT  => 'Search by text',
            SettingsNames::THEME  => 'Style & theme',
            SettingsNames::CHRISTMAS  => 'Christmas Theme',
            SettingsNames::DATAFEED  => 'Datafeed',
            SettingsNames::SEARCH_BUTTONS   => 'Search Buttons'

        );
        $store = $this->configuration_data->getStore();
        $bar_title = '';
        $trial_days_left_title = '';
        $button_url = '#';
        $button_text = '';
        $subscription_data_promise = $this->get_impresee_subscription_data->execute($store);
        $subscription_data = $subscription_data_promise->wait();
        $subscription_data->either(
            function($failure) use (&$bar_title, &$trial_days_left_title, &$button_url, &$button_text) {
                $button_text = 'Unavailable';
            },
            function($subscription) use($store, &$bar_title, &$trial_days_left_title, &$button_url, &$button_text) {
                $trial_days_left_title = $this->getTrialDaysLeftText($subscription->trial_days_left);
                $redirect_type = CreateAccountUrlType::SUBSCRIBE;
                if($subscription->is_subscribed) {
                    $redirect_type = CreateAccountUrlType::MODIFY_PLAN;
                    $button_text = 'Check subscription';
                    $bar_title = "You're currently subscribed to ";
                    $bar_title .= "<span style='font-weight:900;'>";
                    $bar_title .= ($subscription->plan_name.' ($'.$subscription->plan_price.'/mo)');
                    $bar_title .= "</span> ";
                }
                else {
                    $button_text = 'Subscribe now';
                }
                $subscribe_to_plan_promise = $this->get_create_account_url->execute($store, $redirect_type);
                $subscribe_to_plan = $subscribe_to_plan_promise->wait();
                $subscribe_to_plan->either(
                    function($failure) use (&$button_text) {
                        $button_text = 'Unavailable';
                    },
                    function($create_url) use(&$button_text, &$button_url) {
                        $button_url = $create_url->url;
                    }

                );
                if (!$subscription->is_subscribed && $subscription->trial_days_left <= 0) {
                    $bar_title = "<span style='font-weight:900;'>Your trial has expired,</span>";
                    $bar_title .= " in order to continue using ";
                    $bar_title .= "<span style='font-weight:900;'>\"Creative Search Bar & Filters\"</span> ";
                    $bar_title .= "you must subscribe to a plan";
                } 
            }
        );
        $page_id = $this->configuration_data->getPluginPageId();
        $active_tab = isset($_GET[ 'tab' ]) && sanitize_key($_GET[ 'tab' ]) != NULL ? sanitize_key( $_GET[ 'tab' ] ) : SettingsNames::GENERAL;
        $error_message = isset($_GET[ 'error' ]) && sanitize_key($_GET[ 'error' ]) != NULL ? sanitize_text_field( $_GET[ 'error' ] ) : "";
        include('wc-creativesearch-settings-page.php');
    }

    /**
    * Adds and registers a page's settings
    */
    public function add_settings_fields( $settings_fields, $page, $option_group, $option_name ) {
        // we go through each setting
        foreach ( $settings_fields as $settings_field ) {
            if (!isset($settings_field['callback'])) {
                continue;
            } elseif ( is_callable( array( $this->callbacks, $settings_field['callback'] ) ) ) {
                $callback = array( $this->callbacks, $settings_field['callback'] );
            } elseif ( is_callable( $settings_field['callback'] ) ) {
                $callback = $settings_field['callback'];
            } else {
                continue;
            }

            if ( $settings_field['type'] == 'section' ) {
                add_settings_section(
                    $settings_field['id'],
                    $settings_field['title'],
                    $callback,
                    $page
                );
            } else {
                add_settings_field(
                    $settings_field['id'],
                    $settings_field['title'],
                    $callback,
                    $page,
                    $settings_field['section'],
                    $settings_field['args']
                );
            }
        }
        register_setting( $option_group, $option_name, array( $this->callbacks, 'validate' ) );
        add_filter( 'option_page_capability_'.$page, array( $this, 'get_required_permission' ) );
    }

} 
