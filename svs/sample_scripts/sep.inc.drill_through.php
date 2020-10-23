<?php

/**
 * Sample Script for DrillThrough to base elements in Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.drill_through.php 585 2018-08-01 12:13:12Z v_martin.dolezal $  
 */
 
class SEPEventHandler extends SEPEventHandlerBase {
   public function OnUserLogin($username) { // void
      sep_log("<< User logged in: $username >>");
   }

   public function OnUserLogout($username) { // void
      sep_log("<< User logged out: $username >>");
   }

   public function OnUserAuthenticate($username, $password) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authentication refused >>");
      return false;
   }
   
   public function OnUserAuthorize($username, $password, array& $groups) { // bool
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< User authorization refused >>");
      return false;
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
      sep_log("<< Server shutdown handler >>");
   }

   public function OnDatabaseSaved($database) { // void
      sep_log("<< Databased saved : $database >>");
   }

   public function OnTermination() { // void
      sep_log("<< Termination handler >>");
   }

   public function InitCubeWorker($database, $cube) {
	  if($database == "Sales")
      {
         # set triggering area for cube Sales
         if($cube == "Tenure (Planning)")
         {
			/*
			$coordinates[0] -> Version
			$coordinates[1] -> Year
			$coordinates[2] -> Period
			$coordinates[3] -> Brand Code
			$coordinates[4] -> Account Type
			$coordinates[5] -> Plan Type
			$coordinates[6] -> Customer Type
			$coordinates[7] -> Contract Category
			$coordinates[8] -> Contract Term
			$coordinates[9] -> Contract Status Group
			$coordinates[10] -> Price Point
			$coordinates[11] -> KPI
			$coordinates[12] -> Meausre (Plan)
			*/
            // $AreaB = array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,array('Connections','PreToPost'),array('Price Point Mix','Tenure Mix'));
            // $this->WatchCubeArea($AreaB,'StepOne');
            $AreaA = array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,array('Connections','PreToPost'),array('Contract Category Mix','Contract Term Mix'));
            $this->WatchCubeArea($AreaA,'UpdateMixesAndVolumes');
         }   
      }
	//   if($database == "SalesBkup06092020")
    //   {
    //      # set triggering area for cube Sales
    //      if($cube == "Tenure (Planning)")
    //      {
	// 		/*
	// 		$coordinates[0] -> Version
	// 		$coordinates[1] -> Year
	// 		$coordinates[2] -> Period
	// 		$coordinates[3] -> Brand Code
	// 		$coordinates[4] -> Account Type
	// 		$coordinates[5] -> Plan Type
	// 		$coordinates[6] -> Customer Type
	// 		$coordinates[7] -> Contract Category
	// 		$coordinates[8] -> Contract Term
	// 		$coordinates[9] -> Contract Status Group
	// 		$coordinates[10] -> Price Point
	// 		$coordinates[11] -> KPI
	// 		$coordinates[12] -> Meausre (Plan)
	// 		*/
    //         $AreaA = array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,array('Connections','PreToPost'),array('Contract Category Mix','Contract Term Mix','Price Point Mix','Tenure Mix'));
    //         $this->WatchCubeArea($AreaA,'StepOne');
	// 		$AreaB = array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,array('UpgradeFrom','RPCFrom'),array('Adjustment','Tenure Rate'));
    //         $this->WatchCubeArea($AreaB,'FromVolume');
    //      }   
    //   }
   }

   public function OnDrillThroughExt( $database, $cubename, $mode, $arg, $sid ) { // string  

      palo_ping('SupervisionServer');     

      $this->element_list_cache = null;	  

      $ret = $this->DrillThroughETL($database, $cubename, $mode, $arg, $sid);

      // if error message starts with "No Drillthrough defined" make Cube-Drillthrough
      if(strpos($ret, 'No Drillthrough defined') !== false) 
      {
         $ret = $this->DrillThroughCube($database, $cubename, $mode, $arg);
      }
      
      return $ret;
   }

   private $element_list_cache = null;

   private function DrillThroughElementHelper($database, $cubename, $arg)
   {
      if($this->element_list_cache == null)
      {
         $dim_list = palo_cube_list_dimensions('SupervisionServer/'.$database, $cubename);
         $ele_list = str_getcsv($arg, ',', '"');
         
         $this->element_list_cache = array();
         for($i=0;$i<count($dim_list);$i++) {         
            $this->element_list_cache[$i] = palo_element_list_descendants('SupervisionServer/'.$database, $dim_list[$i], $ele_list[$i]);      
         }
      }
      return $this->element_list_cache;
   }

   private function DrillThroughETL($database, $cubename, $mode, $arg, $sid)
   {
      sep_log("<< DrillThroughETL >>");

      // Dimensionen
      $dim_list = palo_cube_list_dimensions('SupervisionServer/'.$database, $cubename);
      $ele_list = str_getcsv($arg, ',', '"');

      $names = $dim_list;
      $lengths = array();
      $values = array();
      $maxBaseElements = 100000;      

      for($i=0;$i<count($dim_list);$i++) 
      {
         $num_base_elements = palo_subsetsize('SupervisionServer/'.$database, $dim_list[$i], 1, null, palo_hfilter(null, false, false, 2), null, null, null, null, palo_sort(1, 0, null, 0, null, 0, 1));
         $num_base_elements_for_element = palo_subsetsize('SupervisionServer/'.$database, $dim_list[$i], 1, null, palo_hfilter($ele_list[$i], false, false, 2), null, null, null, null, palo_sort(1, 0, null, 0, null, 0, 1));
         
         if($num_base_elements != $num_base_elements_for_element)
         {
            $ele_type = palo_etype('SupervisionServer/'.$database, $dim_list[$i], $ele_list[$i]);
            if($ele_type == 'consolidated') 
            {
               $count = 0;
               $element_list = $this->DrillThroughElementHelper($database, $cubename, $arg);
               $elements = $element_list[$i];
               foreach($elements as $key => $element)
               {
                  if( palo_etype('SupervisionServer/'.$database,   $dim_list[$i], $element['name']) == 'consolidated') 
                  {
                     unset($elements[$key]);
                  } 
                  else 
                  {
                     ++$count;
                     $values[] = $element['name'];
                     
                  }
               }      
               $lengths[$i] = $count;
            }
            else 
            {
               $values[] = $ele_list[$i];
               $lengths[$i] = 1;
            }
         }
         else
         {
            $lengths[$i] = 0;
         }
      }
      $bc = $lengths[0];
      for($i = 1; $i < count($lengths); $i++) {      
         $bc += $lengths[$i];
      }
      
      $cellerror = 'The query has too many base elements: '.$bc.'. The querylenght is limited to '.$maxBaseElements.' base elements.';

      // Change line parameter according to your needs. 0 puts out all values.
      $line = 50000;
      try
      {
         $wsdl_url = 'http://127.0.0.1:7775/etlserver/services/ETL-Server?wsdl';
         $server = @new SoapClient($wsdl_url, array('exceptions' => true, 'location' => $wsdl_url));
         
         //attempt OLAP session adoption with sid passed to DrillThrough event
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
         $response = $server->drillThrough(array('datastore' => $datastore, 'names' => $names, 'values' => $values, 'lengths' => $lengths, 'lines' => $line));
         $return = $response->return;

         if($bc >= $maxBaseElements) {
            sep_log($cellerror);
            return $cellerror;
         }
         
         $linebreak = getenv("WINDIR") ? "\r\n" : "\n";

         if(!$return->valid) 
         {
            sep_log($return->errorMessage);      
            return $return->errorMessage;
         }
         else if(count(explode($linebreak, $return->result)) < 3) 
         {
            return 'no result';
         }         
         else if(strlen(trim($return->result)) > 0) 
         {
            return str_replace($linebreak, ";\r\n", $return->result);
         }
         
      } catch (SoapFault $fault) {
         sep_log("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");
      } catch (Exception $e) {
         sep_log($e->getMessage());
      }

      return 'no result or error';
   }

   private function DrillThroughCube($database, $cubename, $mode, $arg)
   {
      sep_log("<< DrillThroughCube >>");

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
               $element_list = $this->DrillThroughElementHelper($database, $cubename, $arg);
               $elements = $element_list[$i];
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
      $max_rows = 10000;
      
      // do the cell export with no condition
      $values = palo_getdata_export('SupervisionServer/'.$database, $cubename, true, true, '', $max_rows, null, $area, true);

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
      sep_log("<< Get triggering dimensions >>");
   }
}


