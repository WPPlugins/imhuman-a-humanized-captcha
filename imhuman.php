<?php
/*
Plugin Name: ImHuman - Humanized captcha
Plugin URI: http://elxsy.com/
Description: ImHuman provides more user friendly security image check (captcha method) than normal "enter the image characters" method. You will need a free <a href="http://elxsy.com/imhuman/">ImHuman API key</a> to use it. With this new version AJAX supported comments addded, browser cache problem solved, Manuel editing removed, Multilanguage support added. Requires PHP5 and jQuery. You can use Wordpress Jquery or External Jquery, javascript functions  can be converted to other libraries also. You can edit security settings and measures in the plugin settings. API keys control panel, client library downloads, support, documentation, bug and feature tracks are supported under <a href="http://elxsy.com/imhuman/">ImHuman support pages</a> also.
Version: 0.0.9
Author: Yuksel Kurtbas
Author URI: http://elxsy.com/
*/

define('IMHUMAN_API', 			'api.elxsy.com');
define('IMHUMAN_CLIENT_VERSION','1.0');

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
	imhuman_set_session();
	imhuman_render();
}

function imhuman_install() {
	$options['imhuman_api_user'] = "";
	$options['imhuman_api_key'] = "";
	$options['imhuman_row'] = "1";
	$options['imhuman_col'] = "5";
	$options['imhuman_sel'] = "3";
	$options['imhuman_exc'] = "0";
	$options['imhuman_lang'] = "en";
	$options['imhuman_word'] = "Please Select All : %WORD%(s):";
	update_option( 'imhuman_options', $options );
}

function imhuman_set_session(){
	if(!session_id())
		session_start();
}

function imhuman_head(){
	if( is_singular() ){
	?>
	<link href="<?php echo plugins_url('imhuman-a-humanized-captcha/css/imhuman.css') ?>" rel="stylesheet" type="text/css" />
	<?php
	}
}

function imhuman_render(){
	$ID = intval($_GET['ID']);
	
	$ops 	= get_option( 'imhuman_options' );
	$params	= 'api='.IMHUMAN_CLIENT_VERSION.'&user='.urlencode($ops['imhuman_api_user']).'&key='.urlencode($ops['imhuman_api_key'])
	.'&row='.urlencode($ops['imhuman_row']).'&col='.urlencode($ops['imhuman_col']).'&sel='.urlencode($ops['imhuman_sel']).'&lang='.urlencode($ops['imhuman_lang']);
	
	if(function_exists("file_get_contents")) {
		$rsp = json_decode(@file_get_contents('http://'.IMHUMAN_API.'/?'.$params), true);
	} else {
		$rsp = json_decode(imhuman_get($params), true);
	}
	if( !$rsp['ERROR'] ){
		$_SESSION[$ID.'ANSWER'] = $rsp['RSP']['ANSWER'];
		echo '<div class="imhuman-title">'.str_replace('%WORD%', $rsp['RSP']['WORD'], $ops['imhuman_word']).'</div>'
			.'<div class="imhuman-row pos-left clear-left" id="imhuman-div">';
		for($i=0; $i < $ops['imhuman_row'] * $ops['imhuman_col']; $i++){
			$_SESSION[$ID.'KEYS'][] = $rsp['RSP']['GRID'][$i][1];
			echo '<div class="imhuman-item pos-left" style="background-image:url('.$rsp['RSP']['GRID'][$i][0].')" id="'
				.$rsp['RSP']['GRID'][$i][1].'">'
				.'<div class="imhuman-chk"><input type="checkbox" class="checkbox" name="'.$rsp['RSP']['GRID'][$i][1].'" value="'
				.$rsp['RSP']['GRID'][$i][2].'" /></div>'
				.'</div>';
			if( ($i + 1) % $ops['imhuman_col'] == 0 )
				echo '<div class="clear-left"></div>';
		}
		echo '</div>'
			.'<div class="clear-left"></div>';
	} else {
		?>
		<div class="imhuman-error">
		<?php _e('ImHuman Error Code : '.$rsp['ERROR']) ?><br />
		<?php _e('ImHuman Error Message : '.$rsp['RSP']) ?><br />
		<?php _e('ImHuman API VERSION :'.$rsp['APIV']) ?><br />
		<?php _e('ImHuman Client API Version : '.$rsp['CLAPIV']) ?><br />
		<?php _e('ImHuman Minumum Client API Version : '.$rsp['MAPIV']) ?><br />
		</div>	
		<?php
	}
}

