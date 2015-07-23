<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
?>
<div class="col1">
<div class="sectiontitle"><h2>Site users</h2></div> 
<div class="columns">
<div class="column snav">
<?php

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/siteusers/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

?>
</div>
<div class="column columnwide">
<table class="order users">
<thead>
<tr><th>Surname</th><th>Forename</th><th>Email</th></tr>
</thead>
<tbody>
<?php

foreach( $data as $wu ) {

  echo "<tr><td>{$wu->surname}</td><td>{$wu->forename}</td><td>{$wu->email}</td><td><a href=\"editusers/?id={$wu->id}\">Edit</a></td><td class=\"delete\"><a class=\"delete\" data-siteuserid=\"{$wu->id}\"></a></td></tr>";
}

?>
</tbody>
</table>
</div>
</div>

<div class="clear"></div>
</div>

<div class="col2">
<div class="createpage">
<a class="createuser" href="editusers/">Create new user</a>
</div>
<div class="support">
<h2>Support</h2> 
<div class="supportinfo">
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>
<div id="deleteuserpopup" title="Delete user?"></div>