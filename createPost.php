<?php
require 'src/facebook.php';
// Create our Application instance
$facebook = new Facebook(array(
  //don't display this. function to call this
  'appId'  => 'xxxxxxx',
  'secret' => 'xxxxxxx',
));


$authToken= $_POST['auth'];
$pid= $_POST['pid'];
$msg= $_POST['msg'];
$published= $_POST['publish'];

//print "teaqsd".$published;
//print $authToken;


if($published=="true"){
	$published=True;
	//print "here2";
}else{
	//print "here1";
	$published=False;
}
//print gettype($published);

$error;

$facebook->setAccessToken($authToken);
$path= "/$pid/feed";
$params= array('message'=>$msg,'published'=>$published);
try{
	$ret = $facebook->api($path, "POST", $params);
}catch(FacebookApiException $e) {
  $result = $e->getResult();
  //error_log(json_encode($result));
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