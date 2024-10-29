<?php
//master apwt functions (generally used in all other functions and includes)
function APWTLBAPIAuth() {	//formerly APIAuth
	$apikey = get_option("APWTAPIKEY");
	$apiauth = get_option("APWTAPIAUTH");
	$apwtver = get_option("APWT_THIS_VERSION");

	if ($apwtver == '') {
		print "lost APWT_THIS_VERSION";
		return;
	}

	if ( ($apiauth == '') || ($apikey == '') ) {
		print "The AllProWebTools plugin has not been configured.  Please enter API Credential in wp-admin under 'AllProWebTools'.";
		exit;
	}

	return "apwtpluginversion=".$apwtver."&apikey=".get_option("APWTAPIKEY")."&apiauth=".get_option("APWTAPIAUTH");
}

function APWTLBGetUrl() {	//formerly APWTGetUrl
	$thisurl = "http://auth.allprowebtools.com/listener/installinfo.php?".APWTLBAPIAuth();
	$thereturn = wp_remote_get($thisurl);


	if ($thereturn['body'] == 'API credentials not found') {
		print $thereturn['body'];
		exit;
	}

	return $thereturn['body'];
}

function APWTLeadBoxActivate() {
//http://wordpress.org/support/topic/how-do-i-create-a-new-page-with-the-plugin-im-building
	$APWTThisVersion = APWT_plugin_get_version();
	update_option("APWT_THIS_VERSION", $APWTThisVersion);
}

function APWTLeadBoxDeactivate() {
	//nothing here
}

function APWTRemoteGet($url) {	//formerly apwt_remote_get
	if (empty($_SESSION['serverurl'])) {
		$_SESSION['serverurl'] = APWTLBGetUrl();
	}

	if (empty($_SESSION['serverurl'])) {
		print "unable to contact auth server";
		exit;
	}

	$debug = 0;
	$args = APWTCookieSessionHandler();
	$args['timeout'] = 120;

	$url .= '&siteurl='.$_SERVER['HTTP_HOST'].'&ip='.$_SERVER["REMOTE_ADDR"];
	$url = "https:".$_SESSION['serverurl'].$url;

//in an attempt to debug leadbox slowness, we tried using direct curl instead of wp_remote_get() to improve response times
//we later found another issue related to L30 that improved the leadbox slowness - JAB 2017.05.11
//$ch = curl_init($url);
//$response = curl_exec($ch);

	$response = wp_remote_get($url, $args);

	if (empty($response->errors)) {
		//no errors found
		if ($debug > 0) {
			print "<hr>".$url."<hr>";

			print "<pre>";
			print_r ($args);
			print "</pre>";

			print "<pre>";
			print_r ($_SESSION);
			print "</pre>";
		}
		return $response['body'];
	} else {
		print "error in APWTRemoteGet {APWT154}";
		print $url."<br>";
		print_r ($response->errors);
		exit;
	}
}

function APWTCookieSessionHandler() {	//formerly cookiesessionhandler
	if (isset($_SESSION['PHPSESSID'])) {
	  $cookie = new WP_Http_Cookie( 'PHPSESSID' );
	  $cookie->name = 'PHPSESSID';
	  $cookie->value = $_SESSION['PHPSESSID'];
	  $cookie->expires = mktime( 0, 0, 0, date('m'), date('d') + 7, date('Y') ); // expires in 7 days
	  $cookie->path = '/';
	  $cookie->domain = '';

	  $cookies[] = $cookie;
		$args = array(  'cookies' => $cookies );

		return $args;
	}
}

function APWT_enqueue_scripts() {
  wp_register_script('APWTajax', plugins_url( '/js/apwt-leadbox.js', dirname(__FILE__) ), array(), '1', 'all' );
  wp_enqueue_script( 'APWTajax');
  wp_localize_script( 'APWTajax', 'APWTajaxurl', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
 	wp_enqueue_script('jquery');
}

class APWTLeadBox extends WP_Widget {
	public function __construct() {
		add_action( 'wp_ajax_nopriv_APWTLeadBox', array(&$this,'submit'));
		add_action( 'wp_ajax_APWTLeadBox', array(&$this,'submit'));

		$widget_ops = array(
			'classname' => 'APWTLeadBox',
			'description' => 'Display your AllProWebTools Leadsbox',
		);
		parent::__construct( 'APWTLeadBox', 'AllProWebTools LeadBox', $widget_ops );
	}

  function form($instance) {
  //get a title variable defined by the user
    $instance = wp_parse_args( (array) $instance, array( 'APWTLeadBoxID' => '' ) );
    $APWTLeadBoxID = $instance['APWTLeadBoxID'];
    //get the ids for the lead box
		$thisurl = "/wordpress/wpapi.php?action=leadboxids&".APWTLBAPIAuth();
		$thereturn = APWTRemoteGet($thisurl);

		$leadboxes = explode("-|-",$thereturn);
		array_pop($leadboxes);

		foreach ($leadboxes as $val) {
			$pieces = explode("-*-",$val);
			$newarray[$pieces[0]] = $pieces[1];
		}
    $APWTLeadBoxID = empty($instance['APWTLeadBoxID']) ? ' ' : apply_filters('widget_title', $instance['APWTLeadBoxID']);
		$thisfieldid = sanitize_text_field($this->get_field_id('APWTLeadBoxID'));
		$thisfieldname = sanitize_text_field($this->get_field_name('APWTLeadBoxID'));

?>
	  <p><label for="<?php echo $thisfieldid; ?>">LeadBox:
<?php
		if (empty($newarray)) {
			print "no leadboxes found";
		} else {
			//create select box
			print '<select id="'.$thisfieldid.'" name="'.$thisfieldname.'">';
			foreach ($newarray as $lbid => $lbname) {

				if ($lbid < 1) { //sanitize
					print "error in data";
					return;
				}

				if ($APWTLeadBoxID == $lbid) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				print '<option value="'.$lbid.'" '.$selected.'>'.sanitize_text_field($lbname).'</option>';
			}
			print '</select>';
		}
?>
		</label></p>
<?php
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['APWTLeadBoxID'] = $new_instance['APWTLeadBoxID'];
    return $instance;
  }

  function widget($args, $instance) {
  	extract($args, EXTR_SKIP);
// 		echo $before_widget;

 		$APWTLeadBoxID = empty($instance['APWTLeadBoxID']) ? ' ' : apply_filters('widget_title', $instance['APWTLeadBoxID']);

		if ($APWTLeadBoxID > 0) {
			$thisurl = "/wordpress/wpapi.php?action=getleadbox&leadboxid=".$APWTLeadBoxID."&".APWTLBAPIAuth();
			$thereturn = APWTRemoteGet($thisurl);

//    	echo $before_title . $thereturn . $after_title;
    	echo $thereturn;
		} else {
    	echo "<h1>No Leadbox defined</h1>";
  	}

//		echo $after_widget;
  }

  public static function submit() {
		if (isset($_REQUEST['apwtleadbox'])) {
			$query = http_build_query($_REQUEST['apwtvalues']);

			$thisurl = "/wordpress/wpapi.php?action=submitleadbox&".$query."&".APWTLBAPIAuth();
			$thereturn = APWTRemoteGet($thisurl);

			echo $thereturn;
		}

		die(0);
 	}
} //end class extend APWTLeadBox