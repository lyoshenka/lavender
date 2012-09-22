<?php
// http://wiki.nginx.org/PHPFcgiExample
// If fcgi dies, run "php-cgi -b 127.0.0.1:9000"

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../myApp.php';

$app = new myApp();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/views',
));

$app->get('/', function () use ($app) {
  $key = 'c1427a3ee0f9b8fd';

  $ip = $app['request']->server->get('REMOTE_ADDR');
  $query='autoip';
  $url = "http://api.wunderground.com/api/$key/conditions/forecast/yesterday/q/$query.json?geo_ip=$ip";

  $apiData = json_decode(file_get_contents($url),true);
  $data = $app->parseData($apiData);

  $diff = $data['currentTemp'] - $data['yesterdayTemp'];

  $intDiff = max(10,min(-10,round($diff))); // cap the diff at 10 for the bg color
  $bodyClasses = $intDiff == 0 ? '' : (($intDiff > 0 ? 'warmer' : 'colder') . ' by' . abs($intDiff));

  $debug = compact('data','apiData');

  return $app['twig']->render('home.twig.html', array(
    'data' => $data,
    'adjective' => $app->getAdjective($diff),
    'bodyClasses' => $bodyClasses,
    'debug' => var_export($debug,true)
  ));
});

$app->run();
