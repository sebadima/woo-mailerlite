![Banner](https://ps.w.org/woo-mailerlite/assets/banner-772x250.png)

# WooCommerce - MailerLite
[![Plugin Version](https://img.shields.io/wordpress/plugin/v/woo-mailerlite.svg)](https://wordpress.org/plugins/woo-mailerlite/) [![WordPress Version Compatibility](https://img.shields.io/wordpress/v/woo-mailerlite.svg)](https://wordpress.org/plugins/woo-mailerlite/) [![Downloads](https://img.shields.io/wordpress/plugin/dt/woo-mailerlite.svg)](https://wordpress.org/plugins/woo-mailerlite/) [![Rating](https://img.shields.io/wordpress/plugin/r/woo-mailerlite.svg)](https://wordpress.org/plugins/woo-mailerlite/)

This plugin allows you to easily connect WooCommerce with MailerLite. Track sales and campaign ROI, import products details, automate emails based on purchases and seamlessly add your customers to your email marketing lists via WooCommerce's checkout process.

## Description

#### Official MailerLite Integration for WooCommerce 

[Official WordPress.org Plugin](https://wordpress.org/plugins/woo-mailerlite/)

## Features

*  Checkout integration
*  Select between multiple positions
*  Show/hide checkbox
*  Enable/disable double opt-in
*  Product importing
*  Sales tracking and campaign ROI
*  Customize checkbox label via settings page
*  Forward order data to MailerLite
*  Setup order tracking MailerLite custom fields
*  Setup order related MailerLite segments
*  Set up automation triggered by recent purchases
*  Regular updates and improvements: Check out the [changelog](https://wordpress.org/plugins/woo-mailerlite/changelog/)

## Building the plugin
The plugin uses `php-scoper` to add a unique namespace into its dependencies in order to prevent conflicts with other plugins.

To use the plugin, install `php-scoper` in your system and run it in the plugin directory. Then, copy all files except the vendor folder into the generated build directory and run `composer dump-autoload`. Finally, run the `fix-autoload.php` to prevent conflicts with plugins that use the same packages.

### Credits

This plugin is made by using the official [MailerLite API](https://developers.mailerlite.com/docs).
