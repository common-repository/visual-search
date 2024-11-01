<?php
    namespace Impresee\CreativeSearchBar\Core\Utils;

interface LogHandler {
    const IMSEE_LOG_DEBUG = 'DEBUG';
    const IMSEE_LOG_INFO = 'INFO';
    const IMSEE_LOG_WARNING = 'WARNING';
    const IMSEE_LOG_ERROR = 'ERROR';

    public function writeToLog(String $line, String $type);
}
