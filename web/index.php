<?php
// web/index.php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/views',
));

$app->get('/', function () use ($app) {
  $return = array();
  $key = 'c1427a3ee0f9b8fd';

  $ip = $app['request']->server->get('REMOTE_ADDR');
  $query='autoip';
  $url = "http://api.wunderground.com/api/$key/conditions/forecast/yesterday/q/$query.json?geo_ip=$ip";

  $return[] = $url;

  $parsed_json = json_decode(file_get_contents($url),true);
  $location = $parsed_json['current_observation']['display_location']['full'];
  $temp_f =   $parsed_json['current_observation']['temp_f'];

  $current = array();
  foreach(array('weather','temperature_string','temp_f','feelslike_f','local_epoch') as $field)
  {
    $current[$field] = $parsed_json['current_observation'][$field];
  }
  $tomorrow = array();
  $tomorrow['high'] = $parsed_json['forecast']['simpleforecast']['forecastday'][1]['high']['fahrenheit'];
  $tomorrow['low'] = $parsed_json['forecast']['simpleforecast']['forecastday'][1]['low']['fahrenheit'];

  $yesterday = array();
  $closestIndex = null;
  $closestTime = null;
  $target = $current['local_epoch'] % (60*60*24);
  foreach($parsed_json['history']['observations'] as $index => $ob)
  {
    $date = date('U',mktime($ob['date']['hour'],$ob['date']['min'],0,$ob['date']['mon'],$ob['date']['mday'],$ob['date']['year']));
    $date = $date % (60*60*24);
    if ($closestIndex === null || abs($date - $target) < abs($closestTime - $target))
    {
      $closestIndex = $index;
      $closestTime = $date;
    }
  }
  $yesterday = $parsed_json['history']['observations'][$closestIndex]['tempi'];

  $return[] = $current;
  $return[] = $tomorrow;
  $return[] = $closestTime;
  $return[] = $closestIndex;
  $return[] = $yesterday;
  
  $diff = $temp_f - $yesterday;
  $diffWord = $diff > 0 ? 'warmer' : 'cooler';
  $diff = abs($diff);

  $return[] = "Its $temp_f right now. It was $yesterday yesterday at this time, so it's $diff degrees $diffWord right now.";


  $return[] = "Current temperature in ${location} is: ${temp_f}&#186;F";
  $return[] = $parsed_json;
  return '<pre>' . var_export($return,true) . '</pre>';

});

$app->run();
