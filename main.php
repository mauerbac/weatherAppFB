<?php
require 'src/facebook.php';
// Create our Application instance
$facebook = new Facebook(array(
  //don't display this. function to call this
  'appId'  => 'xxxxxxx',
  'secret' => 'xxxxxxx',
));

//get vars via GET. Exchange code for Auth token.
//don't need to use -> php sdk handles that
$code= $_GET['code'];
$state= $_GET['state'];

//check if User has authenciated
$user_id = $facebook->getUser();

//If user isn't active . Give error message to sign in 
if (!($user_id)) {
  print "Your session isn't valid. You must authenciated to continue";
  print "<br> <a href='index.php'> Return to Login Page </a>"; 
}else{

  //get access token for user 
  $access_token = $facebook->getAccessToken();
  //get user profile (I want to greet user by name) :) 
  $user_profile = $facebook->api('/me','GET');
  $userName= $user_profile['name'];

  //create logout button if they would like
  $params = array('next' => 'http://mattsauerbach.com/fb/logout.php' );
  $logoutUrl = $facebook->getLogoutUrl($params);


  //call API to 
  $path= "/$user_id/accounts";
  $params= array('asccess_token'=>$access_token);
  
  try {
    $ret = $facebook->api($path, "GET", $params);
  } catch(FacebookApiException $e) {
    $result = $e->getResult();
    error_log(json_encode($result));
    print $result;
  }

  //get response 
  $data= $ret['data'];
  //get number of pages 
  $size = sizeof($data);

  //create array for pages 
  $arrPageNames= array();
  for($i=0;$i<$size;$i++){
    $pageName= $data[$i]['name'];
    array_push($arrPageNames, $pageName);
  }


  //if chosen their fb page
  $chosenPage;
  if (isset($_POST['fbpage']) ){
    $fbPage= $_POST['fbpage'];
    $chosenPage= TRUE;
    //get page ID auth token 
    $authToken= $data[intval($fbPage)]['access_token'];
    $pid= $data[intval($fbPage)]['id'];
    $pageName=$arrPageNames[intval($fbPage)];
  }

  //function to get published page posts
  function getPostsPublished(){
    global $pid, $facebook;
    //get posts
    try {
     $posts = $facebook->api(
        "/$pid/posts?fields=message,object_id&limit=50"
    );
      }catch(FacebookApiException $e) {
      $result = $e->getResult();
      error_log(json_encode($result));
      print $result;
    }

    getViews($posts,$facebook,"Published");
    }


  function getPostsUnpublished(){
    global $pid, $facebook;
    //get posts
    try {
    $posts = $facebook->api(
        "/$pid/promotable_posts?fields=id,message&is_published=false"
    );
      }catch(FacebookApiException $e) {
      $result = $e->getResult();
      error_log(json_encode($result));
      print $result;
    }
    getViews($posts,$facebook,"Unpublished");
  }


  //unique impressions
  function getViews($posts,$facebook,$type){
    //get number of posts 
    $numPosts=sizeof($posts['data']);
    //display 10
    if($numPosts>10){
      $numPostsToExplore=10;
    }else{
      $numPostsToExplore=$numPosts;
    }


    //display error if page doesn't have posts
    if($numPosts<1){
      print "Your page doesn't have any $type posts :(";
    }else{
      //print table to display 
      print"
      <table style='width:300px'>
       <tr>
       <th>Message</th>
       <th># of Views</th>    
       </tr>
      <tr>
      ";
      //iterate through each post
      for($i=0;$i<$numPostsToExplore;$i++){
        //check that post is from a page admin and not a user & ensure a message present
        if(($posts['data'][$i]['to']) || (!($posts['data'][$i]['message']))){
          //we skipped this post, so if possible display an extra
          if($numPostsToExplore<$numPosts){
            $numPostsToExplore++;
          }
          continue;
        }
        //get message
        $message=$posts['data'][$i]['message'];
        //get message ID later used for impression count
        $objId=$posts['data'][$i]['id'];


        try {
        //get number of unique impressions
        $response = $facebook->api(
          "/$objId/insights/post_impressions_unique"
        );
          }catch(FacebookApiException $e) {
          $result = $e->getResult();
          error_log(json_encode($result));
          print $result;
        }
        //get number of impressions
        $numViewsUnique=$response['data'][0]['values'][0]['value'];

        //create table to display
        print "
              <tr> 
               <td>$message</td>
               <td>$numViewsUnique</td>
              </tr>
              ";

      }
      print"</table>";
    }

  }

?>
<!doctype html>

<html>
  <head>
<style>
table,th,td
{
border:1px solid black;
border-collapse:collapse;
}
th,td
{
padding:5px;
}
</style>


<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript">

//function to hide send message and weather functionality 
function show(field) {
  if (field === "sendMsg") {
    document.getElementById("sendMsg").style.display = 'block';
    document.getElementById("weather").style.display = 'hidden';
    }
  else if(field==="weather"){
    document.getElementById("sendMsg").style.display = 'hidden';
    document.getElementById("weather").style.display = 'block';
  }

}
             
//function to post regular status update
function sendMsg() {
  msg= document.getElementById('msg').value;
  publish=$('input[name=publish1]:checked').val();
  var conf=confirm("Are you sure you want to post: " + msg);
        if (conf==true)
        {
          $.ajax({
            type: "POST",
            url: "createPost.php",
            global: false,
            data: { "msg": msg, "auth": $("#auth").val(), "pid": $("#pid").val(), "publish":publish },
            success: function(html) {
              try {
                var json = $.parseJSON(html);
               alert(json.message);
              } catch(e) {
                alert(e);
              }
            }
          });
        }
    }


function sendWeather() {
  zip= document.getElementById('zip').value;
  publish=$('input[name=publish2]:checked').val();
  var conf=confirm("Are you sure you want to post the weather");
        if (conf==true)
        {
          $.ajax({
            type: "POST",
            url: "createWeatherPost.php",
            global: false,
            data: { "zip": zip, "auth": $("#auth").val(), "pid": $("#pid").val(), "publish":publish },
            success: function(html) {
              try {
                var json = $.parseJSON(html);
               alert(json.message);
              } catch(e) {
                alert(e);
              }
            }
          });
        }
    }
</script>



<title>Page Dashboard </title>

 <link href="css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
   <div class="container">
     <div class="jumbotron">
      <a href="<?php echo $logoutUrl;?>"> Logout </a>
<? if (!$chosenPage): ?>
        <h1> Hey <?php echo $userName;?> ! </h1>

        <p>You are an admin on <?php echo $size; ?> Facebook Pages! </p>
          
    <p>Which Page do you want to work with? </p>
    <form class="navbar-form navbar-left" action="main.php" method="post">
    <select class="form-control" name="fbpage">
      <?php 
      for($i=0;$i<$size;$i++){
        $name=$arrPageNames[$i];
      print "<option value='$i'>$name</option>";
      }
      ?>
    </select></h6>
    <input type="submit" value="Submit">
    </form>


      </div>


<? else: ?>
  <h1>  <?php echo $pageName; ?> Page! </h1> <h6>(<a href="main.php"> Change Page </a>)</h6>
<p> You can post the temperature, regular status update or view posts.</p>
<p> Create a post: </p>


<input type="button" value="Regular Status Update" onclick = "show('sendMsg');">

<div id="sendMsg" style="display: none;">
 <!--  <form action="createPost.php" method="post"> -->
    Message: <br><!-- <input type="text" id="msg" name="msg"> <br> -->
    <textarea rows="4" cols="40" name="msg" id="msg"> </textarea><br>
   <input type="radio"  id="publish1" name="publish1" value="true">Publish this post
    <input type="radio" id ="publish1" name="publish1" value="false">Unpublished page post<br>
  <input type="hidden" name="pid"  id="pid" value=<?php echo $pid; ?>>
  <input type="hidden" name="auth" id="auth" value=<?php echo $authToken; ?>>

  <input type="submit" id="message" value="Submit" onClick = "sendMsg()"  >
  <!-- onClick = "sendMsg()" -->
  <!-- </form> -->


</div>

-OR-
 
<input type="button" value="Post the Temperature" onclick = "show('weather');">

<div id="weather" style="display: none;">
<!-- <form action="createWeatherPost.php" method="post">  -->
    Zipcode (5 digit): <input type="text" id="zip" name="zip"> <br>
   <input type="radio"  id="publish2" name="publish2" value="true">Publish this post
    <input type="radio" id ="publish2" name="publish2" value="false">Unpublished page post<br>
  <input type="hidden" name="pid"  id="pid" value=<?php echo $pid; ?>>
  <input type="hidden" name="auth" id="auth" value=<?php echo $authToken; ?>>

  <input type="submit" id="weather" value="Submit" onClick = "sendWeather()" >
  <!-- onClick = "sendMsg()" -->
<!--  </form>  -->
</div>

</div>

<div class="page-header">
<br>
<br>

<center> <h3>View your most recent posts: </h3></center>
<div style="float:left">
<center><h3> Published Posts</h3></center>
<?php getPostsPublished(); ?>
 </div> 
<div style="float:left; margin-left:20px;" >
<center><h3> Unpublished Posts</h3></center>
<?php getPostsUnpublished(); ?>
</div>

</div>
<? endif; ?>
<br>
<br>
<br>
 </div> <!-- /container -->
</body>

</html>

<?php
  }
  ?>