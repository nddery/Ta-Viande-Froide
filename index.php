<?php
// create an app instance
require_once('php-sdk/src/facebook.php');
// require the credentials for the FB sdk
require_once('creds.php');
$creds = get_creds();
$fb = new Facebook($creds);

// get the user ID
// we may or may not get this data, depending on if the user is logged in or not
$user = $fb->getUser();

// if $user (id) is set, we know the user is current logged into facebook
// but we do not know if the access token is valid
if($user):
   try{
      // we have a logged in and authenticated user
      $p = $fb->api('/me');
      $feed = $fb->api('/me/feed');
      $events = $fb->api('/me/events');
      $statuses = $fb->api('/me/statuses?limit=1000');
      $pictures = $fb->api('/me/photos?limit=1000');

      // some stuff well use later
      // gender (he/she)
      if(array_key_exists('gender', $p)):
         $gender  = ($p['gender'] == 'male') ? 'he' : 'she';
         $gender2 = ($p['gender'] == 'male') ? 'him' : 'her';
      else:
        $gender = 'the mutant';
      endif;
   }
   catch(FacebookApiException $e){
      error_log($e);
      $user = null;
   }
endif;

// login or logout url, depending on current state of user
if($user):
   $logoutUrl = $fb->getLogoutUrl();
else:
   // which permissions do we need ?
   // I know it's a lot but hey!
   $scope = array(
      'scope' => array(
         'user_hometown',
         'user_location',
         'user_likes',
         'user_online_presence',
         'user_relationships',
         'user_status',
         'user_work_history',
         'user_birthday',
         'user_education_history',
         'user_photos',
         'email',
         'user_checkins',
         'read_stream',
         'user_events'
      )
   );
   $loginUrl = $fb->getLoginUrl($scope);
endif;

// the about text
$about = "
<p>The idea for this project came after watching an episode of <a href='http://www.radio-canada.ca/emissions/infoman/saison11/' title='Infoman'>Infoman</a> (kind of the Qu&eacute;bec Rick Mercer Report equivalent, but better!). In the episode, they we're talking about \"la viande froide\" of public personality that was kept in the basement of Radio-Canada. This \"viande froide\" (cold meat) was actually premade up-to-date footage of the lives of some public personality, ready to be broadcasted in the event one of them died. This website kind of do that, in some way. It retrieve your most commented statuses and pictures from Facebook and display them all on the page. This is your Facebook viande froide, what is/was excitting about your facebook wall.</p>

  <hr />

  <p class='disclaimer'>Disclaimer: We do not collect/log any information that facebook sends us.</p>

  <hr />

  <p>Design + code by <a href='http://jpcart.info' title='Juan Pablo Casis'>Juan Pablo Casis</a><br />
  Concept + code by <a href='http://nddery.ca' title='Nicolas Duvieusart D&eacute;ry'>Nicolas Duvieusart D&eacute;ry</a></p>
";
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ta Viande Froide</title>

<link rel="stylesheet" href="style.css" type="text/css" media="screen" />

<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="css/ie6style.css" />
	<script type="text/javascript" src="js/DD_belatedPNG_0.0.8a-min.js"></script>
	<script type="text/javascript">DD_belatedPNG.fix('img.overlay, div#content-bg-bottom');</script>
<![endif]-->
</head>