function calculateCategoryMix($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive, $setInput)
{
    // set input is used to load or not load the value passed into this function into the cube
    // this needs to be true to set the user input value, but needs to be false when running this function
    // to recalculate the rule driven, reconciling category volumes.
    if ($setInput == true)
    {
        $bulkCoordinates = array($coordinates);
        $bulkValues = array($value);  
    } else {
        $bulkCoordinates = [];
        $bulkValues = []; 
    }
    

    // get term elements
    $termElements = palo_element_list_children("SupervisionServer/$database", 'Contract Term', 'All Contract Terms');
    $countOfTermElements = count($termElements);
    $terms = [1, $countOfTermElements];
    foreach($termElements as $term)
    {
        array_push($terms, $term['name']);
    }

    // sep_log(print_r($terms, true));

   
    palo_startcachecollect();  

    // get customer type level volume
    palo_datac("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], '~', '~', '~', '~', $coordinates[11], 'Volume');

    // get contract term mix for selected contract category
    palo_datav("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], $coordinates[7], $terms, '~', '~', $coordinates[11], 'Contract Term Mix');


    palo_endcachecollect();


    // get contract term mix for selected contract category
    $termSplit = palo_datav("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], $coordinates[7], $terms, '~', '~', $coordinates[11], 'Contract Term Mix');
    // coordinates to post category volume
    $volumeCoordinates = array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], $coordinates[7], $coordinates[8], $coordinates[9], $coordinates[10], $coordinates[11], 'Volume');
    // get customer type level volume
    $totalCustomerVolume = palo_datac("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], '~', '~', '~', '~', $coordinates[11], 'Volume');
    // calculate total category volume 
    $newCategoryVolume = $totalCustomerVolume * $value;
    array_push($bulkCoordinates, $volumeCoordinates);
    array_push($bulkValues, $newCategoryVolume);

    $i = 0;
    // calculate category volume per term
    foreach($termElements as $term)
    {
        $vol = $newCategoryVolume * $termSplit[$i+2];

        if ($term['name'] == 0)
        {
            $csg = '1_Month_Off';
        } else {
            $csg = $term['name'].'_Months_Remaining';
        }
        $termCoords = array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], $term['name'], '~', '~', $coordinates[11], 'Volume');
        array_push($bulkCoordinates, $termCoords);
        array_push($bulkValues, $vol);

        $csgCoords = array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], $term['name'], $csg, '~', $coordinates[11], 'Volume');
        array_push($bulkCoordinates, $csgCoords);
        array_push($bulkValues, $vol);
        $i = $i + 1;

    }
    
    $write = palo_setdata_bulk("SupervisionServer/$database", $cube, $bulkCoordinates, $bulkValues,false);
}


