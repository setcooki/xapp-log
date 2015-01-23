<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

/**
 * Log interface
 *
 * @package Log
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Log_Interface
{
    /**
     * log function receives the log message/object to be processed
     *
     * @param null|string|array|Exception $message expects mixed object
     */
    function log($message);
}