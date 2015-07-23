<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 
/*********************************************/
/***************** SETTINGS ******************/
/*********************************************/

/* Install */
if( !file_exists( '../plugin/SeeCMS/install.txt' ) ) {
  $install = true;
} else {
  /* Connect DB */
  $this->dbConnect( $configuration['DBHost'], $configuration['DBName'], $configuration['DBUsername'], $configuration['DBPassword'] );
}

/* Turn on mixed routing */
$this->mixedRouting();

/* Set the route manager controller and method */
$this->routeManager( array( 'plugin' => $pluginName, 'method' => 'routeManager' ) );
$this->outputManager( array( 'plugin' => $pluginName, 'method' => 'outputManager' ) );

/* Load classes */
include 'controller/SeeCMS.php';
include 'controller/SeeCMSAdminAuthentication.php';
include 'controller/SeeCMSAjax.php';
include 'controller/SeeCMSAnalytics.php';
include 'controller/SeeCMSContent.php';
include 'controller/SeeCMSDownload.php';
include 'controller/SeeCMSHelper.php';
include 'controller/SeeCMSHooks.php';
include 'controller/SeeCMSMedia.php';
include 'controller/SeeCMSPage.php';
include 'controller/SeeCMSPost.php';
include 'controller/SeeCMSSearch.php';
include 'controller/SeeCMSSetting.php';
include 'controller/SeeCMSUpdate.php';
include 'controller/SeeCMSWebsiteUser.php';

/* Load plugin */
$plugin = new SeeCMSController( $this, $configuration, $install );