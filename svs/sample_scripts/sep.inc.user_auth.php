<?php

/**
 * Sample Script for authentication events in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.user_auth.php 606 2019-04-16 07:50:31Z v_martin.dolezal $ 
 */
 
class SEPEventHandler extends SEPEventHandlerBase 
{
   public function OnUserLogin($username) 
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User logged in: $username >>");
   }

   public function OnUserLogout($username) 
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>"); 
      sep_log("<< User logged out: $username >>");
   }
   
   public function OnUserAuthenticate($username, $password) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authenticate >>");
      if ( ($username=="TestUser" && $password=="TestPassword") ||
            ($username=="admin" && $password=="admin") )   {
         return true;
      } else {
         return false;
      }
   }

   public function OnUserAuthorize($username, $password, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authorize >>");
      if ( ($username=="TestUser" && $password=="TestPassword") ||
            ($username=="admin" && $password=="admin") )   {
         $groups = array("TestGroup1", "TestGroup2");
         return true;
      } else {
         return false;
      }
   }

   public function OnWindowsUserAuthenticate($domain, $username, array $winGroups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User Windows authenticate, domain $domain, username $username >>");
      if ($username=="Username" && $domain=="Domain")   {
         return true;
      } else {
         return false;
      }
   }
		
   public function OnWindowsUserAuthorize($domain, $username, array $winGroups, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User Windows authorize, domain $domain, username $username >>");
      if ($username=="Username" && $domain=="Domain")   {
         $groups = array("TestGroup1", "TestGroup2");
         return true;
      } else {
         return false;
      }
   }
   
   public function OnSAMLUserAuthenticate(&$username, array $attributes) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User SAML authenticate, username $username >>");
      foreach ($attributes as $key => $value) {
         sep_log("Attribute ".$key.": ".$value);
         if ($key == "UserID" && $value == "AllowedUserID") {
            return true;
         }
      }
      return false;
   }
   
   public function OnSAMLUserAuthorize(&$username, array $attributes, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User SAML authorize, username $username >>");
      foreach ($attributes as $key => $value) {
         sep_log("Attribute ".$key.": ".$value);
         if ($key == "UserID" && $value == "AllowedUserID") {
            $groups = array("TestGroup1", "TestGroup2");
            return true;
         }
      }
      return false;
   }
   
   public function OnServerShutdown() 
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>"); 
      sep_log("<< Server shutdown handler >>");
   }

   public function OnTermination() 
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>"); 
      sep_log("<< Termination handler >>");
   }

   public function OnDatabaseSaved($database) 
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>"); 
      sep_log("<< Databased saved : $database >>");
   }

   # setting the cubes or part of cubes which should be triggered
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