<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require 'src/facebook.php';

// Create our Application instance
$facebook = new Facebook(array(
  //don't display this. function to call this
  'appId'  => 'xxxxxxx',
  'secret' => 'xxxxxxx',
));

// Get User ID
$user = $facebook->getUser();
if ($user) {
  // if logged in
  $params = array( 'next' => 'http://mattsauerbach.com/fb/logout.php' );
  $logoutUrl = $facebook->getLogoutUrl($params);
} else {
 //$statusUrl = $facebook->getLoginStatusUrl();
  
  //create loginURl 
  //asking for additional permission -> manage pages 
  $params= array('scope' => 'manage_pages,publish_stream,read_stream,read_insights,publish_actions', 'redirect_uri' => 'http://mattsauerbach.com/fb/main.php');

  $loginUrl = $facebook->getLoginUrl($params);
}
// This call will always work since we are fetching public data.
//$naitik = $facebook->api('/mauerbac');


//$access_token = $facebook->getAccessToken();
//print "access token".$access_token;

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>Weather App</title>

  <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">


    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
   <div class="container">
 <div class="jumbotron">
      <center>
        <h1>Welcome to Weather App</h1> <br><br>
        <p class="lead">Post the current temperature from any city directly to your Facebook Page wall.</p>
      </center>
    <?php if ($user): ?>
   <p>You are currently signed in with Facebook.<p>
    <a href='main.php'> Go to Page Dashboard</a> or <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
       <center> <a class="btn btn-lg btn-success" href="<?php echo $loginUrl; ?>" role="button">Get started with Facebook</a></center>
        <h3>App details: </h3>
          <ul>
              <li>Must login via Facebook as a Page owner with proper permissions. </li>
              <li>You can post the temperature directly to your page or a standard status update.</li>
              <li>View 10 most recent published and unpublished page posts, as well as number of impressions.</li>
              <li>The app requires these permissions: <br>
                <ul>
                  <li> manage_pages - view Facebook Pages managed by user</li>
                   <li> publish_stream - ability to publish posts to Facebook page</li>
                   <li> read_stream - ability to read unpublished page posts</li>
                   <li> read_insights - ability to view Page insights</li>
                   <li> publish_actions - ability to an unpublished page post</li>
                </ul>
              <li> Pulls weather data from <a href="http://www.wunderground.com/"> Wunderground API </a>.</li>
              <li> Created by <a href="https://www.facebook.com/mauerbac"> Matt Auerbach </a>. </li>
          </ul>
        </div>

    <?php endif ?>
    </div>
</div>

  </body>
</html>
