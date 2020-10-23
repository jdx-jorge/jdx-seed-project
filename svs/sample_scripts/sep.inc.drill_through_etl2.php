<?php

/**
 * Sample Script for DrillThrough to ETL Server detail data in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.drill_through_etl.php 585 2018-08-01 12:13:12Z v_martin.dolezal $  
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
      sep_log("<< OnDrillThrough2 >>");      
      palo_ping('SupervisionServer');
      $ele_list = str_getcsv($arg, ',', '"');
     
      $maxBaseElements = 100000;
      $retVal = 'no result or error';
      $cellerror = 'The query has too many base elements: '.$bc.'. The querylenght is limited to '.$maxBaseElements.' base elements.';

      // Change line parameter according to your needs. 0 puts out all values.
      $line = 50000;
      try
      {
         $wsdl_url = 'http://127.0.0.1:8885/etlserver/services/ETL-Server?wsdl';
         $server = @new SoapClient($wsdl_url, array('exceptions' => true, 'location' => $wsdl_url));
         
         //attempt OLAP session adoption with session ID passed to DrillThrough event
         $login_attempt = $server->login(array('olapSession' => $sid))->return;
         $session = $login_attempt->result;
   
         // check if session is valid, set headers if okay, return if not
         if (!$session){
            return "Error during OLAP session adoption!";
         } else {
            $header = new SoapHeader('http://ns.jedox.com/ETL-Server/', 'etlsession', $session);   
            $server->__setSoapHeaders($header);
         }         

         // send drillThrough request
         $datastore = $database.'.'.$cubename;
         $response = $server->drillThrough2(array('datastore' => $datastore, 'cellPath' => $ele_list, 'lines' => $line));
         $return = $response->return;		 
         if($bc >= $maxBaseElements){
            sep_log($cellerror);
            sep_error("Warning",$cellerror);
            return $cellerror;
         }

         $linebreak = getenv("WINDIR") ? "\r\n" : "\n";	 
         if(!$return->valid) {
            sep_log($return->errorMessage);
            sep_error("Warning", $return->errorMessage);         
            $retVal = $return->errorMessage;
         }
         else if(count(explode($linebreak, $return->result)) < 3) {
            $retVal = 'no result';
         }         
         else if(strlen(trim($return->result)) > 0) {
            $retVal = str_replace($linebreak, ";\r\n", $return->result);
         }
         
      } catch (SoapFault $fault) {
         sep_log("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");
      } catch (Exception $e) {
         sep_log($e->getMessage());
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
