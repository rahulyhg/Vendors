<?php
/*
 * Plugin Name: WP e-Commerce Vendors
 * Plugin URI: http://getshopped.org/vendors
 * Description: This Plugin provides some basic abilities of making users vendors, vendor-editor and vendor-administers,
 * allowing statistics to be provided on what they earned by selling goods on the site.
 * Author: jghazally (taken over from vbakatis)
 * Version: 1.2
 * Author URI: http://screamingcodemonkey.com
 */

/**
 * This class makes sure all functions load at the right time and not just when WordPress calls it.
 *
 **/

class wpsc_vendors{

	/**
	 * Load this Plugin before WP e-Commerce
	 *
	 * @since 1.1
	 */
	function wpsc_vendors(){
		add_action('wpsc_pre_init' , array( $this, 'init'), 10);
		add_action( 'show_user_profile' , array($this, 'getuserdetails'),10, 1);
		add_action( 'personal_options_update' , array($this, 'save_new_profile'),10, 1);

	}

	function getuserdetails($user){
	?>
		<h3><?php	 	 _e('WP e-Commerce Vendors Details' , 'wpsc_vendors'); ?></h3>
		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<label for="vendor_paypal">PayPal E-mail Address : </label>
				</th>
				<td>
					<input id="vendor_paypal" type="text" class="regular-text" value="<?php	 	 echo $user->vendor_paypal; ?>" name="vendor_paypal" />
				</td>
			</tr>

			</tbody>
		</table>
	<?php
		return;
	}


	// Create the function use in the action hook

	function vendor_add_dashboard_widgets() {
		wp_add_dashboard_widget('vendor_dashboard_widget', 'Vendor Sales', array( $this, 'vendor_dashboard_widget_function'));
	}
	function vendor_dashboard_widget_function() {
		// Display whatever it is you want to show
		$this->wpsc_vendor_ps_ajax();
		$this->wpsc_vendor_ps_selector();
	}


	function save_new_profile($user){
		update_user_meta($_POST['user_id'], 'vendor_paypal', $_POST['vendor_paypal']);

	}
	/**
	 * Start setting up the Plugins Goods
	 *
	 * @since 1.1
	 */
	function init(){
		add_action('admin_init', array( $this, 'vendors_metabox') , 7);
		add_action( 'save_post', array( $this, 'wpsc_save_vendors') );
		add_action( 'wp_dashboard_setup', array( $this, 'wpsc_vendors_setup_widgets' ) , 1 );
		add_action( 'wp_ajax_wpsc_vendor_ps_ajax',array( $this, 'wpsc_vendor_ps_ajax') );
		$this->set_roles();
		require_once('vendors_widget.php');
		if(is_super_admin())
			add_action('wp_dashboard_setup', array ( $this, 'vendor_add_dashboard_widgets' ) );

	}

	/**
	 * Save the vendors details if it has been set.
	 *
	 * @since 1.0
	 */
	function wpsc_save_vendors(){
	if( isset( $_REQUEST['wpsc_vendors']) )
			update_product_meta( absint($_POST['post_ID']), 'vendors', $_REQUEST['wpsc_vendors']);
	}

	/**
	 * Setup the new user roles and add capabilities to the user roles
	 *
	 * @since 1.0
	 */
	function set_roles(){
		//add new roles and set capabilities

		add_role('vendor-administrator', 'Vendor administrator');
		$role =& get_role('vendor-administrator');
		$role->add_cap('moderate_comments');
		$role->add_cap('manage_categories');
		$role->add_cap('manage_links');
		$role->add_cap('upload_files');
		$role->add_cap('import');
		$role->add_cap('unfiltered_html');
		$role->add_cap('edit_posts');
		$role->add_cap('edit_pages');
		$role->add_cap('read');
		$role->add_cap('level_10');
		$role->add_cap('level_9');
		$role->add_cap('level_8');
		$role->add_cap('level_7');
		$role->add_cap('level_6');
		$role->add_cap('level_5');
		$role->add_cap('level_4');
		$role->add_cap('level_3');
		$role->add_cap('level_2');
		$role->add_cap('level_1');
		$role->add_cap('level_0');
		$role->add_cap('wpsc_view_product_sales');


	}

	/**
	 * Initiate the Vendors Metabox to the edit products page
	 *
	 * @since 1.0
	 */
	function vendors_metabox(){
		add_meta_box( "wpsc_vendors", __( 'Vendors', 'wpsc_vendors' ), array( $this, "wpsc_vendors_form") ,"wpsc-product","side","low");
	}
	/**
	 * output vendors form to edit product page
	 * @since 1.0
	 * @return XHTML
	 */

