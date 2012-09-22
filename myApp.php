<?php

class myApp extends Silex\Application
{
  public function getAdjective($diff)
  {
    if ($diff > 8)
    {
      return 'Way hotter than';
    }
    elseif ($diff > 5)
    {
      return 'Warmer than';
    }
    elseif ($diff > 2)
    {
      return 'A bit warmer than';
    }
    elseif ($diff >= -2)
    {
      return 'About the same as';
    }
    elseif ($diff >= -5)
    {
      return 'A bit cooler than';
    }
    elseif ($diff >= -8)
    {
      return 'Cooler than';
    }
    else
    {
      return 'Way colder than';
    }
  }


  public function parseData($data)
  {
    $result = array();
    $result['location'] = $data['current_observation']['display_location']['full'];
    
    // Today
    $result['currentTemp'] = $data['current_observation']['temp_f'];
    $result['currentWeather'] = $data['current_observation']['weather'];
    $result['currentFeelsLike'] = $data['current_observation']['feelslike_f'];

    // Tomorrow
    $result['tomorrowHigh'] = $data['forecast']['simpleforecast']['forecastday'][1]['high']['fahrenheit'];
    $result['tomorrowLow'] = $data['forecast']['simpleforecast']['forecastday'][1]['low']['fahrenheit'];


    // Yesterday
    $yesterday = $this->findBestObservation($data['history']['observations'], $data['current_observation']['local_epoch']);
    $result['yesterdayTemp'] = $yesterday['tempi'];

    return $result;
  }

  // go through yesterday's observations and find the one that was at the same time of day as today's
  protected function findBestObservation($observations, $time)
  {
    $closestIndex = null;
    $closestTime = null;
    $target = $time % (60*60*24);
    foreach($observations as $index => $ob)
    {
      $thisDate = date('U',mktime($ob['date']['hour'],$ob['date']['min'],0,$ob['date']['mon'],$ob['date']['mday'],$ob['date']['year']));
      $thisTime = $thisDate % (60*60*24);
      if ($closestIndex === null || abs($thisTime - $target) < abs($closestTime - $target))
      {
        $closestIndex = $index;
        $closestTime = $thisTime;
      }
    }

    return $observations[$closestIndex];
  }
}
