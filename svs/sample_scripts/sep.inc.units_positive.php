<?php
/**
 * Sample Script for cell change event in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.units_positive.php 585 2018-08-01 12:13:12Z v_martin.dolezal $
 */
 
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

   public function InitCubeWorker($database, $cube) { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Get triggering area >>");
      if ($database == "Demo")
      {
         if ($cube == "Sales")
         {
               //Set the area of the database:Demo and the cube:Sales that has to be watched
               //The variable DIMENSION_TOTAL includes all elements of the dimension. 
               //The dimensions follow the same order as in the modeller of the Excel addin. 
               $Area_to_watch = Array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,Array('Units'));
               //The next line tells the SVS to execute a function named neagatveUnits once a cellvalue in the 
               //watched area has been changed.
               $this->WatchCubeArea($Area_to_watch,'negativeUnits');
         }
      }
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

//Function negativeUnits: Params 
//$database: name of the database
//$cube: name of the cube
//$areaid: areaid
//$sid: Session ID
//$coordiantes: coordinates in the cube where the event has been triggered
//$value: the value entered by the user
//On execution the function will check if the entered value is higher or equl zero.
//If the value is higher or equal zero the database will be locked to write the value.
//Has the value been writen the lock will be disengaged.
//If the entered value is negative it wont be writen and a message will be displayed in Excel.
function negativeUnits($database,$cube,$areaid,$sid,$coordinates,$value,$splashMode,$additive) {
   //check if value is not negative
   if ($value >= 0) {
      //write the value at the desginated coordiantes
      $write = palo_setdataa($value,'SPLASH_MODE_DEFAULT',"SupervisionServer/$database",$cube,$coordinates);
   } else {
      //Popup-message in Excel if the value is negative
      sep_error(" $coordinates[5] must not be negative.","No negative $coordinates[5] are allowed!");
   }
}

?>