	function wpsc_vendors_form(){
		global $post, $current_user;
		get_currentuserinfo();
		$blogusers = get_users_of_blog();
		$product_type_object = get_post_type_object('wpsc-product');
		$current_user_meta = get_user_meta($current_user->ID, 'wp_capabilities', true);
		if (!current_user_can($product_type_object->cap->edit_post, $post->ID) || isset($current_user_meta['vendor']))
			return '<p>Sorry you do not have sufficient permission to edit vendor details</p>';

		$vendors = get_post_meta( $post->ID, '_wpsc_vendors', true);
		foreach ( $blogusers as $user_id ) {
			$user = new WP_User( $user_id );
			$is_vendor =    $user->has_cap( 'vendor'               )
			             || $user->has_cap( 'vendor-administrator' )
			             || $user->has_cap( 'vendor-editor'        );

			if( ! $is_vendor )
				continue;

			$checked = '';
			$rate = '';
			if( isset( $vendors[$user->ID] ) ){
				if($vendors[$user->ID]['enabled'])
					$checked = 'checked="checked"';
				$rate = $vendors[$user->ID]['rate'];
			}
?>
			<p>
				<label for="wpsc_user_<?php	 	 echo $user->ID; ?>"><input type="checkbox" name="wpsc_vendors[<?php	 	 echo $user->ID; ?>][enabled]" <?php	 	 echo $checked; ?> id="wpsc_user_<?php	 	 echo $user->ID; ?>" value="true" /> <?php	 	 echo $user->user_login; ?>,</label><label for="wpsc_vendors[<?php	 	 echo $user->ID; ?>][rate]"> rate: <input type="text" name="wpsc_vendors[<?php	 	 echo $user->ID; ?>][rate]" value="<?php	 	 echo $rate; ?>" /></label>
			<br /></p>
		<?php
		}
	}

	/**
	 * Make sure vendors can only see what you want them to see,,
	 * @since 1.0
	 */
	function wpsc_vendors_setup_widgets(){
		$is_vendor =    current_user_can( 'vendor'               )
		             || current_user_can( 'vendor-administrator' )
			         || current_user_can( 'vendor-editor'        );

		if ( $is_vendor ) {
			remove_action('wp_dashboard_setup', 'ses_wpscd_add_dashboard_widgets' );
			remove_action('wp_dashboard_setup', 'wpsc_dashboard_widget_setup' );
			remove_action('wp_dashboard_setup', 'wpsc_quarterly_setup' );
			remove_action('wp_dashboard_setup', 'wpsc_dashboard_4months_widget_setup' );
			wp_add_dashboard_widget( 'wpsc_vendor_product_sales', __('Product sales', 'wpsc-vendor'), array( $this, 'wpsc_vendor_product_sales' ) );
		}
	}
	/**
	 * Initiate Dashboard Sales Section
	 *
	 * @since 1.0
	 */
	function wpsc_vendor_product_sales() {

		$this->wpsc_vendor_ps_ajax();
		$this->wpsc_vendor_ps_selector();

	}

	/**
	 * The XHTML for selection box of perios in the Dashboard Widget
	 * @since 1.0
	 * @return XHTML Select Box
	 */
	function wpsc_vendor_ps_selector() {

		$period = $this->wpsc_vendor_ps_period();
?>

	<div width="100%" class="wpsc-vendor-right">
		<form method="POST" action="#">
			<select id="wpsc-vendor-product-sales-period" name="wpsc-vendor-product-sales-period">
				<option value="today"<?php	 	 if($period=="today") echo " selected"; ?>>Today</option>
				<option value="7days"<?php	 	 if($period=="7days") echo " selected"; ?>>Last 7 Days</option>
				<option value="thismonth"<?php	 	 if($period=="thismonth") echo " selected"; ?>>This Month</option>
				<option value="lastmonth"<?php	 	 if($period=="lastmonth") echo " selected"; ?>>Last Month</option>
				<option value="thisyear"<?php	 	 if($period=="thisyear") echo " selected"; ?>>This Year</option>
				<option value="alltime"<?php	 	 if($period=="alltime") echo " selected"; ?>>All Time</option>
			</select>
		</form>
		<script type="text/javascript">
			jQuery('#wpsc-vendor-product-sales-period').change(function() {
		             jQuery.ajax( { url: "admin-ajax.php?action=wpsc_vendor_ps_ajax&wpsc_vendor_period="+jQuery(this).val(),
	                                    success: function(data) { jQuery("#wpsc-vendor-product-sales").html(data); }
	                                      }
	                                    ) });
		</script>
		<a href="<?php	 	 echo admin_url('?vendor_get_emails=true'); ?>">Download email list</a>
	</div>
	<?php
	}

