
<?php

$json = file_get_contents("http://api.wunderground.com/api/20dcb4c82e2bed97/conditions/q/10514.json");
//print_r($json);
$weather = json_decode($json);
//print_r($weather);
//print_r($weather);
$temp= $weather->{'current_observation'}->{'temp_f'};
$location = $weather->{'current_observation'}->{'display_location'}->{'full'};
print "location:".$location;
  ?>