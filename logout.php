<?php

require 'src/facebook.php';

// Create our Application instance
$facebook = new Facebook(array(
  //don't display this. function to call this
  'appId'  => 'xxxxxxx',
  'secret' => 'xxxxxxx',
));


setcookie('fbs_'.$facebook->getAppId(), '', time()-100, '/', 'mattsauerbach.com/fb');
session_destroy();
header('Location: http://mattsauerbach.com/fb/index.php');

?>