<?php

/**
 * Sample Script for AD LDAP authentication in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.adldap_sample.php 585 2018-08-01 12:13:12Z v_martin.dolezal $ 
 */
require_once('adLDAP/src/adLDAP.php');

function AuthHelper($username, $password, array& $groups)
{
    
    // change the following lines to your needs
    $server = array('example.com');
    $account_suffix = '@myad.local';
    
    $options = array
    (
        'domain_controllers' => $server,
        'ad_port' => '389',
        'use_ssl' => false,
        'base_dn' => null,
        'account_suffix' => $account_suffix,
        'real_primarygroup' => true,
        'recursive_groups' => false,
        'admin_username' => $username,
        'admin_password' => $password,
    );

    try 
    {
        $adldap = new adLDAP($options);
        if(!$adldap->authenticate($username, $password))
        {
            sep_log("ldap: authenticate user '$username' failed.");
            return false;
        }

        $groups = $adldap->user()->groups($username);
    } 
    catch(adLDAPException $e) 
    { 
        sep_log($e->getMessage());
        return false;
    }
    
    return true;
}

/**
* SEPEventHandler class
*
* Implements the event handler class. Must extend SEPEventHandlerBase. (sample)
*
* @package SupervisionServer
* @subpackage User defined
* @access public
* @see SEPEventHandlerBase
*/
class SEPEventHandler extends SEPEventHandlerBase 
{
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
   
   public function OnUserAuthorize($username, $password, array& $groups) { // bool
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
