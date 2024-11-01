<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionStatusModel;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSubscriptionStatus;

class ImpreseeSubscriptionStatusModel2ImpreseeSubscriptionStatusMapper {

    public function mapFrom(ImpreseeSubscriptionStatusModel $from){
        $status = new ImpreseeSubscriptionStatus;
        $status->suspended = $from->suspended;
        return $status;
    }
    public function mapTo(ImpreseeSubscriptionStatus $to){
        $status_model = new ImpreseeSubscriptionStatusModel;
        $status_model->suspended = $to->suspended;
        return $status_model;
    }
}