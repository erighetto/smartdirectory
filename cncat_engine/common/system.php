<?php
/*******************************************************************************
 * CNCat 4.4 
 * Copyright (c) "CN-Software" Ltd. 
 * http://www.cn-software.com/cncat/
 * ----------------------------------------------------------------------------
 * Please do not modify this header!
 *
 * If you change the original code, we do not guarantee the correct functioning
 * of the program and correct updates.
 * See full text of license agreement in cncat-license.txt file located at the 
 * root folder of the web directory.
*******************************************************************************/

if (!defined("CNCAT_ENGINE")) die();
        
    /**
    *   Loads class and creates object. 
    *   First loads class from engine, than from product than from config directory.
    *   Class name must be: "<classPrefix>" for engine, "<classPrefix>Product" for product and "<classPrefix>User" for user-defined
    *   Hierarchy: engine class <- product class <- user class
    *   Engine class should exist.
    *   If product class not exists, user class can't be created and engine class is used
    *   If user class not exists, product or engine class will be used
    *   @param fileName file name of class declaration (without extension)
    *   @param classPrefix classPrefix
    *   @return created object
    */
    function cncatCreateObject($fileName, $classPrefix)
    {
        GLOBAL $CNCAT;

        require_once $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_engine_classes"].$fileName.".php";

        $fnProduct = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_product_classes"].$fileName.".php";
        if (!file_exists($fnProduct)) {
            //return eval ("return new ".$classPrefix."();");
            return new $classPrefix();
        }

        require_once $fnProduct;
        $fnUser = $CNCAT["system"]["dir_root"].$CNCAT["system"]["dir_config_classes"].$fileName.".php";
        
        if (!file_exists($fnUser)) {
            //return eval ("return new ".$classPrefix."Product();");
            $className = $classPrefix . "Product";
            return new $className();
        }

        require_once $fnUser;

        //return eval ("return new ".$classPrefix."User();");
        $className = $classPrefix . "User";
        return $className();
    }

    function cncatStripslashes($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = cncatStripslashes($v);
                } else {
                    $data[$k] = stripslashes($v);
                }
            }
        } else {
            $data = stripslashes($data);
        }

        return $data;
    }
    
    /**
    *   Returns current time in milliseconds
    */
    function cncatGetMicrotime() 
    {
	    list($usec, $sec) = explode(' ', microtime());
	    return (float) $sec + ((float) $usec);
    }    

    function cncatShowRenderStats()
    {

    	GLOBAL $CNCAT;
		$CNCAT["system"]["debug_time_stop"] = cncatGetMicrotime();
		
		$ft = $CNCAT["system"]["debug_time_stop"]-$CNCAT["system"]["debug_time_start"];
		$et = $CNCAT["system"]["debug_time_engine_init_stop"]-$CNCAT["system"]["debug_time_engine_init_start"];
		$qt = $CNCAT["system"]["debug_query_sumtime"];
	    $renderTime = ($ft-$qt-$et);
		?>
		<table>
		<tr>
			<td>FPS:</td><td><?php echo (1/$ft)?></td>
		</tr>
		<tr>
	    	<td>Full time:</td><td><?php echo $ft?></td>
	    </tr>
	    <tr>
	    	<td>Engine init time:</td><td><?php echo $et?> (<?php echo (1/$et) ?> FPS)</td>
	    </tr>
	    <tr>
	    	<td>DB time:</td><td><?php echo $qt?> (<?php echo (1/$qt)?> FPS)</td>
	    </tr>
	    <tr>
	    	<td>Render time:</td><td><?php echo $renderTime?> (<?php echo (1/$renderTime)?> FPS)</td>
	    </tr>
	    </table>
	    <br>
	    
	    <h3>Query count: <?php echo $CNCAT["system"]["debug_query_count"]?></h3>
	    <?php echo $CNCAT["system"]["debug_db_log"];?>
	    
	    <h3>Templates used: <?php echo count($CNCAT["system"]["debug_templates_result"]) ?></h3>
	    <table>	    
			<tr>
				<td>Name</td>
				<td>Count</td>
				<td>Sum time</td>
				<td>Avg time</td>
			</tr>
			<?php
				$resTime = 0;
				$sumCount = 0;
				foreach ((array)$CNCAT["system"]["debug_templates_result"] as $name=>$res)
				{
					$sumTime += $res["time"];
					$sumCount += $res["count"];
					?>
						<tr>
							<td><?php echo $name?></td>
							<td><?php echo $res["count"]?></td>
							<td><?php printf ("%f", $res["time"])?></td>
							<td><?php printf ("%f", $res["time"]/$res["count"])?></td>
						</tr>
					<?php
				}
			?>
			<tr>
				<td><b>Summary</b></td>
				<td><?php echo $sumCount?></td>
				<td><?php printf ("%f", $sumTime)?></td>
				<td><?php printf ("%f", $sumCount != 0 ? ($sumTime / $sumCount) : 0)?></td>
			</tr>
		
		</table>
		<?php
		
	}

?>