function imhuman_get($request, $port = 80) {
	global $wp_version;

	$http_request  = "GET /?$request HTTP/1.0\r\n";
	$http_request .= "Host: ".IMHUMAN_API."\r\n";
	$http_request .= "User-Agent: WordPress/$wp_version | ImHuman/1.0\r\n";
	$http_request .= "\r\n";

	$response = '';
	if( false != ( $fs = @fsockopen(IMHUMAN_API, $port, $errno, $errstr, 10) ) ) {
		fwrite($fs, $http_request);

		while ( !feof($fs) )
			$response .= fgets($fs, 1160); // One TCP-IP packet
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);
	}
	return $response[1];
}

function imhuman_admin_menu() {
add_submenu_page('plugins.php', __('ImHuman'), __('ImHuman Settings'), 'manage_options', 'imhuman', 'imhuman_admin_page');
}

function imhuman_preprocess_comment($comment) {
	global $user_ID;
	
	$ops = get_option('imhuman_options');
	if( $ops['imhuman_exc'] && $user_ID ){
		return $comment;
	}
	
	$ID = $comment['comment_post_ID'];
	
	imhuman_set_session();

	if(is_array($_SESSION[$ID.'KEYS'])){
		foreach($_SESSION[$ID.'KEYS'] as $k)
			if( isset($_POST[$k]) )
				$v[]=$_POST[$k];
		if(is_array($v)){
			sort($v);
			reset($v);
			if ( md5(implode('.',$v)) == $_SESSION[$ID.'ANSWER'] ){
				unset($_SESSION[$ID.'ANSWER']);
				unset($_SESSION[$ID.'KEYS']);
				return($comment);
			}
		}	
	}
	unset($_SESSION[$ID.'ANSWER']);
	unset($_SESSION[$ID.'KEYS']);
	wp_die( __("Error: please select the correct humanizers"));	

}

