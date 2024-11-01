<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\SearchTypes;

class ImpreseeSearchByPhoto implements ImpreseeApplicationType {
    public function toString(){
        return SearchTypes::PHOTO;
    }
}