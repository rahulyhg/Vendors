<?php	 	

class vendor_users extends WP_Widget {
	function vendor_users() {
		// widget actual processes
		$widget_ops = array(
			'classname'   => 'widget_vendor_users',
			'description' => __( 'Display Vendor Users Widget', 'wpsc' )
		);

		$this->WP_Widget( 'widget_vendor_users', __( 'Vendor Users', 'wpsc' ), $widget_ops );
		
	}

	function form($instance) {
		// outputs the options form on admin
		$instance = wp_parse_args( (array)$instance, array(	'userid' => 0 ) );

		$users = get_users(array('role'=>'vendor-administrator'));?>

		<p>
			<label for="<?php	 	 echo $this->get_field_id('userid'); ?>"><?php	 	 _e( 'Choose a User:', 'wpsc' ); ?></label>
			<select name="<?php	 	 echo $this->get_field_name('userid'); ?>" id="<?php	 	 echo $this->get_field_id('userid'); ?>">
				<option value="-1">Select a User</option>
				<?php	 	 
				foreach( (array)$users as $user ){
					if(isset($instance['userid']) && $instance['userid'] == $user->ID){ ?>
						<option value="<?php	 	 echo $user->ID; ?>" selected="selected"><?php	 	 echo $user->user_login; ?></option>
					<?php	 	
					}else{
						?>
						<option value="<?php	 	 echo $user->ID; ?>"><?php	 	 echo $user->user_login; ?></option>
						<?php	 	
					}
				}?>
			</select>
			
		</p>
		<?php	 	
	}

	function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = $old_instance;
		$instance['userid']  = strip_tags( $new_instance['userid'] );
		return $instance;

	}

	function widget($args, $instance) {
		// outputs the content of the widget
		$user = get_userdata($instance['userid']);
		$avatar = get_avatar($instance['userid'], 80);
	?>
	<div class="widget row_3_text_widget widget_text">
	<h4 class="widget-title"><?php	 	 echo $user->nickname; ?></h4>
	<p>
		<?php	 	 
		echo $avatar; 
		echo $user->description	;
		?>
		
	</p>
	</div>
	<?php	 		
	}

}
//register_widget('vendor_users');
add_action( 'widgets_init', create_function( '', 'return register_widget("vendor_users");' ) );

?>
