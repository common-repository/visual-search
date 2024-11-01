<?php
    namespace Impresee\CreativeSearchBar\Data\Models;

class InformationEmailModel implements EmailModel {
    public $hostname;
    public $source_project;
    public $destination_group;
    public $event_code;
    public $event_details;

    public function __construct(String $hostname, String $destination_group, 
        String $event_code, String $event_details,  String $project_name) {
        $this->hostname = $hostname;
        $this->source_project = $project_name;
        $this->destination_group = $destination_group;
        $this->event_code = $event_code;
        $this->event_details = $event_details;
    }


    public function toJson(){
        return array(
            'source_hostname'   => $this->hostname, 
            'source_project'    => $this->source_project,
            'destination_group' => $this->destination_group,
            'event_code'        => $this->event_code,
            'event_details'     => $this->event_details.' php: '.phpversion()
        );
    }
}