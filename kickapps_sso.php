<?php
/*
Plugin Name: KickApps SSO
Plugin URI: http://www.kickapps.com
Description: This plugin will allow creation of your users in your kickapps community.
Version: 1.0
*/
/*
    KickApps Single Sign-On Wordpress Plugin
    Copyright (C) 2007  KickApps Inc.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

/*
This function will create the SOAP request and send it to the KickApp server.
*/
function ka_sign_in($user_obj, $save_to_session){

	$userid=$user_obj->ID;
	$affiliateUserName = get_option('kickapps_username');
	$affiliateEmail = get_option('kickapps_email');
	$affiliateSiteName = get_option('kickapps_site_name');
	$requestType = "signInRegisterUser";

	/* NOTE below if you are using DNS masking, you MUST replace affiliate.kickapps.com with
	your DNS masked URL e.g. community.yoursite.com */
	$kickapps_client = new SOAPClient("http://affiliate.kickapps.com/kickapps/soap/KaSoapSvc?WSDL");

	$kickapps_client->trace = 4;
	$auth->AffiliateUserName = $affiliateUserName;
	$auth->AffiliateEmail = $affiliateEmail;
	$doc = new DomDocument('1.0', 'UTF-8');
	$header_input = array('ns2:AffiliateUserName' => $affiliateUserName, 'ns2:AffiliateUserEmail' =>$affiliateEmail);
	$headerVar = new SoapVar($header_input, SOAP_ENC_OBJECT);
	$headert = new SoapHeader('http://schemas.kickapps.com/services/soap',	'AffiliateAuthenticationToken', $headerVar);
	$kickapps_client->__setSoapHeaders(array($headert));
	$nodeKassoRequest = $doc->createElementNS("http://schemas.kickapps.com/services/soap","KassoRequest");
	$nodeKassoRequest = $doc->appendChild($nodeKassoRequest);
	$requestName = $nodeKassoRequest->setAttribute("requestName", $requestType);

	/* dynamically replace this with the username of the member currently registering or logging in*/
	$userName = $user_obj->user_login;

	/* dynamically replace this with the email of the member currently registering */
	$email = $user_obj->user_email;

	/* dynamically replace this with the first name of the member currently registering OPTIONAL */
	$firstName = get_usermeta($userid,'first_name');

	/* dynamically replace this with the last name of the member currently registering OPTIONAL */
	$lastName = get_usermeta($userid,'last_name');

	/* build the request XML document */
	$nodeaffiliateUserName = $doc->createElement("Param");
	$newnodeaffiliateUserName = $nodeKassoRequest->appendChild($nodeaffiliateUserName);
	$paraNameAttr = $nodeaffiliateUserName->setAttribute("paramName", "affiliateUserName");
	$paramValue = $nodeaffiliateUserName->setAttribute("paramValue", $affiliateUserName);
	$nodeaffiliateEmail = $doc->createElement("Param");
	$newnodeaffiliateEmail = $nodeKassoRequest->appendChild($nodeaffiliateEmail);
	$paraNameAttr = $nodeaffiliateEmail->setAttribute("paramName", "affiliateEmail");
	$paramValue = $nodeaffiliateEmail->setAttribute("paramValue", $affiliateEmail);
	$nodeaffiliateSiteName = $doc->createElement("Param");
	$newnodeaffiliateSiteName = $nodeKassoRequest->appendChild($nodeaffiliateSiteName);
	$paraNameAttr = $nodeaffiliateSiteName->setAttribute("paramName", "affiliateSiteName");
	$paramValue = $nodeaffiliateSiteName->setAttribute("paramValue", $affiliateSiteName);
	$nodeuserName = $doc->createElement("Param");
	$newnodeuserName = $nodeKassoRequest->appendChild($nodeuserName);
	$paraNameAttr = $nodeuserName->setAttribute("paramName", "userName");
	$paramValue = $nodeuserName->setAttribute("paramValue", $userName);
	$nodeemail = $doc->createElement("Param");
	$newnodeemail = $nodeKassoRequest->appendChild($nodeemail);
	$paraNameAttr = $nodeemail->setAttribute("paramName", "email");
	$paramValue = $nodeemail->setAttribute("paramValue", $email);
	$nodeforce = $doc->createElement("Param");
	$newnodeemail = $nodeKassoRequest->appendChild($nodeforce);
	$paraNameAttr = $nodeforce->setAttribute("paramName", "forceEmailUpdate");
	$paramValue = $nodeforce->setAttribute("paramValue", "true");


	$nodebirthday = $doc->createElement("Param");
	$newnodebirthday = $nodeKassoRequest->appendChild($nodebirthday);
	$paraNameAttr = $nodebirthday->setAttribute("paramName", "birthday");
	$paramValue = $nodebirthday->setAttribute("paramValue", "1970-01-01");

	$nodefirstName = $doc->createElement("Param");
	$newnodefirstName = $nodeKassoRequest->appendChild($nodefirstName);
	$paraNameAttr = $nodefirstName->setAttribute("paramName", "firstName");
	$paramValue = $nodefirstName->setAttribute("paramValue", $firstName);
	$nodelastName = $doc->createElement("Param");
	$newnodelastName = $nodeKassoRequest->appendChild($nodelastName);
	$paraNameAttr = $newnodelastName->setAttribute("paramName", "lastName");
	$paramValue = $newnodelastName->setAttribute("paramValue", $lastName);

	$doc->appendChild($nodeKassoRequest);
	/* place the registerNewUser call to KickApps */
	try {
		$result = $kickapps_client->processRequest($doc->saveXML());
		if ($save_to_session ==1){
			$dom_doc = new DOMDocument();
			$dom_doc->loadXML($result);
			$params = $dom_doc->getElementsByTagName("Param");

			$sessionParam = $params->item(1);
			$transactionParam = $params->item(2);

			$st = $sessionParam->getAttribute('paramValue');

			$tid = $transactionParam->getAttribute('paramValue');

			$_SESSION['ka_st'] = $st;
			$_SESSION['ka_tid'] = $tid;
                           //incase sessions not enabled/working
			setcookie('ka_tid', $tid, 0);
                           setcookie('ka_st', $st, 0);
		}
	} catch (SOAPFault $soapex) {
		error_log("KickApps SSO: ".$soapex->getMessage());
	}
}

