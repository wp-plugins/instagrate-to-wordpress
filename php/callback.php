<?php
session_start();

require_once 'instagram.php';

$instagram = new itw_Instagram(CLIENT_ID, CLIENT_SECRET, null);

if(isset($_GET['error']) || isset($_GET['error_reason']) || isset($_GET['error_description'])){
    
	// Throw error message... the user might have pressed Deny.	
	$_SESSION['error_reason'] = $_GET['error_reason'];
	$_SESSION['error_description'] = $_GET['error_description'];
	
}
else
{

	$url =  itw_curPageURL();
	$url = preg_replace('/\?.*/', '', $url);

	$access_token = $instagram->getAccessToken($_GET['code'], REDIRECT_URI.'?return_uri='.$url); 

	$accesstkn = $access_token->access_token;
	$username = $access_token->user->username;
	$userid = $access_token->user->id;

	$_SESSION['access_token'] = $accesstkn;
	$_SESSION['username'] = $username;
	$_SESSION['userid'] = $userid;


}

$redirect = itw_adminOptionsURL($url);

//print $redirect;
header("Location: ".$redirect);

?>