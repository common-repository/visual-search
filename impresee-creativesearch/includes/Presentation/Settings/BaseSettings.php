<?php 
    namespace SEE\WC\CreativeSearch\Presentation\Settings;
    use SEE\WC\CreativeSearch\Presentation\Settings\ISettings;
    use SEE\WC\CreativeSearch\Presentation\Utils\Callbacks;

abstract class BaseSettings implements ISettings {
    private $callbacks;
    protected $config_section_id;
    protected $settings_name;

    function __construct(String $settings_name, Callbacks $callbacks){
        $this->callbacks = $callbacks;
        $this->config_section_id = "see_wccs_".$settings_name;
        $this->settings_name = $settings_name;
        add_action( 'admin_init', array($this, 'init_settings') );
        add_action( 'see_wccs_settings_output_'.$this->settings_name, 
            array( $this, 'build' ), 10, 0 );
    }


        /*
    * Output the form content that will shown on the screen
    */
    public function build( ) {
        echo "<input type=\"hidden\" name=\"action\" value=\"".$this->settings_name."\" />";
        $this->getNecessaryData();
        do_settings_sections( $this->config_section_id );
        $this->addExtraElementsToSettings();
        submit_button();
    } 

    public function getNecessaryData(){
        
    }

    public function addExtraElementsToSettings() {

    }

    /**
    * Adds and registers a page's settings
    */
    protected function add_settings_fields( $settings_fields, $page, $option_group, $option_name ) {
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
        add_filter( 'option_page_capability_'.$page, array( $this, 'get_required_permission' ) );
    }
}