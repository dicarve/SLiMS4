<?php
/**
 * simbio class
 * Simbio Framework
 *
 * Copyright (C) 2009,2010  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

class Simbio {
    const SIMBIO_VERSION = '3.0';
    // Class properties
    private static $framework;
    // main database connection object
    private $dbc = null;
    // last query
    private $last_query = null;
    // an array of global application configuration
    private $config = array();
    // module related properties
    private $currentRequest;
    private $currentModule = 'biblio';
    private $currentMethod = 'index';
    private $currentParam = 0;
    private $enabledModules = array();
    // a registry for already loaded modules or libraries
    private $loadedModules = array();
    private $loadedLibraries = array();
    private $modules = array();
    // locales
    private $locales = array();
    // views registry
    private $views = array();
    // javascript files
    private $javascripts = array();
    // css files
    private $css = array();
    // errors
    private $errors = array();
    // content data
    private $contents = array();
    // page metadata
    private $metadata = array();

    /**
     * Simbio framework class constructor set to private to prevent instance creation
     * framework initialization happen in here
     *
     * @return  void
     */
    private function __construt() {
        // do nothing
    }


    /**
     * Method to prevent object cloning
     *
     * @return  void
     */
    public function __clone() {
        // throw an Exception
        throw new Exception('Simbio framework\'s instance already created! Cannot create another instance!');
    }


    /**
     * Method to add CSS to running framework
     *
     * @param   mixed   $mixed_css : a string or an array of CSS file to include
     * @return  void
     */
    public function addCSS($mixed_css) {
        if (is_string($mixed_css)) {
            $this->css[] = $mixed_css;
        } else if (is_array($mixed_css)) {
            $this->css = array_merge($this->css, $mixed_css);
        }
    }


    /**
     * Method to add error information to framework
     *
     * @param   mixed   $mixed_js : a string or an array of Javascript file to include
     * @return  void
     */
    public function addError($str_error_ID, $str_error_message) {
        $this->errors[$str_error_ID] = array('error_id' => $str_error_ID, 'error_message' => $str_error_message);
    }


    /**
     * Method to add Javascript to running framework
     *
     * @param   mixed   $mixed_js : a string or an array of Javascript file to include
     * @return  void
     */
    public function addJS($mixed_js) {
        if (is_string($mixed_js)) {
            $this->javascripts[] = $mixed_js;
        } else if (is_array($mixed_js)) {
            $this->javascripts = array_merge($this->javascripts, $mixed_js);
        }
    }


    /**
     * Method to add page Metadata
     *
     * @param   string  $str_metadata_name : a metadata field name
     * @param   string  $str_content : a metadata field value
     * @return  void
     */
    public function addMetadata($str_metadata_name, $str_content) {
        if (isset($this->metadata[$str_metadata_name])) {
            $this->metadata[$str_metadata_name] .= ' '.$str_content;
        } else {
            $this->metadata[$str_metadata_name] = $str_content;
        }
    }


    /**
     * Method to check privileges of module
     *
     * @return  boolean     true if have privileges and false if don't have privileges
     */
    private function checkPrivileges() {
        return true;
    }


    /**
     * Static method to create framework instance
     *
     * @param   array   $arr_config: an array containing global application configuration variables
     * @return  object  an instance/object of Simbio framework
     */
    public static function create($arr_config) {
        if (!isset(self::$framework)) {
            $c = __CLASS__;
            self::$framework = new $c();
            // initialize
            self::$framework->init($arr_config);
        } else {
            // throw an Exception
            throw new Exception('Simbio framework\'s instance already created! Cannot create another instance!');
        }

        return self::$framework;
    }


    /**
     * Method to create framework's database connection
     * Simbio framework use MySQLi database library
     *
     * @param   string  $str_connection : an URI specifying connection
     * @return  void
     */
    private function dbConnection($str_connection) {
        // parse connection URI
        $_uri = parse_url($str_connection);
        $_dbname = isset($_uri['path'])?trim(str_ireplace('/', '', $_uri['path'])):'simbiodb';
        $_dbport = isset($_uri['port'])?trim($_uri['port']):3306;
        // we prefer to use mysqli extensions if its available
        if ($_uri['scheme'] == 'mysqli' && extension_loaded('mysqli')) {
            /* MYSQLI */
            $this->dbc = @new mysqli($_uri['host'], $_uri['user'], $_uri['pass'], $_dbname, $_dbport);
            if (mysqli_connect_error()) {
                throw new Exception('Error connecting to database (with error code : '.mysqli_connect_errno().')! '
                    .'Probably wrong username or password for database connection.');
                die();
            }
        } else {
            /* MYSQL */
            // require the simbio mysql class to emulate MySQLi style
            include SIMBIO_BASE.'Databases/Mysql/Mysql.inc.php';
            // make a new connection object that will be used by all applications
            $this->dbc = @new Mysql($_uri['host'], $_uri['user'], $_uri['pass'], $_dbname, $_dbport);
        }
    }


    /**
     * Method easily update data of database table records
     *
     * @param   array   $arr_criteria : an array of table information and SQL criteria
     * @return  array   an array of operation status flag and message
     */
    public function dbDelete($arr_criteria, $str_table) {
        // the delete query
        $this->last_query = "DELETE FROM $str_table WHERE $str_criteria";
        $_delete = $this->dbc->query($this->last_query);
        // if an error occur
        if ($this->dbc->error) { $this->error = $this->dbc->error; return false; }

        return true;
    }


    /**
     * Method easily insert record to database table
     *
     * @param   array   $arr_data : an array containing field => value combination
     * @param   string	$str_table : database table name to be inserted
     * @return  array   an array of operation status flag and message
     */
    public function dbInsert($arr_data, $str_table) {
        if (!is_array($arr_data) OR count($arr_data) == 0) {
            return false;
        }

        // parse the array first
        $_str_columns = '';
        $_str_value = '';
        foreach ($arr_data as $column => $value) {
            // concatenating column name
            $_str_columns .= ", `$column`";
            // concatenating value
            if ($value === 'NULL' OR $value === null) {
                // if the value is NULL or string NULL
                $_str_value .= ', NULL';
            } else if (is_string($value)) {
                if (preg_match("@^literal{.+}@i", $value)) {
                    $value = preg_replace("@literal{|}@i", '', $value);
                    $_str_value .= ", $value";
                } else {
                    // concatenating column value
                    $_str_value .= ", '$value'";
                }
            } else {
                // if the value is an integer or unknown data type
                $_str_value .= ", $value";
            }
        }

        // strip the first comma  of string
        $_str_columns = substr_replace($_str_columns, '', 0, 1);
        $_str_value = substr_replace($_str_value, '', 0, 1);

        // the insert query
        $this->last_query = "INSERT INTO $str_table ($_str_columns) "
            ."VALUES ($_str_value)";
        $_insert = $this->dbc->query($this->last_query);
        // if an error occur
        if ($this->dbc->error) { $this->error = $this->dbc->error; return false; }

        return true;
    }


    /**
     * Method easily update data of database table records
     *
     * @param   array   $arr_data : an array containing field => value combination
     * @param   array   $str_table : database table name to be updated
     * @param   string  $str_criteria : SQL criteria of data to be updated
     * @return  array   an array of operation status flag and message
     */
    public function dbUpdate($arr_data, $str_table, $str_criteria) {
        // check if the first argumen is an array
        if (!is_array($arr_data)) {
            return false;
        } else {
            $_set = '';
            // concat the update query string
            foreach ($arr_data as $column => $new_value) {
                if ($new_value == '') {
                    $_set .= ", $column = ''";
                } else if ($new_value === 'NULL' OR $new_value == null) {
                    $_set .= ", $column = NULL";
                } else if (is_string($new_value)) {
                    if (preg_match("/^literal{.+}/i", $new_value)) {
                        $new_value = preg_replace("/literal{|}/i", '', $new_value);
                        $_set .= ", `$column` = $new_value";
                    } else {
                        $_set .= ", `$column` = '$new_value'";
                    }
                } else {
                    $_set .= ", `$column` = $new_value";
                }
            }

            // strip the first comma
            $_set = substr_replace($_set, '', 0, 1);
        }

        // update query
        $this->last_query = "UPDATE $str_table SET $_set WHERE $str_criteria";
        $_update = $this->dbc->query($this->last_query);
        // if an error occur
        if ($this->dbc->error) { $this->error = $this->dbc->error; return false; }

        return true;
    }


	/**
	 * Invoke query to database
	 * 
	 * @param	string	$str_query : SQL query to be executed
	 * @return	object	mysql statement object
	 */
	public function dbQuery($str_query) {
		return $this->dbc->query(Simbio::rewriteQuery($str_query));
	}
	
	
    /**
     * Method to generate view data
     */
    public function generateView() {

    }


    /**
     * Method to get block content
     *
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public function getBlock($str_block_type) {
        $_block;
        switch ($str_block_type) {
            case 'footer' :
                $_block = isset($this->config['copyright_info'])?$this->config['copyright_info']:'&copy; '.(date('Y')).' Simbio framework.';
                return $_block;
                break;
            case 'language select' :
                $_block = '<form name="lang-select" id="lang-select" action="index.php" method="get">'
                    .'<select name="locale" onchange="document.lang-select.submit();">'
                    .'</select></form>';
                return Utility::createBlock($_block, 'Language', 'language');
                break;
        }
    }


    /**
     * Method to get global application config
     *
     * @return  array   an array of global config variables
     */
    public function getGlobalConfig() {
        return $this->config;
    }


    /**
     * Method to get current framework views data
     *
     * @param   string      $str_view_id: an ID of view data to get
     * @return  string
     */
    public function getViews($str_view_id = null) {
        if ($str_view_id) {
            return $this->views['content'][$str_view_id];
        } else {
            return $this->views;
        }
    }


    /**
     * Method to set enabled module from database
     *
     * @return  void
     */
    public function getEnabledModule() {
        return $this->enabledModules;
    }


    /**
     * Method to get current view data
     *
     * @return  array   an array of view data
     */
    public function getView() {
        return $this->views;
    }


    /**
     * Initialization function
     *
     * @param   array   $arr_config
     * @return  void
     */
    private function init($arr_config) {
        // re-define DIRECTORY_SEPARATOR constant to make it shorter
        define( 'DSEP', DIRECTORY_SEPARATOR );
        // initialization
        // simbio framework base dir
        if (!defined('SIMBIO_BASE')) {
            define( 'SIMBIO_BASE', realpath(dirname(__FILE__)).DSEP );
        }
        // simbio framework web base dir
        if (!defined('SIMBIO_WEB_BASE')) {
            $_simbio_web_base = preg_replace('@modules.*@i', '', dirname($_SERVER['PHP_SELF']));
            define( 'SIMBIO_WEB_BASE', $_simbio_web_base.(preg_match('@\/$@i', $_simbio_web_base)?'':'/') );
        }
        // application base dir
        if (!defined('APP_BASE')) {
            define( 'APP_BASE', SIMBIO_BASE );
        }
        // application web base dir
        if (!defined('APP_WEB_BASE')) {
            define( 'APP_WEB_BASE', SIMBIO_WEB_BASE );
        }
        // application modules & plugins base dir
        if (!defined('MODULES_BASE')) {
            define( 'MODULES_BASE', APP_BASE.'modules'.DSEP );
        }
        // application modules & plugins web base dir
        if (!defined('MODULES_WEB_BASE')) {
            define( 'MODULES_WEB_BASE', APP_WEB_BASE.'modules/' );
        }
        // application UI template base dir
        if (!defined('TEMPLATES_BASE')) {
            define( 'TEMPLATES_BASE', APP_BASE.'templates'.DSEP );
        }
        // application UI template web base dir
        if (!defined('TEMPLATES_WEB_BASE')) {
            define( 'TEMPLATES_WEB_BASE', APP_WEB_BASE.'templates/' );
        }
        // external library base dir
        if (!defined('LIBS_BASE')) {
            define( 'LIBS_BASE', APP_BASE.'libraries'.DSEP );
        }
        // external library web base dir
        if (!defined('LIBS_WEB_BASE')) {
            define( 'LIBS_WEB_BASE', APP_WEB_BASE.'libraries/' );
        }

        /**
         * Default configurations
         *
         */
        $this->config['default_module'] = 'biblio';
        $this->config['db_uri'] = 'mysqli://root:@localhost:3306/simbiodb';
        $this->config['db_prefix'] = 'simbio_';
        $this->config['default_template'] = 'default';
        $this->config['default_admin_template'] = 'default';
        $this->config['locale'] = 'en_US';
        $this->config['show_error'] = true;

        // merge config from external configuration
        $this->config = array_merge($this->config, $arr_config);
        // turn off/on all errors
        @ini_set('display_errors', $this->config['show_error']);
        // check magic quote gpc
        self::setMagicQuotes();
        // create database connection
        self::dbConnection($this->config['db_uri']);
        // get enabled module
        self::setEnabledModule();
    }


    /**
     * Method to load/write all CSS inclusion tags
     *
     * @return  string  CSS inclusion tags
     */
    private function loadCSS() {
        $_js = '';
        foreach ($this->css as $_cssfile) {
            $_js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$_cssfile\" />\n";
        }
        return $_js;
    }


    /**
     * Method to load/write all JS inclusion tags
     *
     * @return  string  Javascript inclusion tags
     */
    private function loadJS() {
        $_js = '';
        foreach ($this->javascripts as $_jsfile) {
            $_js .= "<script type=\"text/javascript\" src=\"$_jsfile\"></script>\n";
        }
        return $_js;
    }


    /**
     * Method to load global application config from database
     *
     * @return  array   an array of global configuration
     */
    private function loadGlobalConfig() {
        $_config_q = $this->dbc->query(Simbio::rewriteQuery('SELECT config_name,config_value FROM {config} LIMIT 200', $this->config['db_prefix']));
        $_configs = array();
        while ($_config_d = $_config_q->fetch_row()) {
            if (in_array($_config_d[0], array('default_module', 'db_uri', 'db_prefix', 'show_error'))) {
                continue;
            }
            $_configs[$_config_d[0]] = @unserialize($_config_d[0]);
        }
        return $_configs;
    }


    /**
     * Method to load application library
     *
     * @param   string  $str_library_name : a library name to load
     * @param   string  $str_path : an optional path to library class file location
     * @return  void
     */
    public function loadLibrary($str_library_name, $str_path = null) {
        if (!isset($this->loadedLibraries[$str_library_name])) {
            if (!class_exists($str_library_name)) {
                $_lib_path = LIBS_BASE.$str_library_name.'.inc.php';
                if ($str_path) {
                    $_lib_path = $str_path;
                }
                if (file_exists($_lib_path)) {
                    require $_lib_path;
                    $this->loadedLibraries[$str_library_name] = $_lib_path;
                }
            }
        }
    }


    /**
     * Method to load page metadata
     *
     * @return  string  string of HTML meta tags
     */
    private function loadMetadata() {
        $_metadata = '';
        foreach ($this->metadata as $_field => $_value) {
            $_metadata .= '<meta name="'.$_field.'" content="'.( strip_tags(trim($_value)) ).'" />'."\n";
        }
        return $_metadata;
    }


    /**
     * Method to load module class
     *
     * @param   string  $str_module_name : module name to load
     * @param   string  $str_path : an optional path to library class file location
     * @return  void
     */
    public function loadModule($str_module_name, $str_path = null) {
        if (!isset($this->loadedModules[$str_module_name])) {
            if (!class_exists($str_module_name)) {
                $_module_path = MODULES_BASE.$str_module_name.DSEP.$str_module_name.'.inc.php';
                if ($str_path) {
                    $_module_path = $str_path;
                }
                if (file_exists($_module_path)) {
                    require $_module_path;
                    $this->loadedModules[$str_module_name] = $_module_path;
                }
            }
        }
    }


    /**
     * Method to add view data to main framework view
     *
     * @param   mixed   $mixed_view_data: a view data to add
     * @param   string  $str_view_id: id of view data
     */
    public function loadView($mixed_view_data, $str_view_id) {
        $this->views[$str_view_id] = $mixed_view_data;
    }


    /**
     * Method to load current template config file
     *
     * @return  array
     */
    private function loadTemplateConfig() {
        $_file = TEMPLATES_BASE.$this->config['default_template'].DSEP.'tconf.ini';
        if (file_exists($_file)) {
            $_tconf = parse_ini_file($_file, true);
            return $_tconf;
        } else {
            return null;
        }
    }


    /**
     * Copyright (C) 2009  Tobias Zeumer (github@verweisungsform.de)
     * Method to load localization settings and load php-gettext library
     *
     * @return  void
     */
    private function localize() {
        // load php-gettext library
        $this->loadLibrary('php-gettext', LIBS_BASE.'php-gettext'.DSEP.'gettext.inc.php');
        // get available locales
        $_locales_conf = LIBS_BASE.'locales'.DSEP.'locale.inc.php';
        if (file_exists($_locales_conf)) {
			require LIBS_BASE.'locales'.DSEP.'locale.inc.php';
			if (isset($locales)) {
				$this->locales = $locales;
			}
		}

        // gettext setup
        $_locale = $this->config['locale'];
        $_domain = 'messages';
        $_encoding = 'UTF-8';

        // set language to use
        T_setlocale(LC_ALL, $_locale);
        // set locales dictionary location
        _bindtextdomain($_domain, LIBS_BASE.'locale');
        // codeset
        _bind_textdomain_codeset($_domain, $_encoding);
        // set .mo filename to use
        _textdomain($_domain);
    }


    /**
     * Framework main routine
     * this is the main framework action
     */
    public function main() {
        // start session
        session_start();
        // parse module request and router
        $this->parseRequest();
        // load global config
        $this->config = array_merge($this->config, $this->loadGlobalConfig());
        // localization
        $this->localize();
        // check privileges
        if (!$this->checkPrivileges()) {
            $this->addError('NO_PRIVILEGES', __('You don\'t have enough privileges to enter this section!'));
            $this->makeFinalOutput();
        }
        // load Simbio Module Skeleton libraries so other other modules can inherit it
        $this->loadLibrary('SimbioModel', SIMBIO_BASE.'SimbioModel.inc.php');
        // load enabled libraries so it is available for all loaded module
        $this->loadLibrary('Utility', SIMBIO_BASE.'Utils'.DSEP.'Utility.inc.php');
        // load enabled modules
        foreach ($this->enabledModules as $_name => $_module) {
            $this->loadModule($_name);
        }

        // load Simbio's default Javascript libraries
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/jquery.js';
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/ajax.js';
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/gui.js';
        // load Simbio's default CSS libraries
        $this->css[] = SIMBIO_WEB_BASE.'UI/CSS/core.style.css';

        // create instance of all enabled module
        foreach ($this->enabledModules as $_module_name => $_module) {
            $_module_name = strtolower($_module_name);
            $this->modules[strtolower($_module_name)] = new $_module_name();
            // call initialization method for each module
            $this->modules[$_module_name]->init($this, $this->currentParam);
        }

        // run module validate method
        foreach ($this->modules as $_module_obj) {
            $_validated = $_module_obj->validate($this);
            if ($_validated !== true) {
                $this->addError($_validated['error_ID'], $_validated['error_message']);
            }
        }

        // run current module method
        $_cmodule = trim(strtolower($this->currentModule));
        $_cmethod = trim($this->currentMethod);
        if ($_cmodule) {
            if (method_exists($this->modules[$_cmodule], $_cmethod)) {
                $this->modules[$_cmodule]->$_cmethod($this, $this->currentParam);
            }
        }

        // make the final output
        $this->makeFinalOutput();
    }


    /**
     * Method to make the final application output
     *
     * @return  void
     */
    private function makeFinalOutput() {
        // check content type
        // set header
        if (isset($this->views['content_type'])) {
            header('Content-type: '.$this->views['content_type']);
        } else {
            header('Content-type: text/html');
        }

        /**
         * Core template variables initialization
         *
         */
        // metadata to place on page head
        $metadata = '';
        // css files
        $css = '';
        // javascript link files
        $javascripts = '';
        // language code
        $language_code = $this->config['locale'];
        // read text direction
        $text_direction = 'ltr';
        // page title
        $page_title = $this->config['app_title'];
        // page charset
        $page_charset = 'utf-8';
        // shortcut icon
        $webicon = 'webicon.png';
        // logo
        $_logo = isset($this->config['logo'])?isset($this->config['logo']):TEMPLATES_WEB_BASE.$this->config['default_template'].'/logo.png';
        $app_logo = '<a href="index.php" id="logo-link"><img src="'.$_logo.'" border="0" id="logo-link" /></a>';
        // application main title
        $app_title = '<a href="index.php" id="app-title-link">'.$this->config['app_title'].'</a>';
        // application subtitle
        $app_subtitle = $this->config['app_subtitle'];
        // main content
        $main_content = '';

        // load template config file
        $_tconf = $this->loadTemplateConfig();
        if ($_tconf) {
            if (isset($_tconf['css']) && $_tconf['css']) {
                foreach($_tconf['css']['cssfile'] as $_cssfile) {
                    $this->css[] = TEMPLATES_WEB_BASE.$this->config['default_template'].'/'.$_cssfile;
                }
            }
            if (isset($_tconf['js']) && $_tconf['js']) {
                foreach($_tconf['js']['jsfile'] as $_jsfile) {
                    $this->javascripts[] = TEMPLATES_WEB_BASE.$this->config['default_template'].'/'.$_jsfile;
                }
            }
            if (isset($_tconf['text_direction'])) {
                $text_direction = $_tconf['text_direction'];
            }
            if (isset($_tconf['page_charset'])) {
                $page_charset = $_tconf['page_charset'];
            }
        }

        // load CSS and javascript links
        $css = $this->loadCSS();
        $javascripts = $this->loadJS();
        $metadata = $this->loadMetadata();

        // check load type
        if (!isset($this->views['type']) && $this->views['load_type'] != 'ajax') {
            // load the template
            require TEMPLATES_BASE.$this->config['default_template'].DSEP.'index_template.inc.php';
            exit(0);
        } else {
            // only output main content
            echo $main_content;
            exit(0);
        }
    }


    /**
     * Method to set enabled module from database
     *
     * @return  void
     */
    private function setEnabledModule() {
        $_sql = 'SELECT module_id, module_name, module_desc FROM {modules} WHERE enabled=1';
        $_arr_modules = array();
        $_module_q = $this->dbc->query(Simbio::rewriteQuery($_sql, $this->config['db_prefix']));
        while ($_module_d = $_module_q->fetch_assoc()) {
            $this->enabledModules[$_module_d['module_name']] = $_module_d;
        }
    }


    /**
     * Private method to check magic_quotes_gpc
     * this method make sure that magic_quotes_gpc is turned off
     *
     * @return  void
     */
    private function setMagicQuotes() {
        // be sure that magic quote is off
        @ini_set('magic_quotes_gpc', false);
        @ini_set('magic_quotes_runtime', false);
        @ini_set('magic_quotes_sybase', false);
        // force disabling magic quotes
        if (get_magic_quotes_gpc()) {
            function stripslashes_deep($value)
            {
                $value = is_array($value)?array_map('stripslashes_deep', $value):stripslashes($value);
                return $value;
            }

            $_POST = array_map('stripslashes_deep', $_POST);
            $_GET = array_map('stripslashes_deep', $_GET);
            $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
            $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        }
    }


    /**
     * Method to parse module request
     *
     * @return  void
     */
    private function parseRequest() {
        $this->currentRequest = trim(preg_replace('@^.{0,}index\.php@i', '', $_SERVER['REQUEST_URI']));
        // explode string by slash
        $_request = explode('/', $this->currentRequest, 5);
        // main module to load
        $this->currentModule = isset($_request[0])?trim(str_ireplace('/', '', $_request[0])):$this->config['default_module'];
        // main method to call
        $this->currentMethod = isset($_request[1])?trim(str_ireplace('/', '', $_request[1])):'index';
        // remaining are method params
        foreach ($_request as $_id => $_request_val) {
            if ($_id > 1 && isset($_request[$_id])) {
                $_request_val = trim(str_ireplace('/', '', $_request_val));
                $this->currentParam .= $_request_val.'/';
            }
        }
        // strip the last slash of param
        $this->currentParam = preg_replace('@\/$@i', '', $this->currentParam);
    }


    /**
     * Static method to rewrite SQL query
     * this method rewrite {tablename} to real table name prefixed with $str_table_prefix
     *
     * @param   string  $str_SQL : the SQL string to rewrite
     * @param   string  $str_table_prefix : prefix of database tables
     * @return  string  rewritten SQL string
     */
    public static function rewriteQuery($str_SQL, $str_table_prefix = '') {
        $_matches = array();
        // rewrite table name
        preg_match_all('@\{[a-z0-9_-]+\}@i', $str_SQL, $_matches);
        if ($_matches) {
            foreach ($_matches[0] as $_match) {
                $_table = str_ireplace(array('{', '}'), '', $_match);
                $str_SQL = str_ireplace($_match, $str_table_prefix.$_table, $str_SQL);
            }
        }
        return $str_SQL;
    }


    /**
     * Method to write application activity logs
     *
     * @param       string      $str_module_name = name of module
     * @param       string      $str_log_msg = log messages
     * @param       integer     $int_operation_constant = simbio operation constant
     * @return      void
     */
    public function writeLogs($str_module_name, $str_log_msg, $int_operation_constant = 0)
    {
        $_str_sql = 'INSERT INTO {syslogs} ';
        $_str_sql .= '(log_flag, log_module, log_operator_id, log_message)';
        $_str_sql .= ' VALUES ';
        $_str_sql .= '('.$int_operation_constant.', \''.trim(strtolower($str_module_name)).'\',';
        $_str_sql .= $_SESSION['userID'].', \''.trim(strip_tags(strtolower($str_log_msg))).'\')';
        $this->dbc->query(self::rewriteQuery($_str_sql));
    }
}
?>
