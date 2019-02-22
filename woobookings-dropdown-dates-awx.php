<?php
/*
Plugin Name: WooBookings Drop-down Dates (AW Mod)
Description: Add-on for WooCommerce Bookings. Switch Calendar to drop-down (full days or hourly increments only). Based on, and uses most code from, Do It Simply Select Courses by Dropdown Date (http://plugins.doitsimply.co.uk/).
Version: 1.2.5
Author: AWITS / DO IT SIMPLY LTD
Author URI: http://www.awitservices.co.uk/
GitHub URI: awits/woobookings-dropdown-dates-awx
Requires at least: 4.0
Tested up to: 4.9.4
 * WC requires at least: 3.2
 * WC tested up to: 3.4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
// Most credit to DO IT SIMPLY LTD and Webby Scotts per original plugin.
*/

defined( 'ABSPATH' ) or exit;

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function dis_ddd_requirements_met () {
    
    if ( ! is_plugin_active ( 'woocommerce-bookings/woocommmerce-bookings.php' ) ) {
        return false ;
    }
    return true ;
}

// ADD ACTIONS AND FILTERS

add_action('wp_enqueue_scripts','dis_wc_ddd_enqueue_script');

add_action( 'woocommerce_product_options_general_product_data', 'dis_wc_ddd_add_checkbox' );
add_action( 'woocommerce_process_product_meta', 'dis_wc_ddd_save_checkbox' );

if(!is_admin()){
	add_action( 'wp', 'dis_ddd_check_product' );
}

function dis_wc_ddd_enqueue_script() {
    wp_enqueue_script('dis_wc_ddd-dropdown',plugins_url('js/dis_wc_ddd-dropdown.js',__FILE__),array('jquery'));
    wp_localize_script('dis_wc_ddd-dropdown','WooBookingDropdown',array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'secure' => wp_create_nonce('woo-bookings-dropdown-refreshing-dates')
    ));
}
/**
  * Add tickbox to product DATA area to allow % cart increase
**/ 

function dis_wc_ddd_add_checkbox() {
	//verify it's a booking product before adding option
	$_product = wc_get_product( get_the_ID());
    if( $_product->is_type( 'booking' ) OR  $_product->is_type( 'accommodation-booking' )) {	
	    woocommerce_wp_checkbox( array(
	        'id' => '_dis_wc_ddd_dropdown_dates',
	        'label' => __('Change this Booking Product to dropdown.', 'WooBooking-Dropdown' ) ,
	        'description' =>  __('Ticking this removes the calendar and adds a dropbox.', 'WooBooking-Dropdown' ) ,
	        'desc_tip' => 'true',
	
	    ) );
	    
	}
}

/**
  * Save to Post META
**/ 

function dis_wc_ddd_save_checkbox( $post_id ) {
    if ( ! empty( $_POST['_dis_wc_ddd_dropdown_dates'] ) ) {
        update_post_meta( $post_id, '_dis_wc_ddd_dropdown_dates', esc_attr( $_POST['_dis_wc_ddd_dropdown_dates'] ) );
    }
    else {
	    delete_post_meta( $post_id, '_dis_wc_ddd_dropdown_dates' );
    }
}



$wswp_dates_built = false;
function dis_ddd_booking_form_fields($fields) {
	//echo '<pre>'; print_r($fields); echo '</pre>';
    global $wswp_dates_built;
    $i = 0;
    $selected_resource = 0;
    $reset_options = false;
    
	
    foreach($fields as $field) {
	   
        $new_fields[$i] = $field;
		
        if ($field['type'] == "select") {
	           
            $selected_resource = reset(array_keys($field['options']));
           
            if ( $reset_options !== false ) {
	            if(dis_ddd_build_options( $field['availability_rules'][$selected_resource] )) {
                	$new_fields[$reset_options]['options'] = dis_ddd_build_options( $field['availability_rules'][$selected_resource] );
                }
                else {
	                $new_fields[$reset_options]['options'] ='There is nothing currently to book';
                }
            }   
               
       }
        if ( $field['type'] == "date-picker" && $wswp_dates_built === false ) {			
            $s = $i;

            $new_fields[$s]['class'] = array('picker-hidden');
            $i++;
            $new_fields[$i] = $field;
            $new_fields[$i]['type'] = "select";
            if ($selected_resource == 0) {
                $reset_options = $i;
	            $max = $field['max_date'];
	            $now = strtotime( 'midnight', current_time( 'timestamp' ) );
	            $max_date = strtotime( "+{$max['value']} {$max['unit']}", $now );
	            if(dis_ddd_build_options( $field['availability_rules'][$selected_resource] )) {
	            	$new_fields[$i]['options'] = dis_ddd_build_options($field['availability_rules'][$selected_resource],$max_date);
	            
					$new_fields[$i]['class'] = array('picker-chooser');
				}
                else {
	                $new_fields[$reset_options]['options'] ='There is nothing currently to book';
                }

	        }
            
       }
       if ( $field['type'] == "datetime-picker" && $wswp_dates_built === false ) {
			 
            $s = $i;
            
            $new_fields[$s]['class'] = array('picker-hidden');
            $i++;
            $new_fields[$i] = $field;
            $new_fields[$i]['type'] = "select";
            if ($selected_resource == 0) {
                $reset_options = $i;
	            $max = $field['max_date'];
	            $now = strtotime( 'midnight', current_time( 'timestamp' ) );
	            $max_date = strtotime( "+{$max['value']} {$max['unit']}", $now );
	           
	             if(dis_ddd_build_options( $field['availability_rules'][$selected_resource] )) {
	            	$new_fields[$i]['options'] = dis_ddd_build_options($field['availability_rules'][$selected_resource],$max_date);
	            
					$new_fields[$i]['class'] = array('picker-chooser');
				}
                else {
	                $new_fields[$reset_options]['options'] ='There is nothing currently to book';
                }
	        }
            
       }
      /*/  must have an option if no dates are bookable WRONG PLACE FOR THIS?
       else {
	       echo '<div class="alert alert-danger" id="no-dates">There are currently no bookable dates</div>';
       }*/
        $i++;
        
    }
    return $new_fields;
}