function calculateTermMix($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive, $setInput)
{
    // set input is used to load or not load the value passed into this function into the cube
    // this needs to be true to set the user input value, but needs to be false when running this function
    // to recalculate the rule driven, reconciling category volumes.
    if ($setInput == true)
    {
        $bulkCoordinates = array($coordinates);
        $bulkValues = array($value);  
    } else {
        $bulkCoordinates = [];
        $bulkValues = []; 
    }
   
    palo_startcachecollect();  

    // get contract category level volume
    palo_datac("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], $coordinates[7], '~', '~', '~', $coordinates[11], 'Volume');

    palo_endcachecollect();


    // get contract term mix for selected contract category
    // $termSplit = palo_datav("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        // $coordinates[5], $coordinates[6], $coordinates[7], $terms, '~', '~', $coordinates[11], 'Contract Term Mix');
    // coordinates to post category volume
    $volumeCoordinates = array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], $coordinates[7], $coordinates[8], $coordinates[9], $coordinates[10], $coordinates[11], 'Volume');
    // get customer type level volume
    $totalTermVolume = palo_datac("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        $coordinates[5], $coordinates[6], $coordinates[7], '~', '~', '~', $coordinates[11], 'Volume');
    // calculate total category volume 
    $newTermVolume = $totalTermVolume * $value;
    array_push($bulkCoordinates, $volumeCoordinates);
    array_push($bulkValues, $newTermVolume);
    sep_log('here');
    sep_log($newTermMix);

    // $i = 0;
    // calculate category volume per term
    // foreach($termElements as $term)
    // {
        $vol = $newCategoryVolume * $termSplit[$i];
        if ($coordinates[8] == 0)
        {
            $csg = '1_Month_Off';
        } else {
            $csg = $coordinates[8].'_Months_Remaining';
        }
        // $termCoords = array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
        //     $coordinates[5], $coordinates[6], $coordinates[7], $coordinates[8], '~', '~', $coordinates[11], 'Volume');
        // array_push($bulkCoordinates, $termCoords);
        // array_push($bulkValues, $vol);

        $csgCoords = array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], $coordinates[8], $csg, '~', $coordinates[11], 'Volume');
        array_push($bulkCoordinates, $csgCoords);
        array_push($bulkValues, $newTermVolume);
        // $i = $i + 1;

    // }
    
    
    $write = palo_setdata_bulk("SupervisionServer/$database", $cube, $bulkCoordinates, $bulkValues,false);
}

