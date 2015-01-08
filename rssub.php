<?php
	/** 
  * Plugin Name: Really Simple Subscriptions
  * Description: A simple subscription system that allows bulk e-mailing to subscribers.
  * Author: James "Thyme Cypher" Landrum
  * Version: 1.0
  * License: MIT License
  */


	// Internal Objects
	include('db.php');
	include('mailer.php');
	
	// Api
	include('api.php');

	// Hooks
	include('hooks.php');

	// Front-end
  include('administration.php');