<?php
/*
Plugin Name: Store Lunch Widget
Description: Display lunch menu from 'orderlunchesatwork.com'.
Version: 0.1
Author: Gary McKnight
*/

class sLunch_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'sLunch_widget',
			__('Lunch Widget', 'sLunch_widget_domain'),
			array( 'description' => __( 'Widget for displaying lunch info', 'sLunch_widget_domain' ), )
		);
	}

	//As soon as they change the structure of the table this is going to explode.
	public function widget( $args, $instance ) {
		//Dates holds all the dates. Lunch holds all the lunches and lStore matches the lunches with
		//the dates
		$dates = array();
		$lunches = array();
		$lStore = array();

		$url = 'https://'.$instance['sub'].'.orderlunchesatwork.com/includes/view_school_menu.php?mt='.date('n').'&yr='.date('Y').'&mcid='.$instance['mcid'].'&pid='.$instance['pid'].'&sch_id='.$instance['schid'];

		//Lunch content rips the HTML from our lunch ordering site
		$lunchContent = file_get_contents($url);
		//The array entries go 'dates, lunch, dates, lunch, etc.'
		$lExpld = explode('<tr',$lunchContent);
		for($x=0; $x<7; $x++){
			array_shift($lExpld);
		}
		//Get all the dates
		$y = 0;
		foreach($lExpld as $le){
			if($y%2==0){
				$hold = explode('>',$le);
				foreach($hold as $h){
					$sub = substr($h,0,2);
					$insert = filter_var($sub, FILTER_SANITIZE_NUMBER_INT);
					if($insert != '' && $insert != null){
						array_push($dates, $insert);
					}
				}
			}
			$y++;
		}
		//Get all the lunches
		$y = 0;
		foreach($lExpld as $le){
			if($y%2!=0){
				$hold = explode('</td>',$le);
				foreach($hold as $h){
					$t = explode('Special',$h)[1];
					$push = explode('Make',$t)[0];
					if($push != null && $push != ''){
						array_push($lunches, $push);
					}		
				}
			}
			$y++;
		}

		$key = array_search(date("j"), $dates);

		//Get the day in integer format
		$day = intval(date('j'));

		//Standard stuff. Set title. etc.
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		echo __( 
			//Strip the remaining HTML off of the lunches and then format to your liking.
			'<span class="lunchMenu" style="text-align:center; display:block; width:100%; margin-top:.5em;">'.strip_tags($lunches[$key]).'</span>
			<div class="lunchLink" style="text-align:center; margin-top:30%;"><a href="https://'.$instance["sub"].'.orderlunchesatwork.com"><button class="btn">Order Lunches</button></a></div>'
			, 'sLunch_widget_domain' );
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'New title', 'sLunch_widget_domain' );
		}
		if ( isset( $instance[ 'sub' ] ) ) {
			$sub = $instance[ 'sub' ];
		} else {
			$sub = __( 'subdomain', 'sLunch_widget_domain' );
		}
		if ( isset( $instance[ 'mcid' ] ) ) {
			$mcid = $instance[ 'mcid' ];
		} else {
			$mcid = __( 'mcid', 'sLunch_widget_domain' );
		}
		if ( isset( $instance[ 'pid' ] ) ) {
			$pid = $instance[ 'pid' ];
		} else {
			$pid = __( 'pid', 'sLunch_widget_domain' );
		}
		if ( isset( $instance[ 'schid' ] ) ) {
			$schid = $instance[ 'schid' ];
		} else {
			$schid = __( 'sch id', 'sLunch_widget_domain' );
		}
?>
		<p>
			<span class="slExpln">All of these variables can be found in the URL of your meal plan calendar. To find this, log in to "*yourSubdomain*.orderlunchesatwork.com", click on "Order" > "View Menu" and then take the variables from that URL.</span>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			<label for="<?php echo $this->get_field_id( 'sub' ); ?>"><?php _e( 'Subdomain:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'sub' ); ?>" name="<?php echo $this->get_field_name( 'sub' ); ?>" type="text" value="<?php echo esc_attr( $sub ); ?>" />
			<label for="<?php echo $this->get_field_id( 'mcid' ); ?>"><?php _e( 'MCID:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'mcid' ); ?>" name="<?php echo $this->get_field_name( 'mcid' ); ?>" type="text" value="<?php echo esc_attr( $mcid ); ?>" />
			<label for="<?php echo $this->get_field_id( 'pid' ); ?>"><?php _e( 'PID:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'pid' ); ?>" name="<?php echo $this->get_field_name( 'pid' ); ?>" type="text" value="<?php echo esc_attr( $pid ); ?>" />
			<label for="<?php echo $this->get_field_id( 'schid' ); ?>"><?php _e( 'SCH_ID:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'schid' ); ?>" name="<?php echo $this->get_field_name( 'schid' ); ?>" type="text" value="<?php echo esc_attr( $schid ); ?>" />
		</p>
<?php
	}
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['sub'] = ( ! empty( $new_instance['sub'] ) ) ? strip_tags( $new_instance['sub'] ) : '';
		$instance['mcid'] = ( ! empty( $new_instance['mcid'] ) ) ? strip_tags( $new_instance['mcid'] ) : '';
		$instance['pid'] = ( ! empty( $new_instance['pid'] ) ) ? strip_tags( $new_instance['pid'] ) : '';
		$instance['schid'] = ( ! empty( $new_instance['schid'] ) ) ? strip_tags( $new_instance['schid'] ) : '';
		return $instance;
	}
} 

function sLunch_load_widget() {
    register_widget( 'sLunch_widget' );
}
add_action( 'widgets_init', 'sLunch_load_widget' );
?>
