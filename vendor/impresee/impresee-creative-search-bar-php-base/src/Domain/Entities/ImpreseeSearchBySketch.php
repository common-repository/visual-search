<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\SearchTypes;

class ImpreseeSearchBySketch implements ImpreseeApplicationType {
    public function toString(){
        return SearchTypes::SKETCH;
    }
}