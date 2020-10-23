<?php

/**
 * Sample Script for cell change event in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.on_cell_copy.php 585 2018-08-01 12:13:12Z v_martin.dolezal $  
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

   # setting the cubes or part of cubes which should be triggered
   public function InitCubeWorker($database,$cube)
   {
      if($database == "Demo")
      {
         # set triggering area for cube Sales
         if($cube == "Sales")
         {
            $AreaA = array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL, DIMENSION_TOTAL,DIMENSION_TOTAL,array('Units','Turnover'));
        $this->WatchCubeArea($AreaA,'SalesCube');
            $this->WatchCubeAreaCopy($AreaA, 'SalesCubeCopy');
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

function SalesCube($database, $cube, $areaid, $sid2, $coordinates, $value, $splashMode, $additive)
{

   $user = get_user_for_sid($sid2); # user who made the change
   $groups = get_groups_for_sid($sid2); # groups of the user who made the change

   if ($coordinates[5] == 'Units')
   {
      # read the Price of target product from its attribute cube
      $unit_price = palo_dataa("SupervisionServer/$database", '#_Products',array('Price Per Unit',$coordinates[0]));      

      # calculate turnover
      $turnover_cell = array ($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4], 'Turnover');
      $turnover_value = $unit_price * $value;

      # get old units value
      $oldUnitsVal = palo_data("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4], $coordinates[5]);

      # write original dataset at given coordinates in 'Sales' cube
      # (notice that in case of a triggered event you may decide yourself whether the input data should be written to
      # database or not. In case you want it written you must not forget to write it into the database)
      $write = palo_setdataa($value, FALSE, "SupervisionServer/$database", $cube, $coordinates);

      # write calculated turnover into column 'Turnover' in 'Sales' cube
      $write = palo_setdataa($turnover_value, FALSE, "SupervisionServer/$database",$cube, $turnover_cell);
   }
   elseif ($coordinates[5] == 'Turnover')
   {
      # Throws a popup notice in Excel
      sep_error("Warning", "No one is allowed to change the Turnover");
   }
}

function SalesCubeCopy($database, $cube, $areaid, $sid2, $coordinatesFrom, $coordinatesTo, $value, $function, $areaPredict, $useRules)
{

   $user = get_user_for_sid($sid2); # user who made the change
   $groups = get_groups_for_sid($sid2); # groups of the user who made the change

   if ($coordinatesTo[5] == 'Units')
   {
      # read the Price of target product from its attribute cube
      $unit_price = palo_dataa("SupervisionServer/$database", '#_Products',array('Price Per Unit',$coordinatesTo[0]));

      # check if value is not set, i.e. 'copy' is used
      if (!$value) {
         # if 'copy' is used, read the Units source value that is copied
         $operation_type = "copy";
         $source_units_value = palo_data("SupervisionServer/$database", $cube, $coordinatesFrom[0], $coordinatesFrom[1], $coordinatesFrom[2], $coordinatesFrom[3], $coordinatesFrom[4], $coordinatesFrom[5]);
      } else {
         # check whether 'like' or 'predict'
         if (!$function) {
            $operation_type = "like";
      } else {
            $operation_type = "predict";
         }
         # in both cases, uses the value passed on by OLAP server for calculation
         $source_units_value = $value;
      }

      # calculate turnover
      $turnover_value = $unit_price * $source_units_value;

      # set coordinates for calculated copy operations
      $turnover_cell_from = array ($coordinatesFrom[0], $coordinatesFrom[1], $coordinatesFrom[2], $coordinatesFrom[3], $coordinatesFrom[4], 'Turnover');
      $turnover_cell_to = array ($coordinatesTo[0], $coordinatesTo[1], $coordinatesTo[2], $coordinatesTo[3], $coordinatesTo[4], 'Turnover');
      
      # execute copy operation
      palo_cellcopy("SupervisionServer/$database", $cube, $coordinatesFrom, $coordinatesTo, $value, $useRules);

      # execute like operation for element 'Turnover' in 'Sales' cube
      palo_cellcopy("SupervisionServer/$database", $cube, $turnover_cell_from, $turnover_cell_to, $turnover_value, $useRules);

      # write log message
      sep_log('Executed '.$operation_type.' operation for Units value, calculated turnover');
      
   }
   elseif ($coordinatesTo[5] == 'Turnover')
   {
      # Throws a popup notice in Excel
      sep_error("Warning", "No one is allowed to change the Turnover");

      # write log message
      sep_log('Blocked '.$operation_type.' operation for Turnover value');
   }
}
 
?>