function imhuman_admin_page() {
	global $plugin_page;
	$lang = array('en'=>'ENGLISH','fr'=>'FRENCH');
	$m = NULL;
	if(isset( $_POST['do'] )) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));
		check_admin_referer($plugin_page);
		
		$t['imhuman_api_user'] = $_POST['imhuman_api_user'];
		$t['imhuman_api_key'] = $_POST['imhuman_api_key'];
		$t['imhuman_row'] = $_POST['imhuman_row'];
		$t['imhuman_col'] = $_POST['imhuman_col'];
		$t['imhuman_sel'] = $_POST['imhuman_sel'];
		$t['imhuman_exc'] = isset($_POST['imhuman_exc'] ) ? 1 : 0;
		$t['imhuman_word'] = $_POST['imhuman_word'];
		$t['imhuman_lang'] = $_POST['imhuman_lang'];
		update_option( 'imhuman_options', $t );
		$m = '<p>Settings Saved!</p>';
	}
	$options = get_option( 'imhuman_options' );
	wp_enqueue_script('jquery');
	?>
	 <script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function(){
			jQuery('#help').css("display","none");
			jQuery('.helper').click(function(){
				jQuery('#help').toggle();
			});
		});
	 </script>
	<div class="wrap">
		<h2><?php _e('ImHuman - Humanized Security Check'); ?></h2>
		<div class="updated">
			<p>Welcome to <strong>ImHuman</strong> where humans win the fight against machines.</p>
		</div>
			<p>ImHuman is a user friendly, easy to use, secure and fun "are you a human?" check (captcha) method for your website comments.</p>
			<p>You need an API username and key values for it to work properly, Please visit <a href="http://www.elxsy.com/imhuman/">ImHuman</a> pages to get your free keys</p>
			<p>If you are having problems with the plugin or have some questions please visit the documentation and faq pages. First hand support will be always there.</p>
		
		<?php if($m) echo "<div class='updated fade'>".$m."</div>" ?>
		<div class="narrow">
			<form action="plugins.php?page=<?php echo $plugin_page; ?>" method="post">
			<?php wp_nonce_field($plugin_page); ?>
			<table class="form-table">
				<tr>
					<th><?php _e('ImHuman Apı User'); ?></th>
					<td><input type="text" name="imhuman_api_user" id="imhuman_api_user" value="<?php echo $options['imhuman_api_user']; ?>" /></td>
				</tr>
				<tr>
					<th><?php _e('ImHuman Apı Key'); ?></th>
					<td><input type="text" name="imhuman_api_key" id="imhuman_api_key" value="<?php echo $options['imhuman_api_key']; ?>" /></td>
				</tr>
				<tr>
					<th><?php _e('Language'); ?></th>
					<td><select name="imhuman_lang">
						<?php
						foreach($lang as $k=>$v)
							echo '<option value="'.$k.'"'.($k == $options['imhuman_lang'] ? ' selected' : '').'>'.$v.'</option>';
						?>
						</select> 
					Want to see your language? Visit <a href="http://www.elxsy.com/imhuman/" target="_blank">elxsy</a> and help translation!</td>
				</tr>
				<tr>
					<th><?php _e('Skip for Registered Users?'); ?></th>
					<td><input type="checkbox" name="imhuman_exc" id="imhuman_exc" value="1" <?php echo $options['imhuman_exc'] ? 'checked="checked"' : ''; ?> /></td>
				</tr>
				<tr>
					<th><?php _e('Instruction Text'); ?><br /><strong>%WORD%</strong> will be replaced by the random tag</th>
					<td><input name="imhuman_word" type="text" id="imhuman_word" value="<?php echo $options['imhuman_word']; ?>" size="50" /></td>
				</tr>
				<tr>
					<th><?php _e('Mosaic <strong>row</strong> count'); ?>
					<a href="#none" class="helper">help</a></th>
					<td><input type="text" name="imhuman_row" id="imhuman_row" value="<?php echo $options['imhuman_row']; ?>" /></td>
				</tr>
				<tr>
					<th><?php _e('Mosaic <strong>column</strong> count'); ?>
					<a href="#none" class="helper">help</a></th>
					<td><input type="text" name="imhuman_col" id="imhuman_col" value="<?php echo $options['imhuman_col']; ?>" /></td>
				</tr>
				<tr>
					<th><?php _e('How many picture to select'); ?>
					<a href="#none" class="helper">help</a></th>
					<td><input type="text" name="imhuman_sel" id="imhuman_sel" value="<?php echo $options['imhuman_sel']; ?>" /> 
					Minimum 2</td>
				</tr>
				<tr>
					<td colspan="2" id="help" class="fade">
					<p><strong>Mosaic row count</strong> is, how many images to show horizontally (rows) for selection grid/puzzle.</p>
					<p><strong>Mosaic column count</strong> is, how many images to show vertically (columns) for selection grid/puzzle.</p>
					<p>If you input 3 rows and 2 columns, your puzzle / grid will be consisted of 3 * 2 = 6 images</p>
					<p><strong>How many images to select</strong> is, how many <strong>CORRECT</strong> answers should be exist <strong>and</strong> <strong>MUST</strong> BE SELECTED to result in a <strong>TRUE</strong> check - meaning visitor is human</p>
					<p><img src="<?php echo bloginfo('url')?>/wp-content/plugins/imhuman-a-humanized-captcha/screenshot-2.jpg" /></p>
					<p><strong>1 ROW, 3 COLUMN, 2 SELECTION RESULT</strong><br />
						<img src="<?php echo bloginfo('url')?>/wp-content/plugins/imhuman-a-humanized-captcha/screenshot-3.jpg" /></p>
					<p><strong>1 ROW, 6 COLUMN, 2 SELECTION RESULT</strong></strong><br /><img src="<?php echo bloginfo('url')?>/wp-content/plugins/imhuman-a-humanized-captcha/screenshot-4.jpg" /></p>
					</td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" name="do" class="button" value="<?php _e('Save Changes &raquo;'); ?>" /></td>
				</tr>
			</table>
			</form>
		</div>
	</div>
