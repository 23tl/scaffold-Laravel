<?php

namespace App\Logging;

use App\Facades\Json\Json;
use Illuminate\Support\Facades\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class JsonLineFormatter extends LineFormatter
{
    public function format(LogRecord $record): string
    {
        $server = Request::server();
        $port = $server['SERVER_PORT'] ?? 80;
        $protocol = $port == 443 ? 'https://' : 'http://';
        $host = $server['HTTP_HOST'] ?? '';
        $uri = $server['REQUEST_URI'] ?? '';
        $referer = $server['HTTP_REFERER'] ?? '';
        $ua = $server['HTTP_USER_AGENT'] ?? '';
        $url = $protocol.$host.$uri;
        $cookies = Json::encode($_COOKIE ?? '');
        $authorization = $server['HTTP_AUTHORIZATION'] ?? '';
        if ($ua === 'Symfony') {
            $url = 'artisan';
            $referer = 'artisan';
            $ua = 'artisan';
        }
        $datetime = now()->toDateTimeString();
        $output = '{"app": "%app%", "authorization": "'.$authorization.'", "datetime": "'.$datetime.'", "timestamp": "%datetime%", "url": "'.$url.'", "UA": "'.$ua.'", "referer": "'.$referer.'", "uuid": %uuid%, "domain": "%domain%", "channel": "%channel%", "level": "%level_name%", "message": "%message%", "context": [%context%], "extra": %extra%, "cookies": '.$cookies.'}'."\n";
        $vars = (new NormalizerFormatter())->format($record);
        $vars['app'] = config('app.name');
        $vars['channel'] = $vars['context']['channel'] ?? 'local';
        $vars['uuid'] = app('uuid');
        $vars['domain'] = $host;
        foreach ($vars['extra'] as $var => $val) {
            if (str_contains($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }
        if (! empty($vars['context']['exception'])) {
            $vars['context'] = $vars['context']['exception'];
            if (isset($vars['context']['trace'])) {
                unset($vars['context']['trace']);
            }
            if (isset($vars['context']['previous'])) {
                unset($vars['context']['previous']);
            }
        }
        if (str_contains($output, '%')) {
            $output = preg_replace('/%(?:extra|context)\..+?%/', '', $output);
        }
        foreach ($vars as $var => $val) {
            if (str_contains($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }
        if (str_contains($output, '%')) {
            $output = preg_replace('/%(?:extra|context)\..+?%/', '', $output);
        }

        return $output;
    }
}
