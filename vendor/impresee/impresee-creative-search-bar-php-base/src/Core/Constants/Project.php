<?php
    namespace Impresee\CreativeSearchBar\Core\Constants;

interface Project{
    
    public function getVersion();
    public function getProjectName();
    public function getIsDebug();
    public function getCatalogFormat();
    public function getTrialDays();
}
