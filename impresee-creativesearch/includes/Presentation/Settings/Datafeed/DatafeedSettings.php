<?php
namespace SEE\WC\CreativeSearch\Presentation\Settings\Datafeed;
use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;
use SEE\WC\CreativeSearch\Presentation\Settings\SettingsNames;

if (! defined('ABSPATH')){
    exit;
}

class DatafeedSettings {
    private $plugin_utils;

    function __construct(PluginUtils $plugin_utils) {
        $this->plugin_utils = $plugin_utils; 
        add_action( 'see_wccs_settings_output_'.SettingsNames::DATAFEED, array( $this, 'output' ), 10, 0 );
    }


    /*
    * Output the form content that will shown on the screen
    */
    public function output( ) {
        $catalog_code = $this->plugin_utils->getStore()->catalog_generation_code;
        $catalog_url =  get_rest_url( null, $this->plugin_utils->getUriCatalog() ) . $catalog_code.'?page=1'; 
        $datafeed_settings =  <<<EOT
<div style="padding: 3%;font-size: 1rem;">
<span>To get our visual search services ready to work on your site we extract a datafeed with your products.
We do this by creating a public URL with the data, which we use to keep our services constantly updated.</span>
<br>
<h3>You can find the URL with your datafeed below. You can also download the file.</h3>
<span style="font-weight:bold">Datafeed URL: </span><a href="$catalog_url">$catalog_url</a>

<br>
<br>
<br><a href="$catalog_url" download="datafeed.xml" class="download-datafeed" style="border-color: #23282d;border-style: solid;padding: 10px;border-width: 1px;border-radius: 20px;background-color: #ccc;text-decoration: none;color: #23282d;font-weight: bold;">Download your datafeed</a>
</div>
EOT;
     
        echo $datafeed_settings;
    } 

}
