<?php
if (!defined("CNCAT_ENGINE")) die();

    class CNCatDatabase
    {
        var $handler = null;
        
        
        /***************************************
        * Connecting to DB
        ***************************************/
        function CNCatDatabase()
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            
            $dbError=false;
            if ($CNCAT["config"]["wordpress"]||$CNCAT["config"]["drupal"]||$CNCAT["config"]["joomla"])
                $this->handler = @mysql_connect($CNCAT["config"]["db"]["host"],$CNCAT["config"]["db"]["user"],$CNCAT["config"]["db"]["password"], true);
            else
                $this->handler = @mysql_connect($CNCAT["config"]["db"]["host"],$CNCAT["config"]["db"]["user"],$CNCAT["config"]["db"]["password"]);
            if (mysql_errno($this->handler)!=0) 
	            $CNCAT_ENGINE->displayError ("Error connecting database server: ".mysql_error());

	        @mysql_select_db($CNCAT["config"]["db"]["name"], $this->handler);
	        if (mysql_errno($this->handler)!=0) 
                $CNCAT_ENGINE->displayError ("Error connecting database: ".mysql_error());

            $CNCAT["system"]["query_count"] = 0;    
                
            /* Database connection charset */
            if (!empty ($CNCAT["config"]["db"]["charset"]))
            {
                mysql_query("SET NAMES '" . $CNCAT["config"]["db"]["charset"] . "'", $this->handler);
            }
                
            /* Additional SQL instructions */
            if (@is_array($CNCAT["config"]["db"]["sqlinstructions"])) 
                foreach($CNCAT["config"]["db"]["sqlinstructions"] as $sql) 
                    @$this->query($sql, "Additional SQL instructions") or $CNCAT_ENGINE->displayError("Error at:<br>".$sql."<br><br>".mysql_error());

        }            
        
        /***************************************
        * Database functions
        ***************************************/
        
        /**
        * Returns list of table field names for specified template and variable name
        * @param tplName - name of template
        * @param varName - name of variable in the template (for example, "CAT" or "ITEM")
        * @param tableName - table name (useful when extended fields are used, f.e. for item)
        * @return result["int"] - array of internal field names, result["ext"] - external
        */
        function getRecordFieldsForSelect ($tplName, $varName, $tableName="")
        {                    
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            if (!is_array ($CNCAT_ENGINE->tpl->templates[$tplName]["fields"][$varName]))
                return "";
                
            $f=1;
            $result = array();
            $result["int"] = "";
            $result["ext"] = "";
            if (is_array ($CNCAT["config"]["extfields"][$tableName]))
            {
                foreach ($CNCAT_ENGINE->tpl->templates[$tplName]["fields"][$varName] as $fieldName)
                {
                    if (isset ($CNCAT["config"]["extfields"][$tableName][$fieldName]))
                        $result["ext"][] = $fieldName;
                    else
                        $result["int"][] = $fieldName;
                }   
            }
            else
            {
                foreach ($CNCAT_ENGINE->tpl->templates[$tplName]["fields"][$varName] as $fieldName)
                {
                    $result["int"][] = $fieldName;
                }               
            }
            return $result;    
        }
        
        
       
        
        /**
        * Executes mysql_query    
        */
        function query($query, $comment="", $debug = true)
        {
            GLOBAL $CNCAT, $CNCAT_ENGINE;
            $query = ltrim($query);
            if ($CNCAT["system"]["debug"])
            {
                $CNCAT["system"]["debug_query_count"]++;         
                $CNCAT["system"]["debug_db_log"] .= "<b>".$comment."</b><br>";
                $CNCAT["system"]["debug_db_log"] .= $query."<br>";                
                $t1 = cncatGetMicrotime();
            }
            $result = @mysql_query ($query, $this->handler);    
            if ($CNCAT["system"]["debug"] && substr($query, 0, 6) == "SELECT")
            {
                $t2 = cncatGetMicrotime();
                $CNCAT["system"]["debug_query_sumtime"] += ($t2-$t1);
                
                // Try to explain query
                
                if ($res = @mysql_query ("EXPLAIN ".$query, $this->handler)) {
                    while ($row = mysql_fetch_assoc ($res))
                    {
                        $extra = $row["Extra"];
                        
                        if (cn_strpos ($extra, "filesort")!==FALSE || 
                            cn_strpos ($extra, "temp")!==FALSE)
                            $CNCAT["system"]["debug_db_log"] .= "<font color=red>".$extra."</font> ";   
                    };
                }
                
                $CNCAT["system"]["debug_db_log"] .= ($t2-$t1)." (".$CNCAT["system"]["debug_query_sumtime"].")<br><br>";
            }
            return $result;                 
        }
    }
      
?>
