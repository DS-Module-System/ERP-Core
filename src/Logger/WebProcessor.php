<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-12-06
 * Time: 08:45
 */

namespace App\Logger;


use Monolog\LogRecord;

class WebProcessor extends \Monolog\Processor\WebProcessor
{
    protected array $extraFields = [
        'url'         => 'REQUEST_URI',
        'ip'          => 'REMOTE_ADDR',
        'http_method' => 'REQUEST_METHOD',
        'server'      => 'SERVER_NAME',
        'referrer'    => 'HTTP_REFERER',
        'status_code' => 'REDIRECT_STATUS',
    ];

    public function __invoke(LogRecord $record): LogRecord {

        $record['extra']['$_SERVER'] = "<pre>".print_r($this->serverData, true)."</pre>";
        $record['extra']['$_POST'] = '';
        if(!empty($_POST)) {
            $record['extra']['$_POST'] = "<pre>" . print_r($_POST, true) . "</pre>";
        }

        return $record;
    }

}