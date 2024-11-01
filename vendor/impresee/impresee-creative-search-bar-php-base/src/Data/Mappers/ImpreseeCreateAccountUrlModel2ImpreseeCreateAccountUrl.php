<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeCreateAccountUrlModel;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeCreateAccountUrl;

class ImpreseeCreateAccountUrlModel2ImpreseeCreateAccountUrl {

    public function mapFrom(ImpreseeCreateAccountUrlModel $from){
        $url_data = new ImpreseeCreateAccountUrl;
        $url_data->url = $from->url;
        return $url_data;
    }
    public function mapTo(ImpreseeCreateAccountUrl $to){
        $url_model = new ImpreseeCreateAccountUrlModel;
        $url_model->url = $to->url;
        return $url_model;
    }
}
