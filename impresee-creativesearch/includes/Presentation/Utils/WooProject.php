<?php
namespace SEE\WC\CreativeSearch\Presentation\Utils;
use Impresee\CreativeSearchBar\Core\Constants\Project;

class WooProject implements Project {
    const VERSION = '5.3.0';
    const PROJECT_NAME = 'Creative search bar WooCommerce '.WooProject::VERSION;
    const DEBUG = FALSE;
    const CATALOG_FORMAT = 'xml_impresee_20';

    public function getVersion(){
        return WooProject::VERSION;
    }
    public function getProjectName(){
        return WooProject::PROJECT_NAME;
    }
    public function getIsDebug(){
        return WooProject::DEBUG;
    }
    public function getCatalogFormat(){
        return WooProject::CATALOG_FORMAT;
    }
    public function getTrialDays(){
        return 15;
    }
}