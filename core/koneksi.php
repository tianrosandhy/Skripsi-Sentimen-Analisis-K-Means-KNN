<?php

if(is_file('core/credentials.php')){
	require_once('core/credentials.php');
	require_once("core/function.php");
	require_once("core/analisis.php");
	require_once("core/nazief.php");	
}
else{
	die('Please create database credentials file first!');
}
