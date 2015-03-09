<?php
  /** 
  * Plugin Name: Really Simple Subscriptions
  * Description: A simple subscription system that allows bulk e-mailing to subscribers.
  * Author: James "Thyme Cypher" Landrum
  * Version: 0.9.0
  * License: MIT License
  */

  // Core Level
  include_once('backcompat.php');
  include_once('updater.php');

  // Internal Objects
  include('db.php');
  include('mailer.php');

  // Api
  include('api.php');

  // Hooks
  include('hooks.php');

  // Front-end
  include('administration.php');
  include('integration.php');

  register_activation_hook( __FILE__, 'RSSub\_Private\setup_plugin' );


  if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
    $config = array(
      'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
      'proper_folder_name' => 'wp-rssub', // this is the name of the folder your plugin lives in
      'api_url' => 'https://api.github.com/repos/jlandrum/wp-rssub', // the GitHub API url of your GitHub repo
      'raw_url' => 'https://raw.github.com/jlandrum/wp-rssub/master', // the GitHub raw url of your GitHub repo
      'github_url' => 'https://github.com/jlandrum/wp-rssub', // the GitHub url of your GitHub repo
      'zip_url' => 'https://github.com/jlandrum/wp-rssub/zipball/master', // the zip url of the GitHub repo
      'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
      'requires' => '3.0', // which version of WordPress does your plugin require?
      'tested' => '3.3', // which version of WordPress is your plugin tested up to?
      'readme' => 'README.md', // which file to use as the readme for the version number
      'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
    );
  new WP_GitHub_Updater($config);
  }
