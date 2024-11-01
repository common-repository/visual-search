<?php
    namespace SEE\WC\CreativeSearch\Presentation\Errors\GenericError;
    use SEE\WC\CreativeSearch\Presentation\Utils\PluginUtils;

class ErrorScreen {
    private $utils;


    public function __construct(
        PluginUtils $utils
    ){
        $this->utils = $utils;
        
    }

    public function build(){
        $error_image = $this->utils->getImageUrl('error.jpg');
        include 'wc-error-screen.php';
    }
}