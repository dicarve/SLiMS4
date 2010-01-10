<?php
/**
 * SENAYAN Library Management System 4 (SLiMS4)
 * SLiMS4 application global file configuration
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

/**
 * Database connection URI
 * Please modify this according to your database connection options
 *
 * We prefer to use MySQLI
 * mysqli://YOUR_DATABASE_USERNAME:YOUR_DATABASE_PASSWORD@DATABASE_HOST/DATABASE_NAME
 *
 * if you don't have MySQLi extension enabled, then you can use old MySQL extension
 * mysql://YOUR_DATABASE_USERNAME:YOUR_DATABASE_PASSWORD@DATABASE_HOST/DATABASE_NAME
 *
 * if you use non-standard MySQL port then please supply the port after database host
 * mysqli://YOUR_DATABASE_USERNAME:YOUR_DATABASE_PASSWORD@DATABASE_HOST:DATABASE_PORT/DATABASE_NAME
 */
$config['db_uri'] = 'mysqli://arie:ariearie@localhost/slimsdb';

/**
 * Database table prefix
 */
$config['db_prefix'] = 'slims_';

/**
 * Default module to load
 */
$config['default_module'] = 'Biblio';

/**
 * Default template
 */
$config['default_template'] = 'default';

/**
 * Admin template
 */
$config['admin_template'] = 'default';

/**
 * Application title
 */
$config['app_title'] = 'SLiMS';

/**
 * Application subtitle
 */
$config['app_subtitle'] = 'Open Source Library Management System';

/**
 * Copyright info
 */
$config['copyright_info'] = 'SLiMS (SENAYAN Library Management System) is licensed under GNU GPL Version 3. <a href="http://senayan.diknas.go.id">SLiMS Official Website</a>';

/**
 * Mysqldump settings
 */
$config['mysqldump'] = '/usr/local/bin/mysqldump';

/**
 * Application wide constants
 *
 * You better don't change below constants especially APP_BASE and SIMBIO_BASE constants
 * except you have other Simbio framework installed in other directory
 */
// Application base dir
define( 'APP_BASE', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR );
// SLiMS version
define( 'APP_VERSION', 'SLiMS 4.0');
// Application session cookie name
define( 'APP_SESSION_COOKIE_NAME', 'slims4' );
// Application web base dir
$_app_web_base = preg_replace('@modules.+@i', '', dirname($_SERVER['PHP_SELF']));
define( 'APP_WEB_BASE', $_app_web_base.(preg_match('@\/$@i', $_app_web_base)?'':'/') );
// Simbio framework base
define( 'SIMBIO_BASE', APP_BASE.'simbio3'.DIRECTORY_SEPARATOR );
// Simbio framework web base
define( 'SIMBIO_WEB_BASE', APP_WEB_BASE.'simbio3/' );
?>
