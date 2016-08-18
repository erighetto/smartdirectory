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
//TODO DELETE THIS FROM DISTRIB
class CNCatPlugin {
    var $_cncat;
    var $_cncat_engine;

    var $name;
    var $adminDir;

    function CNCatPlugin($name) {
        $this->_cncat =& $GLOBALS["CNCAT"];
        $this->_cncat_engine =& $GLOBALS["CNCAT_ENGINE"];

        $this->name = $name;
        $this->adminDir = $this->_cncat['dir_root']
            . $this->_cncat['system']['dir_admin_plugins'] . $this->name;

        if (!is_dir($this->adminDir)) {
            trigger_error('Plugin "' . $this->name . '" not found');
        }

        $this->loadConfig();
        $this->loadLang();
    }

    function loadConfig() {
        // Load default config
        require_once @$this->dir . '/config.default.php';

        // Load custom config
        $config_file = $this->dir . '/config.php';

        if (file_exists($config_file)) {
            require_once $config_file;
        }
    }

    function loadLang() {
    }

    function checkInstall() {
        $query = 'SHOW TABLES LIKE \'' . $this->_cncat['config']['db']['prefix'] . 'pages\'';
        $result = $this->_cncat_engine->db->query($query) or $this->_cncat_engine->displayErrorDB(mysql_error());

        if (!mysql_num_rows($result)) {
            return false;
        }
        return true;
    }

    function install() {
        $install_queries = array(
            "
                CREATE TABLE `" . $CNCAT['config']['db']['prefix'] . "pages` (
                    `id` int(11) NOT NULL auto_increment,
                    `title` varchar(255) NOT NULL default '',
                    `name` varchar(255) NOT NULL default '',
                    `contents` text NOT NULL,
                    PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );

        foreach ($install_queries as $query) {
            $this->_cncat_engine->db->query($query) or $this->_cncat_engine->displayErrorDB(mysql_error());
        }
    }

    function config($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    function lang($key) {
        return isset($this->lang[$key]) ? $this->lang[$key] : null;
    }
}
