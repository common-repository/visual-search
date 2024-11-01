<?php
    namespace Impresee\CreativeSearchBar\Data\Mappers;
    use Impresee\CreativeSearchBar\Data\Models\PluginVersionModel;
    use Impresee\CreativeSearchBar\Domain\Entities\PluginVersion;

class PluginVersionModel2PluginVersion {
    
    public function mapFrom(PluginVersionModel $model){
        $entity = new PluginVersion;
        $entity->version = $model->version;
        return $entity;
    } 
 
    public function mapTo(PluginVersion $entity){
        $model = new PluginVersionModel;
        $model->version = $entity->version;
        return $model;
    }
}