<?php
}

if(!function_exists('wp_redirect')) :
function wp_redirect($location, $status = 302) {
	global $is_IIS;
	// my hack
	if( ereg('wp-comments-post', $_SERVER['PHP_SELF']) ):
		echo $location;
		exit;
	endif;
	// original wp_redirect for 2.8
	$location = apply_filters('wp_redirect', $location, $status);
	$status = apply_filters('wp_redirect_status', $status, $location);

	if ( !$location ) // allows the wp_redirect filter to cancel a redirect
		return false;

	$location = wp_sanitize_redirect($location);

	if ( $is_IIS ) {
		header("Refresh: 0;url=$location");
	} else {
		if ( php_sapi_name() != 'cgi-fcgi' )
			status_header($status); // This causes problems on IIS and some FastCGI setups
		header("Location: $location");
	}
}
endif;

function imhuman_trigger($ID){
	global $user_ID;
	$show = 1;
	$ops = get_option('imhuman_options');
	if( $ops['imhuman_exc'] && $user_ID )
		$show = 0;
?>
<script language="javascript">
jQuery(document).ready(function($) {
	$('#submit').after('<img align="textbottom" src="<?php echo plugins_url('imhuman-a-humanized-captcha/load.gif') ?>" style="display:none" id="imger" />');
	<?php if($show): ?>
	$('<div id="imhuman"></div>').insertBefore('#submit').load('<?php echo plugins_url('imhuman-a-humanized-captcha/imhuman.php?ID=').$ID ?>',clean);	
	function clean(){
		$('.imhuman-item').click(function(){
			e = $(this).hasClass('imhuman-sel');
			$("input[name='"+this.id+"']").attr('checked', !e);
			$(this).toggleClass('imhuman-sel');
		});
		$('.imhuman-chk').hide();
	}
	<?php endif; ?>
	$('#commentform').submit(function(){
		$(".imhuman-error").remove();
		$('#submit').attr('disabled', true);
		$('#imger').show();
		$.ajax({
		   type: "POST",
		   url: $(this).attr('action'),
		   data: $(this).serialize(),
		   timeout:30000,
		   complete: function(XMLHttpRequest, textStatus){
			 switch(XMLHttpRequest.status){
				 case 200: 
				 	s = XMLHttpRequest.responseText;
					s = s.replace('#','?#');
				 	self.location = s;
				 break;
			 	 case 500:
				 	s = XMLHttpRequest.responseText.match(/<body id="error-page">([^\[]*)<\/body>/);
					$('#commentform').before(s[1]).prev().hide().addClass('imhuman-error').fadeIn('slow');
					<?php if($show): ?>
					$('#imhuman').load('<?php echo plugins_url('imhuman-a-humanized-captcha/imhuman.php?ID=').$ID ?>',clean);
					<?php endif; ?>
					$('#submit').attr('disabled', false);
					$('#imger').hide();
				break;
			 }
			}
		 });
		return false;		
	});
});
</script>
<?php
}

register_activation_hook(__FILE__, 		'imhuman_install');
add_action('init', 						'imhuman_set_session');
add_action('wp_head', 					'imhuman_head');
add_action('admin_menu', 				'imhuman_admin_menu');
add_action('preprocess_comment', 		'imhuman_preprocess_comment');
add_action('comment_form', 				'imhuman_trigger');
?>