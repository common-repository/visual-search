<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;

class OwnerModel implements Serializable {
    public $owner_code;

    public function toArray(){
        $impresee_data = array(
            'owner_code' => $this->owner_code
        );
        return $impresee_data;
    }

    public function loadDataFromArray(Array $impresee_data){
        if (
            !isset($impresee_data['owner_code'])
        ){
            throw new NoDataException;
        }
        $this->owner_code = $impresee_data['owner_code'];
    }
}