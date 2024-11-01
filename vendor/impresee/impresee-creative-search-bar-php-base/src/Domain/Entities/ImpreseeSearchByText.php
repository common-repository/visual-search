<?php
    namespace Impresee\CreativeSearchBar\Domain\Entities;
    use Impresee\CreativeSearchBar\Core\Constants\SearchTypes;

class ImpreseeSearchByText implements ImpreseeApplicationType {
    public function toString(){
        return SearchTypes::TEXT;
    }
}
