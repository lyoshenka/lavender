<?php
// web/index.php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->get('/', function () use ($app) {
  $return = array();
  $key = 'c1427a3ee0f9b8fd';

  $ip = $app['request']->server->get('REMOTE_ADDR');

//  $geolocateUrl = 'http://www.geoplugin.net/php.gp?ip=' . $ip;
//  $return[] = $geolocateUrl;
//  $geoData = unserialize(file_get_contents($geolocateUrl));
//  $return[] = $geoData;
//  $query = $geoData['geoplugin_latitude'].','.$geoData['geoplugin_longitude'];

  $query='autoip';
  $url = "http://api.wunderground.com/api/$key/conditions/forecast/yesterday/q/$query.json?geo_ip=$ip";
  $json_string = file_get_contents($url);

  $return[] = $url;

  $parsed_json = json_decode($json_string,true);
  $location = $parsed_json['current_observation']['display_location']['full'];
  $temp_f =   $parsed_json['current_observation']['temp_f'];

  $return[] = $parsed_json;
  $return[] = "Current temperature in ${location} is: ${temp_f}&#186;F";
  return '<pre>' . var_export($return,true) . '</pre>';

});

$app->run();
