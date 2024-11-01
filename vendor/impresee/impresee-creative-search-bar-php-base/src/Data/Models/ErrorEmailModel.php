<?php
    namespace Impresee\CreativeSearchBar\Data\Models;

class ErrorEmailModel implements EmailModel{
    public $hostname;
    public $source_project;
    public $title;
    public $body;
    public $is_error;
    public $throwable_stacktrace;

    public function __construct(String $hostname, String $title, 
        String $body, String $project_name, String $throwable_stacktrace = "") {
        $this->hostname = $hostname;
        $this->source_project = $project_name;
        $this->title = $title;
        $this->body = $body;
        $this->is_error = TRUE;
        $this->throwable_stacktrace = $throwable_stacktrace;
    }

    public function toJson(){
        return array(
            'source_hostname'      => $this->hostname, 
            'source_project'       => $this->source_project,
            'title'                => $this->title,
            'body'                 => $this->body.' php: '.phpversion(),
            'is_error'             => $this->is_error,
            'throwable_stacktrace' => $this->throwable_stacktrace
        );
    }
}
