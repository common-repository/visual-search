<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
include_once( 'impresee-creativesearch.php' );
use SEE\WC\CreativeSearch\Presentation\Settings\ActionNames;
do_action(ActionNames::REMOVE_ALL_DATA);
