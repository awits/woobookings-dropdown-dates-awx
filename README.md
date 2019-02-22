# WooBookings Drop-down Dates (AW Mod)
Primarily allows WooCommerce Bookable Products dates to be selected from drop-down vs the ugly and unnecessary calendar. Automattic somehow don't consider basic functionaility and usability important despite demanding a lot of money for their Bookings plugin ($250/£190 per year).

This is forked from  baperrou/WooBooking-Dropdown-coursedates to add extra functionaility I would like, and the vast, vast majority of code is hers, I merely make modifications here and there.

First to remove availability number from drop-down then:
  - Add an option to enable this or not (once I've read through the code properly and played with adding options to WooCommerce!)
    Need to see whether to add to each product? Or as a global setting somehow?
  - Create shortcode so can list/display dates available in Content area. Could do this sepearately, but makes sense to add to this plugin.
    Or simplify plugin and add to own site-specific plugin.
  - Aim to keep in sync with baperrou/WooBooking-Dropdown-coursedates, and if that implements similar or better changes, wil defer to that as I would not describe myself as an experienced coder.

The below readme is based on the original plugin as provided by DO IT SIMPLY LTD.

___

Requires at least WordPress: 4.0
Tested up to: 4.9.4
 * WC requires at least: 3.2
 * WC tested up to: 3.4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Do It Simply Select Courses by Dropdown Date allows selection of booking products by a dropdown box.  This is useful if you only have a small number of dates to choose and do not want customers to have to scroll through several months to find them. 

## Description

Do It Simply  Courses by Dropdown Date is an add-on for the Woocommerce and Woo Bookings.  You can choose which bookable products it is used for.  It also allows you to use either Resources or Booking Max Blocks with Availability.  You cannot use both Resources and Availability together.

## Docs & Support =

The following plugins are needed for Do It Simply Select Courses by Dropdown Date to work:

* [WooCommerce](https://woocommerce.com/) by Automattic 
* [WooCommerce Bookings](https://woocommerce.com/) by Automattic 

## Installation ==

1. Upload the entire `WooBookings-Dropdown` folder to the `/wp-content/plugins/` directory. 
2. Activate the plugin through the 'Plugins' menu in WordPress.

To use the plugin:

There are no settings with this plugin.  To use simply tick the 'Change this Booking Product to dropdown.'.
NOTE: The product must already be set to be 'Bookable'.

Booking by Days or Hour Blocks only.

## Changelog

= 1.1 =
* Initial Release

= 1.2 =
* Minor Updates:

* Fix - remove error shown when all bookable dates are past
* Tidy - when single date (without hour) do not show midnight as start time

= 1.2.5 =
* AW fork - minor Updates

* Remove availability number from drop-down with aim to add option to enable/disable it.

-- END --