function check_resource_avail_dis_ddd( $course_id, $resource_id, $number = 1, $start, $end ) {
//modified for dropdown Checking availability
	
	$booking = new WC_Product_Booking( $course_id );
	$avail = $booking->get_available_bookings( $start, $end,  $resource_id, $number );
	if( is_int( $avail ) ):$avail = $avail; else: $avail = 0;endif;
	if( $avail > 0 ): $is_available = true; else: $is_available = false; endif;
	$show_avail = array( $is_available, $avail );

	return $show_avail;	
	
}
function dis_ddd_build_options($rules, $building = false) {
	global $product;
	$bookable_product = new WC_Product_Booking($product);
	$rules = $bookable_product->get_availability_rules();
 	//need current date to remove past bookings
	$now = strtotime( 'midnight', current_time( 'timestamp' ) );
	
	global $wswp_dates_built;
    $dates = array();
 
    foreach( $rules as $dateset ) {
	    $i++;

	    //be aware that this associative array changes depending on version of WooBookings
        if ( 'time:range' == $dateset['type'] OR 'custom' == $dateset['type'] ) {
            $year = array_keys( $dateset['range'] );
            $year = reset( $year );
            $month = array_keys( $dateset['range'][$year] );
            $month = reset($month);
            $day = array_keys( $dateset['range'][$year][$month] );
            $day = reset( $day );
            if ( 'time:range' == $dateset['type'] ) {
	             $start = $dateset['range'][$year][$month][$day]['from'];
	             
             }
            // it seams the day key is empty if bookable is set to NO so check here
            if( $dateset['range'][$year][$month][$day] ) {
			
           		$dtime = strtotime( $year."-".$month."-".$day );
		   		// now make sure that it is later then today            
	           if( $dtime > $now ) {
		            $dates[$i]['dtime'] = $dtime;
		            $dates[$i]['resource'] = $dateset['resource_id'];
		            if( $start ) {	
			            $dates[$i]['start'] = $start;	
			            unset( $start );	 
			        }          
		        }
		        
	       }

       }      

    }
	 
    foreach( $dates as  $date_r ) {
	    $start_min = explode(':', $date_r['start']);
	
		if($start_min[0] != null) {
			 $date_s = $date_r['dtime'] + ($start_min[0] * 3600);
			 $new_date = date( "Y-m-d", $date_r['dtime'] ) . "-" . $date_r['start'];
			 $nice_date = date( "d M, Y g:i a", $date_s );
		}
		else {
			$date_s = $date_r['dtime'];
			$new_date = date( "Y-m-d", $date_r['dtime'] );
			$nice_date = date( "d M, Y", $date_s );
		}
	    
	    $date_e =  $date_s + (1*3600);
	    //if there is resource use the ID if not use current post_id DEFAULT
		if( $date_r['resource'] ) {
			$id = $date_r['resource'];
			$r_title =  get_the_title( $id ) . ' - ';
		}
		else {
			$id = null;
		}
	    
		
		//using my availability checker and give good feedback if full.
		// AW mod: Removed availability though function still active. Try to add option in settings to enable/disable it.
	    $avail = check_resource_avail_dis_ddd( get_the_ID(), $id, $number = 1, $date_s, $date_e );
		if( 'yes' == $avail[0] ) {
	        //$dates_last[$new_date] = $r_title  . $nice_date.' ('.$avail[1].' spaces left)';
	        $dates_last[$new_date] = $r_title  . $nice_date;
		}
		else {
			$dates_last[$new_date] = $r_title  . $nice_date .' FULLY BOOKED';
		}
		
     
    }
    if($dates_last) {
		ksort($dates_last);	
		$wswp_dates_built = true;
	}
	else {
		$dates_last[] = "No dates currently available";
	}
    return $dates_last;
}

//only call these action/filters if the category is set to dropdown and not in admin
if ( is_admin() && ! defined( 'DOING_AJAX' ) )
	return;
function dis_ddd_check_product() {
	global $post;
	$meta_set = get_post_meta( $post->ID, '_dis_wc_ddd_dropdown_dates' );
	
	if ( $meta_set &&  'no' != $meta_set ) {		
					
		add_filter('booking_form_fields','dis_ddd_booking_form_fields');
		add_action('wp_footer','dis_ddd_css');
		add_filter( 'body_class', 'dis_ddd_customclass' );
			
	}
	wp_reset_postdata();

}

// add class to page to instigate dropdown picker
function dis_ddd_customclass( $classes ) {
	//check page category
	global $post;	
	$meta_set = get_post_meta( $post->ID, '_dis_wc_ddd_dropdown_dates' );

	
	if ( $meta_set &&  'no' != $meta_set ) {
    	$classes[] = 'dis-dropdown-class';
	}
   
    return $classes;
    wp_reset_postdata();

}

// add simple styles to hide picker
function dis_ddd_css() {
    //adding in footer as not enough to justify new stylesheet
    ?>
    	<style type="text/css">
        .picker-hidden .picker,.picker-hidden legend, .wc-bookings-date-picker-date-fields {
            display:none;
        }
        </style>
    <?php
}
