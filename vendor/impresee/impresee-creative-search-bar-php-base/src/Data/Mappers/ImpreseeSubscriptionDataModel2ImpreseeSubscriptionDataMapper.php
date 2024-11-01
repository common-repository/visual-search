<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\ImpreseeSubscriptionDataModel;
    use Impresee\CreativeSearchBar\Domain\Entities\ImpreseeSubscriptionData;

class ImpreseeSubscriptionDataModel2ImpreseeSubscriptionDataMapper {

    public function mapFrom(ImpreseeSubscriptionDataModel $from){
        $subscription = new ImpreseeSubscriptionData;
        $subscription->trial_days_left = $from->trial_days_left;
        $subscription->is_subscribed = $from->is_subscribed;
        $subscription->plan_name = $from->plan_name;
        $subscription->plan_price = $from->plan_price;
        return $subscription;
    }
    public function mapTo(ImpreseeSubscriptionData $to){
        $subscription_model = new ImpreseeSubscriptionDataModel;
        $subscription_model->trial_days_left = $to->trial_days_left;
        $subscription_model->is_subscribed = $to->is_subscribed;
        $subscription_model->plan_name = $to->plan_name;
        $subscription_model->plan_price = $to->plan_price;
        return $subscription_model;
    }
}