	/**
	 * Get the Period of product sales requested
	 * @since 1.0
	 * @return $period (string) associated with the time requested
	 */
	function wpsc_vendor_ps_period() {

		if (isset($_GET['wpsc_vendor_period'])) {
			$period = $_GET['wpsc_vendor_period'];
			setcookie('wpsc_vendor_product_sales_period', $period, time()+(86400*30));
		} elseif (isset($_COOKIE['wpsc_vendor_product_sales_period'])) {
			$period = $_COOKIE['wpsc_vendor_product_sales_period'];
		} else {
			$period = 'thismonth';
		}
		return $period;
	}

	/**
	 * Ajax Function which outputs the Vendors Product Sales
	 * @since 1.0
	 * @return XHTMl TABLE
	 */
	function wpsc_vendor_ps_ajax() {
		global $wpdb, $current_user;
		$exit = FALSE;
		$output = '';
		$users = array();
		//If your not super admin then your a vendor
		if(!is_super_admin()){
			get_currentuserinfo();
			$users[] = $current_user;
		}else{
			$users = get_users(array('role'=>'vendor-administrator') );
		}
		$period = $this->wpsc_vendor_ps_period();
		if (isset($_GET['wpsc_vendor_period']))
			$exit = TRUE;

		switch($period) {
		case "7days":
			// Actually today + 6 previous days
			$mindate = mktime(0,0,0,date('n'),date('j'),date('Y')) - 6*60*60*24;
			$maxdate = mktime(23,59,59,date('n'),date('j'),date('Y'));
			break;

		case "today":
			$mindate = mktime(0,0,0,date('n'),date('j'),date('Y'));
			$maxdate = mktime(23,59,59,date('n'),date('j'),date('Y'));
			break;

		case "lastmonth":
			if (date('n') == 1)
				$mindate = mktime(0,0,0,12,1,date('Y')-1);
			else
				$mindate = mktime(0,0,0,date('n')-1,1,date('Y'));

			$maxdate = mktime(0,0,0,date('n'),0,date('Y'));
			break;

		case "thisyear":
			$mindate = mktime(0,0,0,1,1,date('Y'));
			$maxdate = mktime(23,59,59,12,31,date('Y'));
			break;

		case "thismonth":
		default:
			$mindate = mktime(0,0,0,date('n'),1,date('Y'));
			if (date('n') == 12)
				$maxdate = mktime(0,0,0,1,date('j'),date('Y')+1)-1;
			else
				$maxdate = mktime(0,0,0,date('n')+1,date('j'),date('Y'))-1;
			break;

		}
		if ($period != "alltime")
			$wpsc_vendor_query_date_range = "pl.date BETWEEN $mindate AND $maxdate";
		else
			$wpsc_vendor_query_date_range = "1 = 1";

		$wpsc_vendor_query = "SELECT c.name, c.prodid as id,
	                                   SUM(c.quantity) AS num_items,
	                                   SUM(c.quantity * c.price) AS product_revenue
		     	                 FROM {$wpdb->prefix}wpsc_cart_contents c
	                             LEFT JOIN {$wpdb->prefix}wpsc_purchase_logs pl
	                                ON c.purchaseid = pl.id
	                             WHERE $wpsc_vendor_query_date_range AND pl.processed IN (3,4,5)
	                          GROUP BY c.prodid
			                  ORDER BY product_revenue DESC, num_items DESC";
		$wpsc_vendor_result_rows = $wpdb->get_results($wpdb->prepare($wpsc_vendor_query),ARRAY_A);

?>
		<style type="text/css">
			.ses-wpscd-table {
				padding: 5px 2px;
				margin: 0px;
				border-collapse: collapse;
			}

			.ses-wpscd-headerrow {
				border-bottom: 1px solid #ddd;
			}

			.ses-wpscd-row {
				border-bottom: 1px solid #f9f9f9;
			}

			.ses-wpscd-cell {
				padding: 5px 2px;
				text-align: center;
			}

			.ses-wpscd-left {
				text-align: left;
			}

			.ses-wpscd-right {
				text-align: right;
			}

			#ses-wpscd-product-sales-config {
				visibility: hidden;
			}
		</style>
		<div id="wpsc-vendor-product-sales">
			<table width="100%" class="ses-wpscd-table">
		<?php
		foreach($users as $user){

			$output .= '<tr class="ses-wpscd-headerrow"><th class="ses-wpscd-left">Product</th><th>Units</th><th>Rate</th><th>Revenue</th><th class="ses-wpscd-right">Profit</th></tr>';


		if (!count($wpsc_vendor_result_rows)) {
			$output .= "<td class=\"ses-wpscd-cell\" colspan=3>No Sales In Selected Period</td>";
		} else {
			//$output = new string;

				$sales_found = 0;
				$total = 0;

				foreach ($wpsc_vendor_result_rows as $row) {
					$vendors = get_product_meta( $row['id'], 'vendors', true );
					if( !$vendors )
						continue;
					$vendors = maybe_unserialize($vendors);
					if( !isset($vendors[$user->ID]['enabled']) )
						continue;
					if( ! $vendors[$user->ID]['enabled'] )
						continue;
					$rate = 1;
					if( isset($vendors[$user->ID]['rate']) )
						if( $vendors[$user->ID]['rate'] )
							$rate = $vendors[$user->ID]['rate']/100;
					$output .= "<tr class=\"ses-wpscd-row\">";

					$output .= "<td class=\"ses-wpscd-cell ses-wpscd-left\">".htmlentities($row['name'])."</td>";
					$output .= "<td class=\"ses-wpscd-cell\">".htmlentities($row['num_items'])."</td>";
					$output .= "<td class=\"ses-wpscd-cell\">" . $rate*100 . '%</td>';
					$sales_found = 1;
					$output .= "<td class=\"ses-wpscd-cell\">" . wpsc_currency_display( $row['product_revenue'] ) . "</td>";
					$output .= "<td class=\"ses-wpscd-cell ses-wpscd-right\">" . wpsc_currency_display( ( (float) $row['product_revenue'] ) * $rate ) . "</td>";
					$output .= "</tr>";
					$total += $row['product_revenue'] * $rate;

				}
				if($total > 0){
					if(is_super_admin()){
						$pay = '';
						//make payment button
						$paypal_address = get_user_meta($user->ID, 'vendor_paypal' ,true);
						if(!empty($paypal_address)){
							$pay = "<form onsubmit='log_paypal_buynow(this)' target='paypal' action='" . get_option( 'paypal_multiple_url' ) . "' method='post'>
							<input type='hidden' name='business' value='" . $paypal_address . "' />
							<input type='hidden' name='cmd' value='_xclick' />
							<input type='hidden' name='item_name' value='".get_bloginfo('name')." Vendor Income' />
							<input type='hidden' id='amount' name='amount' value='" . ($total) . "' />
							<input type='hidden' id='unit' name='unit' value='" . $price . "' />
							<input type='hidden' id='shipping' name='ship11' value='0' />
							<input type='hidden' name='handling' value='0' />
							<input type='hidden' name='currency_code' value='" . get_option( 'paypal_curcode' ) . "' />
							<input type='hidden' name='undefined_quantity' value='0' />
							<input type='submit' name='submit' value='Pay ".$user->user_login."' />
							</form>\n\r";

						}

					}
					$output .="<tr><td colspan='3'>&nbsp;</td><td>".$user->user_login." total: </td><td class='ses-wpscd-cell ses-wpscd-right'>".wpsc_currency_display($total).$pay."</td></tr>";

				}
				if( !$sales_found )
					$output .= "<td class=\"ses-wpscd-cell\" colspan=5>No Sales In Selected Period</td>";
			}
		}