function UpdateMixesAndVolumes($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive)
{
    // set mix before recalculating volume
	event_lock_begin($areaid, $sid2);
	$user = get_user_for_sid($sid2); // user who made the change
	$groups = get_groups_for_sid($sid2); // groups of the user who made the change
    
    if ($coordinates[12] == "Contract Category Mix")
    {
        // recalculate category volume for updated mix entry by user
        calculateCategoryMix($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive, true);

        if ($coordinates[7] == "HS")
        {
            $reconcilingVal = 1 - $value;
            $reconcilingCategory = "SIMO";
            $setMix = true;
        } else {
            $reconcilingVal = palo_data("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
                $coordinates[5], $coordinates[6], "Freedom HS", $coordinates[8], $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]);
            $reconcilingCategory = "Freedom HS";
            $setMix = false;
        }
        // get updated value of reconciling category mix category: Freedom HS
        
        
        // recalculate category volume for reconciling mix category
        calculateCategoryMix($database, $cube, $areaid, $sid, array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $reconcilingCategory, $coordinates[8], $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]), 
            $reconcilingVal, $splashMode, $additive, $setMix);
    }

    if ($coordinates[12] == "Contract Term Mix")
    {

        palo_startcachecollect();  
        palo_datac("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], $reconcilingTerm, $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]);
        palo_datac("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], "All Contract Terms", $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]);
        palo_endcachecollect();
        
        // recalculate Term volume for updated mix entry by user
        calculateTermMix($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive, true);
        $reconcilingTerm = "24";
        // get updated value of reconciling term mix category: 24
        $reconcilingVal = 1 - palo_data("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], "All Contract Terms", $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]) + palo_data("SupervisionServer/$database", $cube, $coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], $reconcilingTerm, $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]);
        

        // recalculate category volume for reconciling mix category
        calculateTermMix($database, $cube, $areaid, $sid, array($coordinates[0], $coordinates[1], $coordinates[2], $coordinates[3], $coordinates[4],
            $coordinates[5], $coordinates[6], $coordinates[7], $reconcilingTerm, $coordinates[9], $coordinates[10], $coordinates[11], $coordinates[12]), 
            $reconcilingVal, $splashMode, $additive, true);
    }

    

    event_lock_end();

    


}

function StepOne($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive)
{
    // set mix before recalculating volume
	event_lock_begin($areaid, $sid2);
	$user = get_user_for_sid($sid2); // user who made the change
	$groups = get_groups_for_sid($sid2); // groups of the user who made the change
	
	switch($splashMode)
    {
      // no splashing is used
      case 0:
        $value = $value;
        break;
    
      // default splashing is used
      case 1:
        $value = '#'.$value;
        break;
    
      // add splashing is used
      case 2:
        $value = '!!'.$value;
        break;
    
      // set splashing is used
      case 3:
        $value = '!'.$value;
        break;
    }
	
	// write calculated turnover into column 'Turnover' in 'Sales' cube
    $write = palo_setdataa($value, FALSE, "SupervisionServer/$database", $cube, $coordinates);	
	event_lock_end();
    
	if ($coordinates[0] != 'Actual')
	{
		$wsdl_url = 'http://127.0.0.1:7775/etlserver/services/ETL-Server?wsdl';
		$server = @new SoapClient($wsdl_url, array('exceptions' => true, 'location' => $wsdl_url));
		
		//attempt OLAP session adoption with sid passed to DrillThrough event
		$login_attempt = $server->login(array('olapSession' => $sid))->return;
		$session = $login_attempt->result;
		
		// check if session is valid, set headers if okay, return if not
		if (!$session){
		sep_log("<< Error during OLAP session adoption! >>");
		} else {
		$header = new SoapHeader('http://ns.jedox.com/ETL-Server/', 'etlsession', $session);   
		$server->__setSoapHeaders($header);
		}
		
		// set the locator to be used for the execution
		switch ($coordinates[12])
		{
		 case 'Contract Category Mix': 
		 	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_1';
		  break;
		 case 'Contract Term Mix': 
		 	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_2';
		  break;
		 case 'Price Point Mix': 
		 	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_3';
		  break;
		 default: 
		 	break;
		}
		
		/*
		$coordinates[0] -> Version
		$coordinates[1] -> Year
		$coordinates[2] -> Period
		$coordinates[3] -> Brand Code
		$coordinates[4] -> Account Type
		$coordinates[5] -> Plan Type
		$coordinates[6] -> Customer Type
		$coordinates[7] -> Contract Category
		$coordinates[8] -> Contract Term
		$coordinates[9] -> Contract Status Group
		$coordinates[10] -> Price Point
		$coordinates[11] -> KPI
		$coordinates[12] -> Meausre (Plan)
		*/
		// set the variables to be used for the execution
		$variables = array(array('name' => 'planningVersion', 'value' => $coordinates[0]),array('name' => 'BaseYear', 'value' => $coordinates[1]),array('name' => 'brandCode', 'value' => $coordinates[3]),array('name' => 'accountType', 'value' => $coordinates[4]),array('name' => 'planType', 'value' => $coordinates[5]),array('name' => 'customerType', 'value' => $coordinates[6]),array('name' => 'contractCategory', 'value' => $coordinates[7]),array('name' => 'kpi', 'value' => $coordinates[11]));
		// execute the job
		$result = $server->execute(array('locator' => $locator, 'variables' => $variables));
		
		// update the reconciling element (Contract Category = Freedom HS, Price Point = 40, Contract Term = 24)
		if ($coordinates[12] == 'Contract Category Mix' && $coordinates[5] == 'Handset' && $coordinates[7] != 'Freedom HS')
		{	
			// set the locator to be used for the execution
			switch ($coordinates[12])
		    {
			   case 'Contract Category Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_1';
			    break;
			   case 'Contract Term Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_2';
			    break;
			   case 'Price Point Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_3';
			    break;
			   default: 
			   	break;
		    }
			
			// set the variables to be used for the execution
			$variables = array(array('name' => 'planningVersion', 'value' => $coordinates[0]),array('name' => 'BaseYear', 'value' => $coordinates[1]),array('name' => 'brandCode', 'value' => $coordinates[3]),array('name' => 'accountType', 'value' => $coordinates[4]),array('name' => 'planType', 'value' => $coordinates[5]),array('name' => 'customerType', 'value' => $coordinates[6]),array('name' => 'contractCategory', 'value' => 'Freedom HS'),array('name' => 'kpi', 'value' => $coordinates[11]));
			// execute the job
			$result = $server->execute(array('locator' => $locator, 'variables' => $variables));
		}
		
		if ($coordinates[12] == 'Contract Category Mix' && $coordinates[5] == 'MBB' && $coordinates[7] != 'Freedom MBB')
		{	
			// set the locator to be used for the execution
			switch ($coordinates[12])
		    {
			   case 'Contract Category Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_1';
			    break;
			   case 'Contract Term Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_2';
			    break;
			   case 'Price Point Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_3';
			    break;
			   default: 
			   	break;
		    }
			
			// set the variables to be used for the execution
			$variables = array(array('name' => 'planningVersion', 'value' => $coordinates[0]),array('name' => 'BaseYear', 'value' => $coordinates[1]),array('name' => 'brandCode', 'value' => $coordinates[3]),array('name' => 'accountType', 'value' => $coordinates[4]),array('name' => 'planType', 'value' => $coordinates[5]),array('name' => 'customerType', 'value' => $coordinates[6]),array('name' => 'contractCategory', 'value' => 'Freedom MBB'),array('name' => 'kpi', 'value' => $coordinates[11]));
			// execute the job
			$result = $server->execute(array('locator' => $locator, 'variables' => $variables));
		}
		
		if ($coordinates[12] == 'Contract Category Mix' && $coordinates[5] == 'FBB' && $coordinates[7] != 'Freedom FBB')
		{	
			// set the locator to be used for the execution
			switch ($coordinates[12])
		    {
			   case 'Contract Category Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_1';
			    break;
			   case 'Contract Term Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_2';
			    break;
			   case 'Price Point Mix': 
			   	$locator = 'Population-SVS-new-JM.jobs.cForecastConnectionsVolume_3';
			    break;
			   default: 
			   	break;
		    }
			
			// set the variables to be used for the execution
			$variables = array(array('name' => 'planningVersion', 'value' => $coordinates[0]),array('name' => 'BaseYear', 'value' => $coordinates[1]),array('name' => 'brandCode', 'value' => $coordinates[3]),array('name' => 'accountType', 'value' => $coordinates[4]),array('name' => 'planType', 'value' => $coordinates[5]),array('name' => 'customerType', 'value' => $coordinates[6]),array('name' => 'contractCategory', 'value' => 'Freedom FBB'),array('name' => 'kpi', 'value' => $coordinates[11]));
			// execute the job
			$result = $server->execute(array('locator' => $locator, 'variables' => $variables));
		}
		
	}
}

function FromVolume($database, $cube, $areaid, $sid, $coordinates, $value, $splashMode, $additive)
{
	event_lock_begin($areaid, $sid2);
	sep_log('start to write '.$value.' into '.$cube);
	$user = get_user_for_sid($sid2); // user who made the change
	$groups = get_groups_for_sid($sid2); // groups of the user who made the change
	
	switch($splashMode)
    {
      // no splashing is used
      case 0:
        $value = $value;
        break;
    
      // default splashing is used
      case 1:
        $value = '#'.$value;
        break;
    
      // add splashing is used
      case 2:
        $value = '!!'.$value;
        break;
    
      // set splashing is used
      case 3:
        $value = '!'.$value;
        break;
    }
	
	// write calculated turnover into column 'Turnover' in 'Sales' cube
    $write = palo_setdataa($value, FALSE, "SupervisionServer/$database", $cube, $coordinates);
	event_lock_end();
	
	if ($coordinates[0] != 'Actual')
	{
		$wsdl_url = 'http://127.0.0.1:7775/etlserver/services/ETL-Server?wsdl';
		$server = @new SoapClient($wsdl_url, array('exceptions' => true, 'location' => $wsdl_url));
		
		//attempt OLAP session adoption with sid passed to DrillThrough event
		$login_attempt = $server->login(array('olapSession' => $sid))->return;
		$session = $login_attempt->result;
		
		// check if session is valid, set headers if okay, return if not
		if (!$session){
		sep_log("<< Error during OLAP session adoption! >>");
		} else {
		$header = new SoapHeader('http://ns.jedox.com/ETL-Server/', 'etlsession', $session);   
		$server->__setSoapHeaders($header);
		}
		
		// set the locator to be used for the execution
		$locator = 'Population-SVS-new-JM.jobs.cForecastFromVolume';
		
		/*
		$coordinates[0] -> Version
		$coordinates[1] -> Year
		$coordinates[2] -> Period
		$coordinates[3] -> Brand Code
		$coordinates[4] -> Account Type
		$coordinates[5] -> Plan Type
		$coordinates[6] -> Customer Type
		$coordinates[7] -> Contract Category
		$coordinates[8] -> Contract Term
		$coordinates[9] -> Contract Status Group
		$coordinates[10] -> Price Point
		$coordinates[11] -> KPI
		$coordinates[12] -> Meausre (Plan)
		*/
		// set the variables to be used for the execution - UpgradeFrom
		$variables = array(array('name' => 'planningVersion', 'value' => $coordinates[0]),array('name' => 'BaseYear', 'value' => $coordinates[1]),array('name' => 'period', 'value' => $coordinates[2]),array('name' => 'brandCode', 'value' => $coordinates[3]),array('name' => 'accountType', 'value' => $coordinates[4]),array('name' => 'planType', 'value' => $coordinates[5]),array('name' => 'customerType', 'value' => $coordinates[6]),array('name' => 'contractCategory', 'value' => $coordinates[7]),array('name' => 'contractTerm', 'value' => $coordinates[8]),array('name' => 'contractStatusGroup', 'value' => $coordinates[9]),array('name' => 'pricePoint', 'value' => $coordinates[10]),array('name' => 'kpiFrom', 'value' => 'UpgradeFrom'));
		// execute the job
		$result = $server->execute(array('locator' => $locator, 'variables' => $variables));
		
		// set the variables to be used for the execution - RPCFrom
		$variables = array(array('name' => 'planningVersion', 'value' => $coordinates[0]),array('name' => 'BaseYear', 'value' => $coordinates[1]),array('name' => 'period', 'value' => $coordinates[2]),array('name' => 'brandCode', 'value' => $coordinates[3]),array('name' => 'accountType', 'value' => $coordinates[4]),array('name' => 'planType', 'value' => $coordinates[5]),array('name' => 'customerType', 'value' => $coordinates[6]),array('name' => 'contractCategory', 'value' => $coordinates[7]),array('name' => 'contractTerm', 'value' => $coordinates[8]),array('name' => 'contractStatusGroup', 'value' => $coordinates[9]),array('name' => 'pricePoint', 'value' => $coordinates[10]),array('name' => 'kpiFrom', 'value' => 'RPCFrom'));
		// execute the job
		$result = $server->execute(array('locator' => $locator, 'variables' => $variables));
	}
}

?>