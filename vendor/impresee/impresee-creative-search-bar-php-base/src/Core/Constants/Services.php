<?php
    namespace Impresee\CreativeSearchBar\Core\Constants;

interface Services {

    public function getAdminEmailUrl();
    public function getEventEmailUrl();
    public function getConsoleUrl();
    public function getCreateOwnerUrl();
    public function getCreateCatalogUrl();
    public function getUpdateCatalogUrl();
    public function getRemoveDataUrl();
    public function getCatalogStatusUrl();
    public function getPlatformCatalogPath();
    public function getSubscriptionDataUrl();
    public function getSubscriptionStatusUrl();
    public function getCreateAccountUrl();
    public function getNotifyChangePluginStatusUrl();
}
