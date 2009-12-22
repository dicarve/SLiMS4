<?php
/**
 * SLiMS4 application bootstrap file configuration
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

 // include the main system configuration
 require 'sysconfig.inc.php';
 require SIMBIO_BASE.'Simbio.inc.php';

 // create new instance of simbio framework
 try {
     $simbio = Simbio::create($config);
 } catch (Exception $err) {
    die('Application error! with message : <strong>'.$err->getMessage()."</strong>\n");
 }

// run the framework
$simbio->main();

?>