<body class="viande">
<?php // echo '<pre>'; print_r($pictures); echo '</pre>'; ?>
<div id="wrapper">

   <?php if($user): ?>

   <div id="about">
    <?php
    echo '<div class="screen-overlay">';
       echo '<div class="overlay-content">';
          echo '<a href="#" id="closeAbout" title="Close">X</a>';
          echo $about;
       echo '</div>';
    echo '</div>';
    ?>
   </div>

   <div id="content">
      <div id="content-bg-bottom">
         <div id="header">
            <ul>
               <li class="active"><a href="#" id="toggleAbout" title="about this project">about</a></li>
            </ul>
            <br class="clear" />

            <div id="logo">
               <img  id="user-profile-image"
                     class="avatar"
                     src="https://graph.facebook.com/<?php echo $user; ?>/picture?type=large"
                     alt="<?php echo $p['name']; ?> facebook profile picture"
                     width="79" height="79"/>
                     <a href="<?php echo $p['link']; ?>" title="Go to facebook" class="user-name logo"><?php echo $p['name']; ?></a> - <a href="<?php echo $logoutUrl; ?>" title="Logout">Logout</a>
               <br /><span class="personal-profile">Personal Profile</span>
               <span><br />
               Latest Status: <?php echo $feed['data'][0]['message']; ?><br />
               </span>
            </div> <!-- end #logo -->
         </div> <!-- end #header -->

      <div id="inside">
         <div id="inside-bg-top">
            <div id="inside-bg-bottom">
               <div class="refresh slide">

<!-- BIOGRPAHY -->
                  <div class="page-content">
                     <div class="entry">
                        <p>
                           <strong>
                              <span style="color: #000000;">
                                 <?php
                                 // name
                                 echo $p['name'];
                                 // if we have a birthday
                                 if(array_key_exists('birthday', $p)):
                                    // explode the string at each /
                                    $birth = explode('/', $p['birthday']);
                                    // now make it readable
                                    echo ' was born on the '. date('j\<\s\u\p\>S\<\/\s\u\p\> \o\f F Y', mktime(0, 0, 0, $birth[0], $birth[1], $birth[2]));
                                 // and if we don't
                                 else:
                                    echo 'was never born. '. ucfirst($gender) .' is probably some sort of mutant!';
                                 endif;
                                 ?>
                              </span>
                           </strong><br />
                           <?php
                           // if both hometown and location exist
                           if(array_key_exists('hometown', $p) && array_key_exists('location', $p)):
                              echo ucfirst($gender) .' is from '. $p['hometown']['name'] .' but currently lives in '. $p['location']['name'] .'. ';
                           // if only hometown exists
                           elseif(array_key_exists('hometown', $p)):
                              echo ucfirst($gender) .' is from '. $p['hometown']['name'] .'.';
                           // if only location exists
                           elseif(array_key_exists('location', $p)):
                              echo ucfirst($gender) .' currently lives in '. $p['location']['name'] .'. ';
                           // else your in the streets
                           else:
                              echo 'Apparently, '. $gender .' does\'nt live anywhere, maybe '. $gender .' is homeless? ';
                           endif;

                           // if the 'work' key exist in the array
                           if(array_key_exists('work', $p)):
                              $i = 0;
                              foreach($p['work'] as $job):
                                 $i++;
                                 $done = (array_key_exists('end_date', $job)) ? true : false;

                                 // employer name
                                 if ($i != 1) echo ' ';
                                 echo $job['employer']['name'];
                                 if($done)
                                    echo ' was ';
                                 else
                                    echo ' is ';
                                    if ($i != 1) echo 'also ';
                                echo 'hiring '. $gender2;
                                 // position
                                 if(array_key_exists('position', $job))
                                    echo ' as a '. $job['position']['name'];
                                 // start date
                                 if(array_key_exists('start_date', $job)):
                                    // explode the string at each /
                                    $start_date = explode('-', $job['start_date']);
                                    // now make it readable
                                    echo ' since '. date('F Y', mktime(0, 0, 0, $start_date[1], 0, $start_date[0]));
                                 endif;
                                 // end date
                                 if(array_key_exists('end_date', $job)):
                                    // explode the string at each /
                                    $end_date = explode('-', $job['end_date']);
                                    // now make it readable
                                    echo ' but unfortunately '. $gender .' got fired (we just assume) in '. date('F Y', mktime(0, 0, 0, $end_date[1], 0, $end_date[0])) .'. ';
                                 else:
                                    echo ' and '. $gender;
                                    if ($i != 1) echo ' also';
                                    echo ' still works there. ';
                                 endif;
                              endforeach;
                           endif;

                           // education
                           // if the 'education' key exist in the array
                           if(array_key_exists('education', $p)):
                              $i = 0;
                              // reverse the array (last item = newer item)
                              $schools = array_reverse($p['education']);
                              foreach($schools as $school):
                                 $i++;
                                 // if ($i != 1) echo ' ';
                                 if ($i == 1) echo ucfirst($gender) .' studies at ';
                                 if ($i == 2) echo ' and went to ';
                                 if ($i > 2) echo ' and ';
                                 // school name
                                 echo $school['school']['name'];
                                 // concentration
                                 if(array_key_exists('concentration', $school))
                                    echo ' in '. $school['concentration'][0]['name'];
                                 // start date
                                 if(array_key_exists('year', $school))
                                    echo ' ('. $school['year']['name'] .')';
                                 // if it was the last school, close the sentence (.)
                                 if ($i == count($schools)) echo '.';
                              endforeach;
                           endif;
                           ?>
                        </p>
                     </div> <!-- end .entry -->
                     <h2>biography</h2>
                  </div> <!-- end .page-content -->


