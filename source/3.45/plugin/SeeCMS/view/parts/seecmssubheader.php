<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

echo '<div class="headerwrap"><div class="header"><div class="left"><a target="_blank" class="logo" href="http://www.seecms.net"></a></div>';

echo '<div class="right">';

echo "<p class=\"sitename\">Welcome to <span>{$see->siteTitle}</span></p>";

echo "<p><seecmsupdatealert> You are logged in as {$_SESSION['seecms'][$see->siteID]['adminuser']['name']} <a href=\"?seecmsLogout=1\" id=\"logout\">Log out</a> <a id=\"visitwebsite\" target=\"_blank\" href=\"/\">Visit website</a></p>";

if( count( $data ) ) {
  echo "<div class=\"visitmultisite\"><ul>";
  
  foreach( $data as $s ) {
    
    echo "<li><a target=\"_blank\" href=\"http://{$s->name}\">{$s->name}</a></li>";
  }
  
  echo "</ul></div>";
}

echo '</div></div></div>';