<?php

/**
 * Sample Script for Jedox OLAP Server. Version 6.0.0
 * SVN: $Id: sep.inc.mysql.php 411 2015-09-21 11:44:54Z ddanehl $ 
 */
 
define MYSQL_HOST = 'localhost';
define MYSQL_USER = 'admin';
define MYSQL_PASS = 'bzQ435pt';
 
class SEPEventHandler extends SEPEventHandlerBase {

   public function InitCubeWorker($database, $cube) { // void
      sep_log("<< USING SAMPLE SCRIPT PLEASE ADJUST TO MATCH YOUR CONFIGURATION >>");
      sep_log("<< Get triggering area >>");
      if ($database == "Demo")
      {
            if ($cube == "Sales")
            {
               $slice_to_watch = array(DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,DIMENSION_TOTAL,Array('Units'));

               $this->WatchCubeArea($slice_to_watch, 'insertIntoMysql');
            }
      }
   }
}

function insertIntoMysql($database,$cube,$areaid,$sid,$coordinates,$value) {

   if ($value >= 0) 
   {
      //write the value at the desginated coordiantes
      $write = palo_setdataa($value,'SPLASH_MODE_DEFAULT',"SupervisionServer/$database",$cube,$coordinates);
      
      //inserts value into MySQL
      $link = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
      if (!$link) {
         sep_log('Could not connect: ' . mysql_error());
      }
      
      $sql = 'INSERT INTO facts_table VALUES (' . $value . ')';
      $result = mysql_query($sql);
      if (!$result) {
         sep_log('Could not query:' . mysql_error());
      }
      
      mysql_close($link);
      
   }
   else 
   {
      //Popup-message in Excel if the value is negative
      sep_error("Error","Negative values are not allowed!");
   }
}

?>


<?php


?>