<!-- PICTURES -->
<?php // echo '<pre>'; print_r($pictures); echo '</pre>'; ?>
                  <div class="page-content">
                     <div class="entry">
                        <?php
                        // go thru them all and take the ones with most comments
                        $i = 0;
                        $avr = 0;
                        $gp = array();
                        foreach($pictures['data'] as $picture):
                           // only if the status contains comments
                           if(array_key_exists('comments', $picture)):
                              // count the number of sub-array in the comments section
                              $count = count($picture['comments']['data']);
                              // store all status with comment in array (with status, number of comment and status id)
                              $gp[$i] = array(
                                 'id'     => $picture['id'],
                                 'thumb'  => $picture['images'][3],
                                 'source' => $picture['images'][0]['source'],
                                 'link'   => $picture['link'],
                                 'count'  => $count
                              );
                              // calculate the average
                              $avr += $count;
                              $i++;
                           endif;
                        endforeach;

                        // round the average up
                        $avr = ceil($avr/count($gp));
                        // $i = 0;
                        // we now have an array of statuses to display
                        foreach($gp as $p):
                           // only show pictures if it as more comments than average
                           if($p['count'] > $avr):
                              // determine which class, portrait or landscape to add
                              // $o for orientation
                              $o = ($p['thumb']['height'] >= $p['thumb']['width']) ? 'portrait' : 'landscape';
                              echo '<a class="gallery-item '. $o .'" target="_blank" href="'. $p['link'] .'" rel="'. $p['source'] .'"><img src="'. $p['thumb']['source'] .'" alt="" /></a>';
                              // if ($i % 4 == 2) echo '<div class="clear height1px">&nbsp;</div>';
                              // $i++;
                           endif;
                        endforeach;
                        // echo '<pre>'; print_r($pictures); echo '</pre>';
                        ?>
                     </div> <!-- end .entry -->
                     <h2>pictures</h2>
                  </div> <!-- end .page-content -->


<!-- EVENTS -->
                  <div class="page-content">
                     <div class="entry">
                        <p>
                        <?php
                        // isolate the date from events
                        $events = $events['data'];

                        foreach($events as $event):
                           // get the event feed (500 messages)
                           $feed = $fb->api('/'. $event['id'] .'/feed?limit=500');
                           // go thru them all and take the ones with most comments
                           $i = 0;
                           foreach($feed['data'] as $msg):
                              if($msg['comments']['count'] >= 4):
                                 $i++;
                                 // first event is bold and get a line break, second will be first on the line, so only for third and more
                                 if ($i > 2) echo ' - ';
                                 // if it's the first status of the first event (if it's the first time we loop)
                                 if($i == 1):
                                    echo '<strong><span style="color: #000000;">';
                                       echo $msg['message'];
                                    echo '</span></strong><br />';
                                 // else just show the message normaly
                                 else:
                                    echo $msg['message'];
                                 endif;
                              endif;
                           endforeach;
                        endforeach;
                        ?>
                        </p>

                        <?php
                        // if there is at least one event
                        if(array_key_exists('0', $events)):
                           echo '<ul>';
                           foreach($events as $event):
                              echo '<li>';
                                 echo $event['name'];
                              echo '</li>';
                           endforeach;
                           echo '</ul>';
                        endif;
                        ?>
                     </div> <!-- end .entry -->
                     <h2>latest events</h2>
                  </div> <!-- end .page-content -->


