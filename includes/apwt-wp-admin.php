<?php
//all wp-admin related functions
function APWT_admin_menu() {
	add_menu_page("AllProWebTools","AllProWebTools",'activate_plugins',"AllProWebTools3","APWTLBSettings",plugins_url( 'wp-icon.png', __FILE__ ));
}

function APWTLBSettings() {	//formerly APWTSettings
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if (isset($_POST['B1'])) {
		$APWTAPIKey = preg_replace('/[^\da-z]/i', '', $_POST['apikey']);
		$APWTAPIAuth = preg_replace('/[^\da-z]/i', '', $_POST['apiauth']);

	  if((strlen($APWTAPIKey) != 25) && ($APWTAPIKey != '')) {
	    $message = '<div id="message" class="error">Invalid API Key</div>';
	    $count = 0;
		} else {
			//they have entered their api key - now save it
			$_SESSION['serverurl'] = '';
			if (!add_option( 'APWTAPIKEY', $APWTAPIKey)) {
				update_option( 'APWTAPIKEY', $APWTAPIKey );
				if($APWTAPIKey == '') {
			  	$count = 0;
			  } else {
			 		$count = 1;
			 	}
			}
		}
	  if((strlen($APWTAPIAuth) != 16) && ($APWTAPIAuth != '')) {
	    $message = '<div id="message" class="error">Invalid API Auth</div>';
	    $count2 = 0;
	  } else {
			//they have entered their api key - now save it
			if (!add_option( 'APWTAPIAUTH', $APWTAPIAuth)) {
				update_option( 'APWTAPIAUTH', $APWTAPIAuth );
				if($APWTAPIAuth == '') {
					$count2 = 0;
				} else {
					$count2 = 1;
				}
			}
		}
	  $count3 = $count + $count2;
	  if($count3 == 2) {
			$message = '<div id="message" class="updated fade">Congratulations You Are Ready To Go!</div>';
		}
	} else {
		$message = '';
	}
	print $message;
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>AllProWebTools API Settings</h2>
	<form method="POST" action="">
		<table class="form-table">
		<tbody>
			<tr>
		  	<th><label for="apikey">API Key</label></th>
		  	<td><input type="text" name="apikey" id="apikey" class="regular-text" value="<?php print sanitize_text_field(get_option("APWTAPIKEY")); ?>"></td>
		  </tr>
			<tr>
		  	<th><label for="apiauth">API Auth</label></th>
		  	<td><input type="text" name="apiauth" id="apiauth" class="regular-text" value="<?php print sanitize_text_field(get_option("APWTAPIAUTH")); ?>"></td>
		  </tr>
		 </tbody>
		 </table>
		<p><input type="submit" value="Update Settings" id="settings" name="B1" class="button"></p>
	</form>
	<p>If you don't already have an AllProWebTools account, you can <a target="register" href="http://myallprowebtools.com">sign up for one here.</a></p>
</div>
<?php
}