		echo $output;
?>
			</table>
		</div>
		<?php
		// If this is an AJAX update exit()
		if ($exit)
			exit();

	}

}
$wpsc_vendors = new wpsc_vendors();

if(isset($_GET['vendor_get_emails']) && is_user_logged_in )
	add_action( 'admin_init','wpsc_vendor_print_emails' );

function wpsc_vendor_print_emails(){
	global $wpdb, $current_user;
	get_currentuserinfo();
	ini_set('memory_limit', '256M');
	header('Content-type: text/txt');
	header('Content-Disposition: attachment; filename="emails.txt"');
	$wpsc_vendor_query = "SELECT pl.id as log_id, c.prodid as id
	     	                 FROM {$wpdb->prefix}wpsc_cart_contents c
                             LEFT JOIN {$wpdb->prefix}wpsc_purchase_logs pl
                                ON c.purchaseid = pl.id
                             WHERE pl.processed IN (3,4,5)
                          ";
	$wpsc_vendor_emails = $wpdb->get_results( $wpsc_vendor_query, ARRAY_A );
	foreach ((array)$wpsc_vendor_emails as $row) {

		$vendors = get_product_meta( $row['id'], 'vendors', true );
		if( !$vendors )
			continue;
		$vendors = maybe_unserialize($vendors);
		if( !isset($vendors[$current_user->ID]['enabled']) )
			continue;
		if( ! $vendors[$current_user->ID]['enabled'] )
			continue;

		$email = $wpdb->get_var('SELECT `value` FROM  `wp_wpsc_submited_form_data` WHERE  `log_id` ='.$row['log_id'].' AND  `form_id` = 8');
		$first = $wpdb->get_var('SELECT `value` FROM  `wp_wpsc_submited_form_data` WHERE  `log_id` ='.$row['log_id'].' AND  `form_id` = 2');
		$last = $wpdb->get_var('SELECT `value` FROM  `wp_wpsc_submited_form_data` WHERE  `log_id` ='.$row['log_id'].' AND  `form_id` = 3');
		echo $first . '|' . $last . '|' . $email . '|' . $row['log_id'] . '
';
	}
	exit();

}
?>
