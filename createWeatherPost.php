<?php
require 'src/facebook.php';
// Create our Application instance
$facebook = new Facebook(array(
  //don't display this. function to call this
  'appId'  => 'xxxxxxx',
  'secret' => 'xxxxxxx',
));

//print "hello";
$authToken= $_POST['auth'];
$pid= $_POST['pid'];
$zip= $_POST['zip'];
$published= $_POST['publish'];
//print "hello1";

$file="http://api.wunderground.com/api/20dcb4c82e2bed97/conditions/q/$zip.json";
//print $file;
$json = file_get_contents($file);
$weather = json_decode($json);
$temp= $weather->{'current_observation'}->{'temp_f'};
$location = $weather->{'current_observation'}->{'display_location'}->{'full'};
$msg = "The current temperature in $location is $temp degrees";

if($published=="true"){
	$published=True;
	//print "here2";
}else{
	//print "here1";
	$published=False;
}
$error;

$facebook->setAccessToken($authToken);
$path= "/$pid/feed";
$params= array('message'=>$msg,'published'=>$published);
try{
	$ret = $facebook->api($path, "POST", $params);
}catch(FacebookApiException $e) {
  $result = $e->getResult();
  error_log(json_encode($result));
  $error=true;
  $result=$result['error']['message'];
}

$response = array();
if(!($error)){
	$postID= $ret['id'];
	$response['message']="Post created. Post ID: $postID";  
}else{
	$response['message']="The post was not created + $result";  
}
//print_r($ret);
echo json_encode($response);

?>