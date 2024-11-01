<?php
    namespace Impresee\CreativeSearchBar\Data\Models;
    use Impresee\CreativeSearchBar\Core\Errors\NoDataException;
    use Impresee\CreativeSearchBar\Data\Models\Serializable;

class ImpreseeSubscriptionStatusModel implements Serializable{
    public $suspended;

    public function toArray(){
        $impresee_data = array(
            'suspended' => $this->suspended
        );
        return $impresee_data;
    }

    public function loadDataFromArray(Array $impresee_data){
        if (
            !isset($impresee_data['suspended'])
        ){
            throw new NoDataException;
        }
        $this->suspended = $impresee_data['suspended'];
    }
}