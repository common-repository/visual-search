<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;

class PluginVersionModel implements Serializable  {
    public $version;

    public function loadDataFromArray(Array $array){
        if(!array_key_exists('plugin_version',$array)){
            throw new NoDataException;
        }
        $this->version = $array['plugin_version'];
    }

    public function toArray(){
        return array(
            'plugin_version' => $this->version
        );
    }
}