<!-- STATUSES -->
                  <div class="page-content">
                     <div class="entry">
                        <p>
                        <?php
                        // go thru them all and take the ones with most comments
                        $i = 0;
                        $avr = 0;
                        $gs = array();
                        foreach($statuses['data'] as $status):
                           // only if the status contains comments
                           if(array_key_exists('comments', $status)):
                              // count the number of sub-array in the comments section
                              $count = count($status['comments']['data']);
                              // store all status with comment in array (with status, number of comment and status id)
                              $gs[$i] = array(
                                 'id'     => $status['id'],
                                 'status' => $status['message'],
                                 'count'  => $count
                              );
                              // calculate the average
                              $avr += $count;
                              $i++;
                           endif;
                        endforeach;

                        // round the average up
                        $avr = ceil($avr/count($gs));
                        $i = 0;
                        // we now have an array of statuses to display
                        foreach($gs as $s):
                           // only show status if it as more comments than average
                           if($s['count'] > $avr):
                              $i++;
                              // first status is bold and get a line break, second will be first on the line, so only for third and more
                              // if ($i > 2) echo ' - ';
                              // if it's the first status
                              if($i == 1):
                                 echo '<strong><span style="color: #000000;">';
                                    echo $s['status'];
                                 echo '</span></strong><br />';
                              // else just show the status normaly
                              else:
                                 $alt = ($i % 2) ? 'alt' : '';
                                 echo '<span class="status '. $alt .'">'. $s['status'] .'</span>';
                              endif;
                           endif;
                        endforeach;
                        ?>
                        </p>
                     </div> <!-- end .entry -->
                     <h2>statuses</h2>
                  </div> <!-- end .page-cotent -->

               </div> <!-- end .page-content -->
            </div> <!-- end .refresh slide -->
         </div> <!-- end #inside-bg-bottom -->
      </div> <!-- end #inside-bg-top -->
   </div> <!-- end #inside -->

   <br class="clear" />
   </div>

   <?php else: // if user is not connected or has not yet accepted the app ?>
      <!-- LOGIN -->
      <?php
      echo '<div class="screen-overlay">';
         echo '<div class="overlay-content">';
            echo '<p class="fblogin">To use this site you need to ';
               echo '<a href="'. $loginUrl .'" title="Login" class="fblogin">';
                  echo 'login with Facebook';
               echo '</a>. After logging in, please be patient while Facebook redirects you to this page - it sometimes takes some time.';
            echo '</p>';

            echo $about;
         echo '</div>';
      echo '</div>';
      ?>
   <?php endif; // end if($user) ?>

   <?php if($user): ?>
      <div id="footer">Disclaimer: We do not collect/log any information that facebook sends us.</div>
   <?php endif; ?>
</div>

<script type='text/javascript' src='code/js/l10n.js?ver=20101110'></script>
<script type='text/javascript' src='code/js/jquery/jquery.js?ver=1.6.1'></script>
<script type="text/javascript" src="js/easing.js"></script>
<script type="text/javascript" src="js/effects.js"></script>
<script type="text/javascript" src="code/js/jquery/jquery.center.min.js"></script>

<script>
  ;(function($){
    $(function(){
      $('#toggleAbout').click(function(){
        $('#about').fadeIn();
      });

      $('#closeAbout').click(function(){
        $('#about').fadeOut();
      });
    }); // end .ready()
  }(jQuery));
</script>

  <script type="text/javascript">
    var _gaq=_gaq||[];_gaq.push(["_setAccount","UA-31577047-1"]);_gaq.push(["_setDomainName","nddery.ca"]);_gaq.push(["_trackPageview"]);(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})()
  </script>

</body>
</html>
