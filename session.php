<?php

$app->register(new Silex\Provider\SessionServiceProvider(), array(
  'session.storage.options' => array(
    'name' => 'lavender',
    'cookie_lifetime' => 60*60*24*14
  )
));

$app->post('/setLocation', function() use ($app) {
  $app['session']->set('location', $app['request']->get('location'));
  return $app->redirect('/');
});
