<?php
$trace_filename = ytrace_disable();
if (file_exists($trace_filename)) {
    unlink($trace_filename);
}
ini_set('display_errors', 'off');

if (preg_match('/\.(js|css)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // 直接返回请求的文件
}

require './vendor/autoload.php';
require './FuncNav.php';

define('YTRACE_OUTPUT_DIR', rtrim(ini_get('ytrace.output_dir'), '/') . '/');
//define('YTRACE_OUTPUT_DIR', '/tmp/ytrace/');

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/trace', 'get_all_trace');
    $r->addRoute('DELETE', '/trace', 'clear_trace');
    $r->addRoute('GET', '/trace/{id:.+}', 'get_trace');
    $r->addRoute('GET', '/source', 'get_source');
    $r->addRoute('GET', '/file-nav/{id:.+}', 'get_fileNav');
    $r->addRoute('GET', '/func-nav', 'get_funcNav');
    $r->addRoute('GET', '/', 'index');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        // ... call $handler with $vars
        call_user_func($handler, $vars);
        break;
}

function index()
{
    readfile('index.html');
}

function get_all_trace()
{
    if (!is_dir(YTRACE_OUTPUT_DIR)) {
        header("HTTP/1.0 500 Internal Server Error");
        echo YTRACE_OUTPUT_DIR . " does not exists.";
        return;
    }
    $res = [];
	$files = scandir(YTRACE_OUTPUT_DIR);
    foreach ($files as $file) {
        $pathinfo = pathinfo($file);
        if ($pathinfo['extension'] == 'yt') {
            $handle = fopen(YTRACE_OUTPUT_DIR . $file, 'r');
            $info = explode("\t", rtrim(fgets($handle), "\n"));
            $fileSize = filesize(YTRACE_OUTPUT_DIR . $file);
            switch (true) {
                case $fileSize > 1024 *1024:
                    $fileSize = round($fileSize / (1024*1024), 2) . 'M';
                    break;
                case $fileSize > 1024:
                    $fileSize = round($fileSize / 1024, 2) . 'k';
                    break;
            }
            $item = [
                'sapi' => $info[0], 'file' => $pathinfo['filename'],
                'time' => date('Y-m-d H:i:s', filectime(YTRACE_OUTPUT_DIR . $file)),
                'size' => $fileSize
            ];
            if ($info[0] == "fpm-fcgi") {
                $item['method'] = $info[1];
                $item['uri'] = $info[2];
            }
			$res[] = $item;
        }
    }
    usort($res, function ($a, $b) {
        return $a['time'] < $b['time'] ? 1 : -1;
    });
    header('content-type: application/json');
    echo json_encode($res);
}

function get_trace($input)
{
    $filename = YTRACE_OUTPUT_DIR . $input['id'] . '.yt';
    if (file_exists($filename)) {
        readfile($filename);
    } else {
        header("HTTP/1.0 404 Not Found");
    }
}

function get_source()
{
	$filename = $_GET['file'];
    if (file_exists($filename)) {
        readfile($filename);
    } else {
        header("HTTP/1.0 404 Not Found");
    }
}

function clear_trace()
{
    $files = scandir(YTRACE_OUTPUT_DIR);
    foreach ($files as $file) {
        $pathinfo = pathinfo($file);
        if ($pathinfo['extension'] == 'yt') {
            unlink(YTRACE_OUTPUT_DIR . $file);
        }
    }
}

function get_fileNav($input)
{
    $filename = YTRACE_OUTPUT_DIR . $input['id'] . '.yt';
    if (file_exists($filename)) {
        $f = fopen($filename, "r");
        $first = true;
        $trace_files = [];
        while (true) {
            $line = fgets($f);
            if ($line === false) break;
            if ($first) {
                $first = false;
                continue;
            }
            $line = explode("\t", $line);
            $trace_files[$line[0]] = null;
        }
        header('content-type: application/json');
        echo json_encode(array_keys($trace_files));
    } else {
        header("HTTP/1.0 404 Not Found");
    }
}
