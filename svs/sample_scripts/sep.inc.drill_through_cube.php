<?php

/**
 * Sample Script for DrillThrough to base elements in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.drill_through_cube.php 585 2018-08-01 12:13:12Z v_martin.dolezal $  
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
   }

   public function OnDrillThroughExt( $database, $cubename, $mode, $arg, $sid ) { // string
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< OnDrillThrough >>");

      palo_ping('SupervisionServer');

      // Dimensionen
      $dim_list = palo_cube_list_dimensions('SupervisionServer/'.$database, $cubename);
      $ele_list = str_getcsv($arg, ',', '"');
   
      $area = array();
      $maxBaseElements = 100000;
      $numBaseElements = 0;

      for($i=0;$i<count($dim_list);$i++) 
      {
         $num_base_elements = palo_subsetsize('SupervisionServer/'.$database, $dim_list[$i], 1, null, palo_hfilter(null, false, false, 2), null, null, null, null, palo_sort(1, 0, null, 0, null, 0, 1));
         $num_base_elements_for_element = palo_subsetsize('SupervisionServer/'.$database, $dim_list[$i], 1, null, palo_hfilter($ele_list[$i], false, false, 2), null, null, null, null, palo_sort(1, 0, null, 0, null, 0, 1));
                  
         if($num_base_elements != $num_base_elements_for_element)
         {
            $dim_elements = array();
            $ele_type = palo_etype('SupervisionServer/'.$database, $dim_list[$i], $ele_list[$i]);
            if($ele_type == 'consolidated') 
            {
               $elements = palo_element_list_descendants('SupervisionServer/'.$database, $dim_list[$i], $ele_list[$i]);
               foreach($elements as $key => $element) 
               {
                  if( palo_etype('SupervisionServer/'.$database,   $dim_list[$i], $element['name']) == 'consolidated') 
                  {
                     unset($elements[$key]);
                  } 
                  else
                  {
                     ++$numBaseElements;
                     $dim_elements[] = $element['name'];                     
                  }
               }
            } 
            else
            {
               $dim_elements[] = $ele_list[$i];
               ++$numBaseElements;
            }
            $area[] = $dim_elements;
         }
         else
         {
            $area[] = array();
         }         
      }
      
      // check for query length
      if($numBaseElements >= $maxBaseElements)
      {
         $cellerror = 'The query has too many base elements: '.$numBaseElements.'. The querylenght is limited to '.$maxBaseElements.' base elements.';
         sep_log($cellerror);
         sep_error("Warning", $cellerror);
         return $cellerror;
      }
      
      // max rows for export
      $max_rows = 1000;
      
      // do the cell export with no condition
      $values = palo_getdata_export('SupervisionServer/'.$database, $cubename, true, true, '', $max_rows, null, $area);      

      $retVal = '';

      // build the header for output
      $dimensions = palo_cube_list_dimensions('SupervisionServer/'.$database, $cubename);
      foreach($dimensions as $dimension) {
         $retVal .= '"'.mb_strtoupper($dimension, 'UTF-8').'";';
      }
      $retVal .= '"VALUE";'."\n";

      // if no data, only return the header
      if(count($values) == 0) {
         return $retVal;
      }
      
      // convert the result to csv output
      foreach($values as $value)
      {
         foreach($value['path'] as $p) {
            $retVal .= '"'.$p.'";';
         }
         $retVal .= '"'.str_replace('"', '""', $value['value']).'";'."\n";
      }   
      
      return $retVal;
   }

   public function InitDimensionWorker()
   {
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Get triggering dimensions >>");
   }
}
?>
