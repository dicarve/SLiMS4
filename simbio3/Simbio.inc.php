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
    private $lastQuery = null;
    // an array of global application configuration
    private $config = array();
    // module related properties
    private $currentRequest;
    private $currentModule = 'biblio';
    private $currentMethod = 'index';
    private $currentParam = '';
    private $enabledModules = array();
    // a registry for already loaded modules or libraries
    private $loadedModules = array();
    private $loadedLibraries = array();
    private $modules = array();
    // locales
    private $locales = array();
    // views registry
    private $views = array();
    // views configuration
    private $viewsConfig = array();
    // javascript files
    private $javascripts = array();
    // css files
    private $css = array();
    // errors
    private $errors = array();
    // info
    private $infos = array();
    // main menu
    private $mainMenu = array();
    // navigation menu
    private $navigationMenu = array();
    // page metadata
    private $metadata = array();
    // header box
    public $headerBlockTitle = '';
    public $headerBlockMenu = array();
    public $headerBlockForm = null;

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
     * @param   string  $str_error_code: error code
     * @param   string  $str_error_message: error message
     * @return  void
     */
    public function addError($str_error_code, $str_error_message) {
        $this->errors[$str_error_code] = array('code' => $str_error_code, 'message' => $str_error_message);
    }


    /**
     * Method to add information to framework
     *
     * @param   string  $str_info_code: info code
     * @param   string  $str_info_message: info message
     * @return  void
     */
    public function addInfo($str_info_code, $str_info_message) {
        $this->infos[$str_info_code] = array('code' => $str_info_code, 'message' => $str_info_message);
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
     * @param   string  $str_metadata_name: a metadata field name
     * @param   string  $str_content: a metadata field value
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
     * Method to add main menu item
     *
     * @param   array   $arr_menu_item: menu item array
     * @return  void
     */
    public function addMainMenu($arr_menu_item) {
        $this->mainMenu[] = $arr_menu_item;
    }


    /**
     * Method to add navigation menu item
     *
     * @param   string  $str_section: navigation menu section to add
     * @param   array   $arr_menu_item: menu item array
     * @return  void
     */
    public function addNavigationMenu($str_section, $arr_menu_item) {
        $this->navigationMenu[$str_section][] = $arr_menu_item;
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
            /* Simbio MYSQL */
            // require the simbio mysql class to emulate MySQLi style
            include SIMBIO_BASE.'Databases/Mysql/Mysql.inc.php';
            // make a new connection object that will be used by all applications
            $this->dbc = @new Mysql($_uri['host'], $_uri['user'], $_uri['pass'], $_dbname, $_dbport);
        }
        // Force UTF-8 for MySQL connection and HTTP header
        header('Content-type: text/html; charset=UTF-8');
        $this->dbc->query('SET NAMES \'utf8\'');
    }


    /**
     * Method easily update data of database table records
     *
     * @param   string  $str_criteria : string of SQL criteria
     * @return  array   an array of operation status flag and message
     */
    public function dbDelete($str_criteria, $str_table) {
        // the delete query
        $_q = "DELETE FROM {".$str_table."} WHERE $str_criteria";
        $_delete = $this->dbQuery($_q);
        // if an error occur
        if ($this->dbc->errno) {
            $this->addError('DB_DELETE_FAILED', 'Error deleting data from table '.$str_table.' with query: '.$this->lastQuery);
            return false;
        }

        return true;
    }


    /**
     * Method easily insert record to database table
     *
     * @param   array   $arr_data : an array containing field => value combination
     * @param   string  $str_table : database table name to be inserted
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
            if ($value == 'NULL' || $value === null) {
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
        $_q = "INSERT INTO {".$str_table."} ($_str_columns) VALUES ($_str_value)";
        $_insert = $this->dbQuery($_q);
        // if an error occur
        if ($this->dbc->errno) {
            $this->addError('DB_INSERT_FAILED', 'Error inserting data to table '.$str_table.' with query: '.$this->lastQuery.'. MySQL server said: '.$this->dbc->error);
            return false;
        }

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
        $_q = "UPDATE {".$str_table."} SET $_set WHERE $str_criteria";
        $_update = $this->dbQuery($_q);
        // if an error occur
        if ($this->dbc->errno) {
            $this->addError('DB_UPDATE_FAILED', 'Error updating data of table '.$str_table.' with query: '.$this->lastQuery);
            return false;
        }

        return true;
    }


    /**
     * Get last error information of last database query
     *
     * @return  array   an array of database error information
     */
    public function dbError() {
        return array('errno' => $this->dbc->errno, 'error' => $this->dbc->error);
    }


    /**
     * Invoke query to database
     *
     * @param   string  $str_query : SQL query to be executed
     * @return  object  mysql statement object
     */
    public function dbQuery($str_query) {
        $this->lastQuery = Simbio::rewriteQuery($str_query, $this->config['db_prefix']);
        return $this->dbc->query($this->lastQuery);
    }


    /**
     * Filterize SQL string
     *
     * @param   string  $str_sql_data: SQL value to be filterize
     * @param   boolean $bool_strip_html: wether to strip HTML tags or not
     * @return  string  filterized string
     */
    public function filterizeSQLString($str_sql_data, $bool_strip_html = false) {
        $_data = $this->dbc->escape_string(trim($str_sql_data));
        $_data = $bool_strip_html?strip_tags($_data):$_data;
        return $_data;
    }


    /**
     * Method to generate view data
     */
    private function generateView() {
        $_view_output = '';
        foreach ($this->views as $_vid => $_view) {
            if (is_string($_view)) {
                $_view_output .= $_view;
            } else if ($_view instanceof FormOutput) {
                $_view_output .= $_view->build();
            } else if ($_view instanceof Datagrid) {
                $_view_output .= $_view->build();
            } else if ($_view instanceof Listing) {
                $_view_output .= $_view->build();
            } else if ($_view instanceof Table) {
                $_view_output .= $_view->printTable();
            } else {
                $_view_output .= (string)$_view;
            }
        }
        return $_view_output;
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
                    .'<select name="locale" onchange="document.lang-select.submit();">';
                foreach ($this->locales as $_locale) {
                    $_block .= '<option value="'.$_locale[0].'">'.$_locale[1].'</option>';
                }
                $_block .= '</select></form>';
                return Utility::createBlock($_block, 'Language', 'language');
                break;
        }
    }


    /**
     * Method to get database connection object
     *
     * @return  object  database connection object created by framework
     */
    public function getDBC() {
        return $this->dbc;
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
     * Generate header block containing menu and quick search
     *
     * @param   string  $str_block_name: name of block
     * @param   string  $str_block_title: title of block
     * @return  string
     */
    private function headerBlock() {
        if ($this->headerBlockMenu || $this->headerBlockForm) {
            $str_block_name = strtolower(str_replace(' ', '-', $this->headerBlockTitle));

            $_block = '';
            // menu
            if ($this->headerBlockMenu) {
                $_block .= '<div class="header-menu">';
                foreach ($this->headerBlockMenu as $_menu) {
                    $_block .= '<a href="index.php?p='.$_menu['link'].'"'.( isset($_menu['class'])?' class="'.$_menu['class'].'"':'' ).' title="'.$_menu['desc'].'">'.$_menu['title'].'</a>';
                }
                $_block .= '</div>';
            }
            // quick search
            if ($this->headerBlockForm instanceof FormOutput) {
                $_block .= $this->headerBlockForm->buildSimple();
            }

            return '<div class="header-block" id="'.$str_block_name.'">'.Utility::createBlock($_block, $this->headerBlockTitle).'</div>'."\n";
        }
        return '';
    }


    /**
     * Method to get current framework views data
     *
     * @param   string      $str_view_id: an ID of view data to get
     * @return  mixed
     */
    public function getViews($str_view_id = null) {
        if ($str_view_id) {
            if (isset($this->views[$str_view_id])) {
                return $this->views[$str_view_id];
            }
            return '';
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
     * Method to get all module objects
     *
     * @param   object  $obj_module_vars: a reference variable which will contain module objects
     * @param   string  $str_module_name: module name to get
     * @return  void
     */
    public function getModules($str_module_name = null) {
        if ($str_module_name) {
            return $this->modules[$str_module_name];
        } else {
            return $this->modules;
        }
    }


    /**
     * Initialization function
     *
     * @param   array   $arr_config
     * @return  void
     */
    private function init($arr_config) {
        // set error handler
        set_error_handler(array($this, 'phpErrorHandler'));
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
        // application base dir
        if (!defined('APP_VERSION')) {
            define( 'APP_VERSION', Simbio::SIMBIO_VERSION );
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
        // session cookie name
        if (!defined('APP_SESSION_COOKIE_NAME')) {
            define( 'APP_SESSION_COOKIE_NAME', 'simbio' );
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
        $this->config['show_error'] = false;

        // merge config from external configuration
        $this->config = array_merge($this->config, $arr_config);

        $this->viewsConfig['load_type'] = 'full';
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
        $_config_q = $this->dbc->query(Simbio::rewriteQuery('SELECT config_name,config_value FROM {config} WHERE (config_scope IS NULL OR config_scope=\'GLOBAL\') LIMIT 200', $this->config['db_prefix']));
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
                require $_lib_path;
                $this->loadedLibraries[$str_library_name] = $_lib_path;
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
                require $_module_path;
                $this->loadedModules[$str_module_name] = $_module_path;
            }
        }
    }


    /**
     * Method to add view data to main framework view
     *
     * @param   mixed   $mixed_view_data: a view data to add
     * @param   string  $str_view_id: id of view data
     * @return  void
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
        // set session parameters
        // always use session cookies
        @ini_set('session.use_cookies', true);
        // use more secure session ids
        @ini_set('session.hash_function', 1);
        // some pages (e.g. stylesheet) may be cached on clients, but not in shared proxy servers
        @session_cache_limiter('private');
        // set session name and start the session
        @session_name(APP_SESSION_COOKIE_NAME);
        // set session cookies params
        @session_set_cookie_params(86400, APP_WEB_BASE);
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
        // load Simbio Model libraries so other modules can inherit it
        $this->loadLibrary('SimbioModel', SIMBIO_BASE.'SimbioModel.inc.php');
        // load enabled libraries so it is available for all loaded module
        $this->loadLibrary('Utility', SIMBIO_BASE.'Utils'.DSEP.'Utility.inc.php');
        $this->loadLibrary('SQLgrid', SIMBIO_BASE.'Databases'.DSEP.'SQLgrid.inc.php');
        $this->loadLibrary('Listing', SIMBIO_BASE.'UI'.DSEP.'Listing.inc.php');
        $this->loadLibrary('Table', SIMBIO_BASE.'UI'.DSEP.'Table'.DSEP.'Table.inc.php');
        $this->loadLibrary('FormOutput', SIMBIO_BASE.'UI'.DSEP.'Form'.DSEP.'FormOutput.inc.php');
        $this->loadLibrary('Paging', SIMBIO_BASE.'UI'.DSEP.'Paging.inc.php');
        // load enabled modules
        foreach ($this->enabledModules as $_name => $_module) {
            $this->loadModule($_name);
        }

        // load Simbio's default Javascript libraries
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/jquery.js';
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/ajax.js';
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/gui.js';
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/jquery.textarearesizer.js';
        $this->javascripts[] = SIMBIO_WEB_BASE.'UI/Javascripts/jquery.date_input.js';
        // load Simbio's default CSS libraries
        $this->css[] = SIMBIO_WEB_BASE.'UI/CSS/core.style.css';
        $this->css[] = SIMBIO_WEB_BASE.'UI/Javascripts/date_input.css';
        $this->css[] = SIMBIO_WEB_BASE.'UI/Javascripts/textarearesizer.css';

        // create instance of all enabled module
        foreach ($this->enabledModules as $_module_name => $_module) {
            $_module_name = trim(strtolower($_module_name));
            $this->modules[$_module_name] = new $_module_name($this);
        }

        // run module init method
        foreach ($this->modules as $_module_obj) {
            // call initialization method for each module
            $_module_obj->init($this, $this->currentModule, $this->currentMethod, $this->currentParam);
        }

        // run module validate method
        foreach ($this->modules as $_module_obj) {
            $_validated = $_module_obj->validate($this, $this->currentModule, $this->currentMethod, $this->currentParam);
            if ($_validated !== true) {
                $this->addError($_validated['error_code'], $_validated['error_message']);
                $this->makeFinalOutput();
            }
        }

        // run current module method
        $_cmodule = trim(strtolower($this->currentModule));
        $_cmethod = trim($this->currentMethod);
        if (isset($this->modules[$_cmodule])) {
            if (method_exists($this->modules[$_cmodule], 'reRoute')) {
                $this->modules[$_cmodule]->reRoute($this, $_cmethod, $this->currentParam);
            } else if (method_exists($this->modules[$_cmodule], $_cmethod)) {
                $this->modules[$_cmodule]->$_cmethod($this, $this->currentParam);
            }
        }

        // run module manipulate method
        foreach ($this->modules as $_module_obj) {
            $_module_obj->manipulate($this, $this->currentModule, $this->currentMethod, $this->currentParam);
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
        $page_title = $this->config['app_title'].( isset($this->viewsConfig['Page Title'])?' - '.$this->viewsConfig['Page Title']:'' );
        // page charset
        $page_charset = 'utf-8';
        // shortcut icon
        $webicon = 'webicon.png';
        // logo
        $_logo = isset($this->config['logo'])?isset($this->config['logo']):TEMPLATES_WEB_BASE.$this->config['default_template'].'/logo.png';
        $app_logo = '<a href="'.APP_WEB_BASE.'" id="logo-link"><img src="'.$_logo.'" border="0" id="logo" /></a>';
        // application main title
        $app_title = '<a href="'.APP_WEB_BASE.'" id="app-title-link"><span>'.$this->config['app_title'].'</span></a>';
        // application subtitle
        $app_subtitle = $this->config['app_subtitle'];
        // main application information box
        $main_info = '';
        // main content
        $main_content = $this->headerBlock();
        // main menu links
        $primary_links = '';
        // navigation links
        $navigation_links = '';
        // closure
        $closure = isset($this->views['CLOSURE'])?$this->views['CLOSURE']:'';

        // load template config file
        $_tconf = $this->loadTemplateConfig();
        if ($_tconf) {
            if (isset($_tconf['css']['cssfile']) && $_tconf['css']) {
                foreach($_tconf['css']['cssfile'] as $_cssfile) {
                    $this->css[] = TEMPLATES_WEB_BASE.$this->config['default_template'].'/'.$_cssfile;
                }
            }
            if (isset($_tconf['js']['jsfile']) && $_tconf['js']) {
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


        // check content type
        // set header
        if (isset($this->viewsConfig['content_type'])) {
            header('Content-type: '.$this->viewsConfig['content_type']);
        } else {
            header('Content-type: text/html');
            // show all error and infofmation
            if ($this->errors || $this->infos) {
                $main_info = '<div class="info" id="info-box">';
                $main_info .= '<ul class="message-list">';
                // show error
                if ($this->errors) {
                    foreach ($this->errors as $_error) {
                        $main_info .= '<li class="error">'.$_error['message'].'</li>';
                    }
                }
                // show information
                if ($this->infos) {
                    foreach ($this->infos as $_info) {
                        $main_info .= '<li class="info">'.$_info['message'].'</li>';
                    }
                }
                $main_info .= '</ul>';
                $main_info .= '</div>'."\n";
            }
        }

        // load type change when requested with AJAX
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
            if ($_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest') {
                $this->viewsConfig['load_type'] = 'notemplate';
            }
        }

        // check load type
        if ($this->viewsConfig['load_type'] != 'notemplate') {
            $_template = $this->config['default_template'];
            $_template_file = 'index_template.inc.php';
            if (strtolower($this->currentModule) == 'admin') {
                $_template = $this->config['admin_template'];
                // set admin template file
                if (isset($_tconf['admin']['template'])) {
                    $_template_file = 'admin_template.inc.php';
                }
                if (isset($_tconf['admin']['cssfile'])) {
                    foreach($_tconf['admin']['cssfile'] as $_admin_cssfile) {
                        $this->css[] = TEMPLATES_WEB_BASE.$_template.'/'.$_admin_cssfile;
                    }
                }
                if (isset($_tconf['admin']['jsfile'])) {
                    foreach($_tconf['admin']['jsfile'] as $_admin_jsfile) {
                        $this->javascripts[] = TEMPLATES_WEB_BASE.$_template.'/'.$_admin_jsfile;
                    }
                }
            }

            // load CSS and javascript links
            $css = $this->loadCSS();
            $javascripts = $this->loadJS();
            $metadata = $this->loadMetadata();
            $main_content .= $this->generateView();

            // generate main menu
            $primary_links = '<ul id="primary-links">'."\n";
            // add admin homepage link at the beginning of array menu
            array_unshift($this->mainMenu, array('link' => 'admin', 'description' => 'Administration homepage', 'name' => 'Admin home'));
            // add logout link at end
            $this->mainMenu[] = array('link' => 'user/logout', 'description' => 'Quit safely from application', 'name' => 'LOGOUT');
            if ($this->mainMenu) {
                foreach ($this->mainMenu as $_menu) {
                    $_menu_class = str_replace(array(' '), '-', strtolower($_menu['name']));
                    if (stripos($this->currentRequest, $_menu['link'], 0) !== false && $_menu['link'] != 'admin') {
                        $_menu_class .= ' active';
                    } else if ($_menu['link'] == 'admin' && $this->currentRequest == 'admin') {
                        $_menu_class .= ' active';
                    }
                    $primary_links .= '<li><a'.( isset($_menu['class'])?' class="'.$_menu_class.' '.$_menu['class'].'"':' class="'.$_menu_class.'"' ).' href="index.php?p='.$_menu['link'].'" title="'.__($_menu['description']).'"><span>'.__($_menu['name']).'</a></span></li>';
                }
            }
            $primary_links .= '</ul>'."\n";

            // generate navigation menu
            if ($this->navigationMenu) {
                $navigation_links = '<div id="navigation-block">'."\n";
                foreach ($this->navigationMenu as $_nav_section => $_nav_menus) {
                    $navigation_links .= '<div class="navigation-section-title">'.__($_nav_section).'</div>'."\n";
                    $navigation_links .= '<ul class="navigation-list">'."\n";
                    foreach ($_nav_menus as $_nav_menu) {
                        $navigation_links .= '<li><a'.( isset($_menu['class'])?' class="navigation '.$_menu['class'].'"':' class="navigation"' ).' href="index.php?p='.$_nav_menu['link'].'" title="'.__($_nav_menu['description']).'"><span>'.__($_nav_menu['name']).'</span></a></li>';
                    }
                    $navigation_links .= '</ul>'."\n";
                }
                $navigation_links .= '</div>'."\n";
            }

            // load the template
            require TEMPLATES_BASE.$_template.DSEP.$_template_file;
        } else {
            // load CSS and javascript links
            $css = $this->loadCSS();
            $javascripts = $this->loadJS();
            $main_content .= $this->generateView();

            echo $main_info;
            // only output main content
            echo $main_content;
            echo $closure;
        }
        exit(0);
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
     * Method to set main view configuration
     *
     * @param   string  $str_view_config_ID: id of config
     * @param   string  $str_view_config_value: view config data
     * @return  void
     */
    public function setViewConfig($str_view_config_ID, $str_view_config_value) {
        $this->viewsConfig[$str_view_config_ID] = $str_view_config_value;
    }


    /**
     * Method to parse module request
     *
     * @return  void
     */
    private function parseRequest() {
        $this->currentRequest = !isset($_GET['p'])?$this->config['default_module'].'/index':$_GET['p'];
        // explode string by slash
        $_request = explode('/', $this->currentRequest, 9);
        // main module to load
        $this->currentModule = isset($_request[0])?trim(str_ireplace('/', '', $_request[0])):$this->config['default_module'];
        // main method to call
        $this->currentMethod = isset($_request[1])?trim(str_ireplace('/', '', $_request[1])):'index';
        // check current method and reroute to index if the method is restricted
        $_restricted_method = array('init', 'validate', 'manipulate', 'reroute');
        if (in_array($this->currentMethod, $_restricted_method)) {
            $this->currentMethod = 'index';
        }
        // remaining are method params
        foreach ($_request as $_id => $_request_val) {
            if ($_id > 1 && isset($_request[$_id])) {
                $_request_val = trim(str_replace('/', '', $_request_val));
                $this->currentParam .= $_request_val.'/';
            }
        }
        // strip the last slash of param
        $this->currentParam = preg_replace('@\/$@i', '', $this->currentParam);
    }


    /**
     * Callback method for php error handling
     *
     * @param   integer $errno
     * @param   string  $errstr
     * @param   string  $errfile
     * @param   integer $errline
     * @return  boolean
     */
    public function phpErrorHandler($errno, $errstr, $errfile, $errline) {
        $this->errors[$errno] = array('code' => $errno, 'message' => $errstr.' at '.$errfile.' on line '.$errline);
        return true;
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
     * @param   string  $str_module_name = name of module
     * @param   string  $str_log_msg = log messages
     * @param   integer $int_operation_constant = simbio operation constant
     * @return  void
     */
    public function writeLogs($str_module_name, $str_log_msg, $int_operation_constant = 0)
    {
        $_str_sql = 'INSERT INTO {syslogs} ';
        $_str_sql .= '(log_flag, log_module, log_operator_id, log_message)';
        $_str_sql .= ' VALUES ';
        $_str_sql .= '('.$int_operation_constant.', \''.trim(strtolower($str_module_name)).'\',';
        $_str_sql .= $_SESSION['userID'].', \''.trim(strip_tags(strtolower($str_log_msg))).'\')';
        $this->dbQuery($_str_sql);
    }
}
?>
