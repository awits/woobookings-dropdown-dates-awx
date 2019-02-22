// dropdown datepicker including start hours

jQuery(function($) {    
    
     $(".picker-chooser").insertBefore('.wc-bookings-date-picker-date-fields');
     //need to add unique ID to page with dropdown picker then use to filter here and above****
      		$(".dis-dropdown-class #wc_bookings_field_start_date").prepend("<option value='' selected='selected'>Choose course</option>");
                $("select#wc_bookings_field_start_date").on('change', function() {
	            
				var selectedDate = $(this).val()
				var selectedDateBreakdown = selectedDate.split("-");
				console.log(selectedDateBreakdown[3]);
				$( "input[name*='wc_bookings_field_start_date_year']" ).val( selectedDateBreakdown[0] );
				$( "input[name*='wc_bookings_field_start_date_month']" ).val( selectedDateBreakdown[1] );
				$( "input[name*='wc_bookings_field_start_date_day']" ).val( selectedDateBreakdown[2] );
				if(selectedDateBreakdown[3]) {
					$( "input[name*='wc_bookings_field_start_date_time']" ).val( selectedDateBreakdown[3] );
				}
			});

})
