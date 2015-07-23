<?php

/*********************************************/
/***************** SETTINGS ******************/
/*********************************************/

$see->version = 0.3;
$see->AESKey = '[AESKEY]';
$see->siteID = '[SITEID]';
$see->siteTitle = '[SITETITLE]';

$see->theme = '[THEME]'; // Theme

$see->setRootURL( '[ROOTURL]' );
$see->publicFolder = '[PUBLICFOLDER]';

$seecmsConfig['cmsRoot'] = '[CMSROOT]'; // CMS address 

$seecmsConfig['DBHost'] = '[DBHOST]'; // MySQL host
$seecmsConfig['DBName'] = '[DBNAME]'; // MySQL DB
$seecmsConfig['DBUsername'] = '[DBUSERNAME]'; // MySQL username
$seecmsConfig['DBPassword'] = '[DBPASSWORD]'; // MySQL password

$seecmsConfig['supportMessage'] = '[CMSSUPPORTMESSAGE]';

$see->loadPlugin( 'SeeCMS', $seecmsConfig ); // Load SeeCMS