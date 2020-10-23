<?php

/**
 * Sample Script for element events in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.elements.php 585 2018-08-01 12:13:12Z v_martin.dolezal $  
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
      return true;
   }
   
   public function OnUserAuthorize($username, $password, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authorize >>");
      return true;
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

   public function OnDrillThroughExt( $database, $cube, $mode, $arg, $sid ) { // string
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< OnDrillThrough >>");
      return "not implemented;\r\n";
   }
   
   public function InitCubeWorker($database, $cube)
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
   }
   
   public function InitDimensionWorker()
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< DefineDimensionWorker >>");
      
      $this->WatchElementCreate("Demo", "Products", 'MyDimCreate');
      
      $this->WatchElementDestroy("Demo", "Products", 'MyDimDestroy');
      
      $this->WatchElementRename("Demo", "Products", 'MyDimRename1');
      $this->WatchElementRename("Demo", "Years", 'MyDimRename2');      
   }
}         
 
function MyDimCreate($database, $dimension, $element, $sid)
{
   sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
   sep_log("In database $database, dimension $dimension, element \"$element\" was created.");
}

 
function MyDimDestroy($database, $dimension, $element, $sid)
{
   sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
   sep_log("In database $database, dimension $dimension, element \"$element\" was destroyed. $sid");
}


function MyDimRename1($database, $dimension, $oldName, $newName, $sid)
{
   sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
   sep_log("In database $database, dimension $dimension, element \"$oldName\" was renamed to \"$newName\" ($sid)");
}

 
function MyDimRename2($database, $dimension, $oldName, $newName, $sid)
{
   sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
   sep_log("In database $database, dimension $dimension, element \"$oldName\" was renamed to \"$newName\".");
}

 
?>