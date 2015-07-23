<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

echo '<div class="headerwrap"><div class="header"><div class="left"><a href="http://www.seecms.net"><img src="/seecms/images/logo.gif" alt="" /></a></div><div class="center">';

if( isset( $_SESSION['seecms']['adminuser']['id'] ) ) {
  echo "<p>Logged in as {$_SESSION['seecms'][$this->see->siteID]['adminuser']['name']}</p>";
}

echo '</div><div class="right">';

echo '<a href="?seecmsLogout=1" id="logout">Log out</a>';

echo '</div></div></div>';