<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\General;
use Impresee\CreativeSearchBar\Domain\UseCases\GetImpreseeConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetIndexationConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\UpdateIndexationConfiguration;
use Impresee\CreativeSearchBar\Domain\UseCases\GetCreateImpreseeAccountUrl;
use SEE\WC\CreativeSearch\Presentation\Settings\ISettings;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;
use SEE\WC\CreativeSearch\Presentation\Settings\BaseSettings;
use Impresee\CreativeSearchBar\Domain\Entities\CatalogIndexationConfiguration;
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;
use Impresee\CreativeSearchBar\Core\Constants\CreateAccountUrlType;

if (! defined('ABSPATH')){
    exit;
}

class GeneralSettings extends BaseSettings{
    private $get_config;
    private $update_indexation_config;
    private $get_indexation_config;
    private $configuration_data;
    private $get_create_account_url;

    function __construct(GetImpreseeConfiguration $get_config,
        UpdateIndexationConfiguration $update_indexation_config, GetIndexationConfiguration $get_indexation_config,
        GetCreateImpreseeAccountUrl $get_create_account_url,
        PluginUtils $configuration_data,
        Callbacks $callbacks
    ) {
        parent::__construct(SettingsNames::GENERAL, $callbacks);
        $this->configuration_data = $configuration_data;
        $this->get_config = $get_config;
        $this->update_indexation_config = $update_indexation_config;
        $this->get_indexation_config = $get_indexation_config;
        $this->get_create_account_url = $get_create_account_url;
    }

    private function getSettingsOrDefault(){
        $config_data_promise = $this->get_indexation_config->execute($this->configuration_data->getStore());
        $config_data_either = $config_data_promise->wait();
        $config_data = $config_data_either->either(
            function ($failure) { 
                $new_configuration = new CatalogIndexationConfiguration;
                $new_configuration->show_products_with_no_price = TRUE;
                $new_configuration->index_only_in_stock_products = TRUE;
                return $new_configuration;
            },
            function ($impresee_data) { return $impresee_data; }
        );
        return $config_data;
    }

    public function get( ){
        $config_data = $this->getSettingsOrDefault();
        $data_array = array(
            $this->config_section_id => array(
                'show_products_no_price' =>  $config_data->show_products_with_no_price,
                'catalog_stock'          =>  $config_data->index_only_in_stock_products,
            )
        );
        return $data_array;
    }

    public function save($data) {
        $sanitized_post = sanitize_post($data, 'db');
        $new_settings = $sanitized_post[$this->config_section_id];
        $new_configuration = new CatalogIndexationConfiguration;
        $new_configuration->show_products_with_no_price = $new_settings["show_products_no_price"] == "enabled";
        $new_configuration->index_only_in_stock_products =$new_settings["catalog_stock"] == "in_stock";
        $impresee_data_promise = $this->update_indexation_config->execute(
            $this->configuration_data->getStore(), 
            $new_configuration
        );
        $impresee_either = $impresee_data_promise->wait();
        $success = $impresee_either->either(
            function ($failure) { return FALSE; },
            function ($impresee_data) { return TRUE; }
        );
        return $success;
    }

    public function saveFormAndRedirect( ){
        $success = $this->save($_POST);
        $page_id = $this->configuration_data->getPluginPageId();
        $tab = SettingsNames::GENERAL;
        $error_update = "";
        if (!$success){
            $error_update = urlencode("We could not update your configuration. Please try again later.");
        }
        wp_redirect(admin_url("admin.php?page={$page_id}&tab={$tab}&error={$error_update}"));
    }


    public function addExtraElementsToSettings(){
        $id = "go_to_dashboard";
        $go_dashboard_promise = $this->get_create_account_url->execute($this->configuration_data->getStore(), CreateAccountUrlType::GO_TO_DASHBOARD);
        $go_dasboard = $go_dashboard_promise->wait();
        $dashboard_url =  $go_dasboard->either(
                                function($failure) {
                                    return NULL;
                                },
                                function($create_url) {
                                    return $create_url;
                                }

                            );
        printf( '<p style="font-size: 15px;font-weight: bold;text-decoration: underline;">Checkout our Dashboard PRO</p>' );
        printf( '<p><button id="%1$s" type="button">Go to dashboard</button>', $id, $this->config_section_id );
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function go_to_dashboard<?php echo $id; ?>() {
                    var goToDashboard = document.createElement('a');
                    goToDashboard.href = '<?php echo $dashboard_url->url; ?>';
                    goToDashboard.target = '_blank';
                    goToDashboard.rel = 'noopener noreferrer';
                    goToDashboard.click();
                }
                $('#<?php echo $id; ?>').click(go_to_dashboard<?php echo $id; ?>);
            } );
            </script>
        <?php
        printf( '<p class="description">%s</p>', 'Click here to visit your Impresee dashboard' );
    }

    /**
    * Add general settings using add_settings_field
    */
    public function init_settings() {
        $impresee_data_promise = $this->get_config->execute($this->configuration_data->getStore());
        $impresee_either = $impresee_data_promise->wait();
        $impresee_data = $impresee_either->either(
            function ($failure) { return NULL; },
            function ($impresee_data) { return $impresee_data; }
        );
        $catalog_data = $impresee_data == NULL ? NULL : $impresee_data->catalog;
        $catalog_code = $catalog_data == NULL ? NULL : $catalog_data->catalog_code; 
        $config_data = $this->getSettingsOrDefault();
        $page = $option_group = $option_name = $this->config_section_id;

        $settings_fields = array(
            array(
                'type'      => 'section',
                'id'        => 'general_settings',
                'title'     => 'General settings',
                'callback'  => 'section',
            ),
            array(
                'type'      => 'setting',
                'id'        => 'catalog_status',
                'title'     => 'Catalog status',
                'callback'  => 'processing_button',
                'section'   => 'general_settings',
                'args'      => array(
                    'owner'         => $impresee_data == NULL ? NULL : $impresee_data->owner_code,
                    'option_name'   => $option_name,
                    'id'            => 'check_catalog_status',

                ),
            ),
            array(
                'type'      => 'setting',
                'id'        => 'show_products_no_price',
                'title'     => 'Show/Hide products with no price',
                'callback'  => 'select',
                'section'   => 'general_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'show_products_no_price',
                    'options'       => array(
                        'enabled'   => 'Show',
                        'disabled'  => 'Hide',
                    ),
                    'description' => 'Gives you te option to show/hide products with no price from the search results',
                    'current' => $config_data->show_products_with_no_price ? 'enabled' : 'disabled',

                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'catalog_stock',
                'title'     => 'What kind of products would you like to index?',
                'callback'  => 'select',
                'section'   => 'general_settings',
                'args'      => array(
                    'option_name'   => $option_name,
                    'id'            => 'catalog_stock',
                    'options'       => array(
                        'all'  => 'All products',
                        'in_stock'   => 'Only in stock products',
                    ),
                    'current' => $config_data->index_only_in_stock_products ? 'in_stock' : 'all',
                )
            ),
            array(
                'type'      => 'setting',
                'id'        => 'update_catalog_setting',
                'title'     => 'Update your catalog',
                'callback'  => 'update_button',
                'section'   => 'general_settings',
                'args'      => array(
                    'catalog_status_id' => 'check_catalog_status',
                    'description' => 'Click here to re-index your catalog. Use this button to manually update the search engine.',
                    'owner'         => $impresee_data == NULL ? NULL : $impresee_data->owner_code,
                    'catalog'       => $catalog_code,
                    'option_name'   => $option_name,
                    'id'            => 'update_catalog',

                ),
            ),
        );
        $this->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
    }
}
