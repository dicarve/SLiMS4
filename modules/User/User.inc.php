<?php
/**
 * User module class
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

class User extends SimbioModel {

    /**
     * Method that must be defined by all child module
     * used by framework to get module information
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of module information containing
     */
    public static function moduleInfo(&$simbio) {
        return array('module-name' => 'User',
            'module-desc' => 'Enable application user management and authentication based on roles',
            'module-depends' => array());
    }


    /**
     * Method that must be defined by all child module
     * used by framework to get module privileges type
     *
     * @param   object      $simbio: Simbio framework object
     * @return  array       an array of privileges for this module
     */
    public static function modulePrivileges(&$simbio) {

    }


    /**
     * Class constructor
     *
     * @param   object  $simbio: Simbio framework object
     * @return  void
     */
    public function __construct(&$simbio) {
        $this->dbTable = 'users';
    }


    /**
     * Method to add module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function add(&$simbio, $str_args) {

    }


    /**
     * Configuration for module
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function config(&$simbio, $str_args) {

    }


    /**
     * Method to get block content
     *
     * @param   object      $simbio: Simbio framework instance/object
     * @param   string      $str_block_type: string of block type to load
     * @return  string
     */
    public static function getBlock($simbio, $str_block_type) {

    }


    /**
     * Default module page method
     * All module must have this method
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function index(&$simbio, $str_args) {
        if (!User::isUserLogin()) {
            $this->login($simbio, $str_args);
        }
    }



    /**
     * Method to login user
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function login(&$simbio, $str_args) {
        if (isset($_POST['userLogin']) && !User::isUserLogin()) {
            // User login
            $_username = $simbio->filterizeSQLString($_POST['username']);
            $_password = $simbio->filterizeSQLString($_POST['password']);
            // check if user exists
            $_user_check = $simbio->dbQuery('SELECT * FROM {'.$this->dbTable.'} WHERE username=\''.$_username.'\' AND pswd=MD5(\''.$_password.'\')');
            if ($_user_check->num_rows) {
                // regenerate session ID
                session_regenerate_id();
                // get user data
                $_user_d = $_user_check->fetch_assoc();
                $simbio->addInfo('USER_LOGIN_SUCCESS', __('Welcome. Your currently logged in as '.$_user_d['realname']));
                // session registering
                $_SESSION['User']['ID'] = $_user_d['user_id'];
                $_SESSION['User']['Name'] = $_user_d['realname'];
                $_SESSION['User']['Username'] = $_user_d['username'];
                $_roles = @unserialize($_user_d['roles']);
                $_SESSION['User']['Priv'] = array();
                // get user access privileges
                if ($_roles && is_array($_roles)) {
                    foreach ($_roles as $_role) {
                        $_access = $simbio->dbQuery("SELECT access FROM {role_access} WHERE role_id=$_role");
                        while ($_access_d = $_access->fetch_row()) {
                            $_SESSION['User']['Priv'][$_access_d[0]] = $_access_d[0];
                        }
                    }
                }
                header('Location: index.php?p=admin');
                exit();
            }
        } else {
            // Login form
            $_login_form = new FormOutput('login-form', 'index.php?p=user/login', 'post');
            $_login_form->submitName = 'userLogin';
            $_login_form->submitValue = __('Logon');
            $_login_form->formInfo = __('Please supply valid username and password to login. Please check if your "caps lock" is turned on or not.');
            // define form elements
            $_form_items[] = array('id' => 'username', 'label' => __('Username/Member ID'), 'type' => 'text', 'maxSize' => '50', 'required' => 1);
            $_form_items[] = array('id' => 'password', 'label' => __('Password'), 'type' => 'password', 'maxSize' => '50', 'required' => 1);
            foreach ($_form_items as $_item) {
                $_login_form->add($_item);
            }
            $simbio->setViewConfig('Page Title', __('User Login'));
            $simbio->loadView($_login_form, 'Login Form');
        }
    }


    /**
     * Method to logout user
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function logout(&$simbio, $str_args) {
        if (User::isUserLogin()) {
            $_name = $_SESSION['User']['Name'];
            Utility::destroySessionCookie(APP_SESSION_COOKIE_NAME, APP_WEB_BASE);
            $simbio->addInfo('USER_LOGGED_OUT', $_name.', you have been successfully logged out.');
        }
    }


    /**
     * Method to manage user and roles
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function manage(&$simbio, $str_args) {

    }


    /**
     * Method returning an array of application main menu and navigation menu
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @param   string  $str_current_module: current module called by framework
     * @param   string  $str_current_method: current method of current module called by framework
     * @return  array
     */
    public function menu(&$simbio, $str_menu_type = 'navigation', $str_current_module = '', $str_current_method = '') {
        $_menu = array();
        if ($str_menu_type != 'main' && $str_current_module == 'admin' && $str_current_method == 'system') {
            $_menu['System'][] = array('link' => 'user/manage', 'name' => __('Users'), 'description' => __('Application user managements'));
            $_menu['System'][] = array('link' => 'user/manage/role', 'name' => __('Roles/Groups'), 'description' => __('Application user role/group managements'));
        }
        return $_menu;
    }


    /**
     * Method to update module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function update(&$simbio, $str_args) {

    }


    /**
     * Method to remove module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  void
     */
    public function remove(&$simbio, $str_args) {

    }


    /**
     * Method to save/update module data
     *
     * @param   object      $simbio: Simbio framework object
     * @param   string      $str_args: method main argument
     * @return  array       an array of status flag and messages
     */
    public function save(&$simbio, $str_args) {

    }


    /**
     * Method to check if user have privilege to certain module
     *
     * @param   string  $str_privileges: string of privileges type to check
     * @return  boolean true if user have privilege and false if not
     */
    public static function userHavePrivileges($str_privileges) {
        if (isset($_SESSION['User']['Priv'][$str_privileges])) {
            return true;
        }
        return false;
    }


    /**
     * Method to check if user already login
     *
     * @return  boolean true if user already login and false if otherwise
     */
    public static function isUserLogin() {
        if (isset($_SESSION['User']['ID']) && $_SESSION['User']['Name'] && isset($_SESSION['User']['Priv'])) {
            return true;
        }
        return false;
    }
}
?>
