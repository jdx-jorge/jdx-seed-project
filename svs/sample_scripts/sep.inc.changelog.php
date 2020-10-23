<?php

/**
 * Sample Script for cell change event in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.changelog.php 585 2018-08-01 12:13:12Z v_martin.dolezal $ 
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
   
   public function InitCubeWorker($database, $cube) {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Get triggering area >>");

      if($database == "Demo") {
         if($cube == "Sales") {
            // array, $elements (nested: dimensions, element names)
            $AreaA = array(
               DIMENSION_TOTAL,
               DIMENSION_TOTAL,
               DIMENSION_TOTAL,
               array("2015"),
               array("Budget"),
               DIMENSION_TOTAL
            );
            $this->WatchCubeArea($AreaA, 'FunctionA');
         }
      }
   }

   public function OnDrillThrough( $database, $cube, $mode, $arg ) { // string
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

/**
* Sample cell change handler function.
*
* @access public
* @see SEPEventHandlerBase::SensitizeArea()
* @see SEPEventHandlerBase::CellChange()
*/
function FunctionA($database, $cube, $areaid,$sid2, array $coordinates, $value, $splashMode, $additive) {
      sep_log("<< Cell change handler for area A >>");
      $user = get_user_for_sid($sid2); // user who made the change
      $groups = get_groups_for_sid($sid2); // groups of the user who made the change
      
      # write value to cube
      $val = palo_data("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4], $coordinates[5]);
      $write = palo_setdataa($value, TRUE, "SupervisionServer/$database", $cube, $coordinates);         
      
      $logline = "<< Cell changed: User: $user, New value: $value, Old value: $val (".'"'."$database/$cube";
      foreach($coordinates as $c)
         $logline .= '", "' . $c;
      $logline .= '"'.") >>";
      sep_log($logline);
}
?>