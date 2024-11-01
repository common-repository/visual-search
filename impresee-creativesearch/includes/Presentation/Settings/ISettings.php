<?php
    namespace SEE\WC\CreativeSearch\Presentation\Settings;

interface ISettings {
    public function init_settings();
    public function build();
    public function save($data);
    public function saveFormAndRedirect();
    public function get();
}