<?php

/**
 * Sample Script for Novell LDAP authentication in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.novell_ldap_sample.php 585 2018-08-01 12:13:12Z v_martin.dolezal $ 
 */
 
function AuthHelper($username, $password, &$groups)
{
   // LDAP Adminuser         
   $adminuser = "";
   $adminpass = "";

   // BaseDN
   $sBaseDn = 'o=dummy';

   // LDAP Server         
   $servername = "127.0.0.1";
   $serverport = 389;
   $serverversion = 3;

   // connect to LDAP
   $conn = ldap_connect( $servername, $serverport );
   ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $serverversion);
   ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
                     
   // check user against Novell eDirectory   
   if(($bind = @ldap_bind($conn)) == false) 
   {
      sep_log("failure: ldap_bind");
      ldap_close($conn);
      return false;
   }
   
   // search for user
   if (($res_id = ldap_search($conn, $sBaseDn, "(&(objectClass=Person)(uid=".$username."))")) == false) {
      sep_log("failure: search in LDAP-tree failed");
      ldap_close($conn);
      return false;
   }

   if (ldap_count_entries($conn, $res_id) > 1) {
      sep_log("failure: username $username found more than once");
      ldap_close($conn);
      return false;
   }

   if(($entry_id = ldap_first_entry($conn, $res_id))== false) {
      sep_log("failur: entry of searchresult couln't be fetched");
      ldap_close($conn);
      return false;
   }

   if(($user_dn = ldap_get_dn($conn, $entry_id)) == false) {
      sep_log("failure: user-dn coulnd't be fetched");
      ldap_close($conn);
      return false;
   }
   if(($link_id = ldap_bind($conn, $user_dn, $password)) == false) {
      sep_log('Unable to bind to LDAP Server - User credentials incorrect for '.$user_dn);
      ldap_close($conn);
      return false;
   }

   // bind to LDAP server as admin
   $oLdapBind = ldap_bind( $conn, $adminuser, $adminpass );
   if(!$oLdapBind) {
      sep_log('Unable to bind to LDAP Server');
      ldap_close($conn);
      return false;
   }

   // find the requested user
   $oLdapSearch = ldap_search($conn, $sBaseDn, 'CN='.$username);
   if ($oLdapSearch === false) {
      sep_log('Error searching LDAP Server');
      ldap_close($conn);
      return false;

   }

   // get the list of entries
   $oLdapResult = ldap_get_entries($conn, $oLdapSearch);

   // if getting entries failed then notify user
   if ($oLdapResult === false)
   {
      sep_log('Error getting entries from LDAP Server');
      ldap_close($conn);
      return false;
   }

   // get all groups if user is ok
   if (ldap_count_entries($conn, $oLdapSearch) == 1)
   {      
      $oGroupsResult = $oLdapResult[0]['groupmembership'];
      foreach($oGroupsResult as $aGroups) {
         $tmp = explode(",", $aGroups);               
         foreach($tmp as $key=>$value) {
            $groups[] = substr($value, strpos($value, '=')+1);         
         }
      }
      
      ldap_close($conn);
      if(count($groups) == 0) {
         return false;
      } else {
         return true;
      }            
   }
   else
   {
      sep_log('cannot find user');
      ldap_close($conn);
      return false;
   }
}

class SEPEventHandler extends SEPEventHandlerBase {

   public function OnUserLogin($username) { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User logged in: $username >>");
   }

   public function OnUserLogout($username) { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User logged out: $username >>");
   }

   public function OnUserAuthenticate($username, $password) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authenticate >>");
      $dummy = array();
      return AuthHelper($username, $password, $dummy);
   }
   
   public function OnUserAuthorize($username, $password, &$groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authorize >>");
      return AuthHelper($username, $password, $groups);
   } 
   
   public function OnWindowsUserAuthenticate($domain, $username, array $winGroups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User Windows authenticate, domain $domain, username $username >>");
      return true;
   }
   
   public function OnWindowsUserAuthorize($domain, $username, array $winGroups, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User Windows authorize, domain $domain, username $username >>");
      $groups = $winGroups;
      return true;
   }
   
   public function OnSAMLUserAuthenticate(&$username, array $attributes) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User SAML authenticate, username $username >>");
      return true;
   }
   
   public function OnSAMLUserAuthorize(&$username, array $attributes, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User SAML authorize, username $username >>");
      $groups = array();
      return true;
   }
   
   public function OnServerShutdown() { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Server shutdown handler >>");
   }

   public function OnDatabaseSaved($database) { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Databased saved : $database >>");
   }

   public function OnTermination() { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Termination handler >>");
   }

   public function InitCubeWorker($database, $cube) { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Get triggering area >>");
   }

   public function OnDrillThroughExt( $database, $cube, $mode, $arg, $sid ) { // string
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< OnDrillThrough >>");      
      return "not implemented;\r\n";
   }

   public function InitDimensionWorker()
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Get triggering dimensions >>");
   }   
}

?>
