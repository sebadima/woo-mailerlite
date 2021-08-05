=== WooCommerce - MailerLite ===
Contributors: mailerlite
Donate link: Donate link: https://www.mailerlite.com/
Tags: woo, woocommerce, mailerlite, marketing, email, email marketing, ecommerce, shop
Requires at least: 3.0.1
Tested up to: 5.7.1
Requires PHP: 7.2.5
Stable tag: 1.5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Welcome to the Official MailerLite integration for WooCommerce. MailerLite is the email marketing tool that makes it super easy to design beautiful newsletters and to set up automated e-commerce email campaigns.

Intuitive tools and templates enable you to build newsletters, websites, landing pages, pop-ups and more—all without coding! Plus, every free plan comes with award-winning 24/7 customer support.

The official MailerLite for WooCommerce plugin gives you a suite of e-commerce features to help you grow your business. You can track sales and campaign ROI, import your products, automate emails based on purchases, and re-engage customers who abandon their carts.

== Description ==

==== OFFICIAL PLUGIN FEATURES ====

* Checkout integration
* Select between multiple positions
* Show/hide checkbox
* Enable/disable double opt-in
* Product importing
* Sales tracking and campaign ROI
* Customize checkbox label via settings page
* Forward order data to MailerLite
* Setup order tracking MailerLite custom fields
* Setup order related MailerLite segments
* Set up automation triggered by recent purchases
* Abandoned cart emails
* Subscribe pop-ups
* Regular updates and improvements: Check out the [changelog](https://wordpress.org/plugins/woo-mailerlite/changelog/)

= Quickstart =

* Enter your MailerLite API key
* For e-commerce tracking on campaigns generate a [consumer key + secret](https://docs.woocommerce.com/document/woocommerce-rest-api/) with read rights
* Select your default list/group
* Enable checkout integration

= Credits =

* Plugin created with the official [MailerLite API](https://developers.mailerlite.com/docs).

== Installation ==

The installation and configuration of the plugin is as simple as it can be.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'woo mailerlite'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select zip file from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download the plugin
2. Extract the directory to your computer
3. Upload the directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= Is WordPress Multisite supported? =

Yes, WordPress Multisite is supported by this official plugin..

== Screenshots ==

1. Settings page
2. Checkout page integration
3. Product block in campaign builder
4. Product picker in campaign builder
5. Campaign e-commerce tracking
6. Dashboard e-commerce performance

== Changelog ==
= 1.5.6 (5th August 2021) =
* Button to reset the sync status of all orders so that they can be synced again
* Fix of dependencies hashes to resolve conflicts with other plugins
* Fix for abandoned cart trigger after the order was submitted

= 1.5.5 (10th May 2021) =
* Changed activate/deactivate method names with a prefix to resolve conflicts with other plugins

= 1.5.4 (19th April 2021) =
* Usage of php-scoper to add a distinct namespace to all dependencies to resolve conflicts with other plugins
* Tested up to wordpress 5.7.1

= 1.5.3 (6th April 2021) =
* Updated dependencies to reduce conflicts with other plugins

= 1.5.2 (22nd Match 2021) =
* Version bump

= 1.5.1 (22nd March 2021) =
* Version bump
* Updated php requirement to 7.2.5

= 1.5.0 (22nd March 2021) =
* Updated dependencies
* Tested up to wordpress 5.7

= 1.4.11 (5th November 2020) =
* Updated description

= 1.4.9 (9th October 2020) =
* Bump supported versions

= 1.4.8 (18th May 2020) =
* Bump supported versions

= 1.4.7 (25th February 2020) =
* Fix: better handling when a large number of orders need to be synced

= 1.4.6 (29th November 2019) =
* Minor fixes and updates

= 1.4.5 (11th September 2019) =
* Update: Ignore products option added

= 1.4.4 (25th July 2019) =
* Fix: Abandoned cart triggering issues
* Fix: Segment update on group change

= 1.4.3 (18th July 2019) =
* Fix: WC not found handling

= 1.4.2 (15th July 2019) =
* Minor fixes and updates

= 1.4.0 & 1.4.1 (11th July 2019) =
* New: Resubscribe option for inactive users
* New: Popups option
* Improvement: Abandoned cart process changes
* Improvement: Layout design change and fixes
* Improvement: Disabling and enabling plugin processes updates
* Improvement: Changes to allow some settings to be updated through the MailerLite app
* Minor fixes and updates

= 1.3.1 (30th May 2019) =
* Minor fixes and updates

= 1.3.0 (21st May 2019) =
* Update: abandoned carts feature
* Minor fixes and updates

= 1.2.4 & 1.2.5 (19th March 2019) =
* Fix: creating custom fields wasn't working properly
* Fix: unnecessary double optin api calls slowing down pages

= 1.2.2 & 1.2.3 (15th February 2019) =
* Minor improvements and fixes

= 1.2.1 (5th February 2019) =
* Improvement: Helper messages on plugin from
* Minor improvements and fixes

= 1.2.0 (1st February 2019) =
* New: Product importing in MailerLite campaign builder
* New: Product and category importing in MailerLite automation workflow builder
* New: Campaign sales tracking

= 1.1.1 (27th December 2017) =
* Fix: Using PHP lower than 5.6 lead into fatal errors

= 1.1.0 (12th December 2017) =
* New: Forward order data to MailerLite
* New: Setup order tracking MailerLite custom fields
* New: Setup order related MailerLite segments
* Improvement: Optimized group selection via settings
* Improvement: Optimized settings page
* Minor improvements and fixes
* WordPress v4.9.1 compatibility

= 1.0.2 (23th September 2017) =
* Fix: Disabling double optin via settings didn't take affect

= 1.0.1 (11th May 2017) =
* Improvement: Added description and link to API key setting
* Fix: Settings not being saved
* Fix: Groups not being available when entering API key for the first time

= 1.0 (11th May 2017) =
* Initial release

== Upgrade Notice ==

= 1.2.0 (1st February 2018)
* New: Product importing in MailerLite campaign builder
* New: Product and category importing in MailerLite automation workflow builder
* New: Campaign sales tracking

= 1.1.1 (27th December 2017) =
* Fix: Using PHP lower than 5.6 lead into fatal errors

= 1.1.0 (12th December 2017) =
* New: Forward order data to MailerLite
* New: Setup order tracking MailerLite custom fields
* New: Setup order related MailerLite segments
* Improvement: Optimized group selection via settings
* Improvement: Optimized settings page
* Minor improvements and fixes
* WordPress v4.9.1 compatibility

= 1.0.2 (23th September 2017) =
* Fix: Disabling double optin via settings didn't take affect

= 1.0.1 (11th May 2017) =
* Improvement: Added description and link to API key setting
* Fix: Settings not being saved
* Fix: Groups not being available when entering API key for the first time

= 1.0 (11th May 2017) =
Initial release