function ka_login_user($user_login) {
	$user_obj = new WP_User(0,$user_login);
	ka_sign_in($user_obj, 1);
}


function ka_register_user($userid){
	$user_obj = new WP_User($userid);
	ka_sign_in($user_obj, 0);
}


function kickapps_add_pages() {

	add_options_page(__('KickApps SSO Options'), __('KickApps SSO Options'), 10, 'kickapps-single-sign-on-module/kickapps_sso_options.php');
}

function add_kickapps_vars($content){
	$ka_st = $_SESSION['ka_st'];
	$ka_tid = $_SESSION['ka_tid'];
	if(empty($ka_st)&& is_user_logged_in() ){
		$ka_st = $_COOKIE['ka_st'];
	}
	if(empty($ka_tid)&& is_user_logged_in() ){
		$ka_tid = $_COOKIE['ka_tid'];
	}

	if ($ka_st != '' && $ka_st != null) {
		$content = str_replace('.kickAction?', '.kickAction?st=' .$ka_st . '&tid=' .$ka_tid . '&' , $content);
		$content = str_replace('<param name="FlashVars" value="', '<param name="FlashVars" value="st=' .$ka_st . '&amp;tid=' .$ka_tid . '&amp;' , $content);
		$content = str_replace('FlashVars="', 'FlashVars="st=' .$ka_st . '&amp;tid=' .$ka_tid . '&amp;' , $content);
	}
	return $content;
}

function ka_logout_user(){
	setcookie('ka_tid', '', 0);
	setcookie('ka_st', '', 0);

}

add_action('user_register', 'ka_register_user');
add_action('wp_login','ka_login_user');
add_action('wp_logout','ka_logout_user');

add_action('admin_menu', 'kickapps_add_pages');
add_filter('the_content', 'add_kickapps_vars');
add_filter('comment_text', 'add_kickapps_vars');
?>