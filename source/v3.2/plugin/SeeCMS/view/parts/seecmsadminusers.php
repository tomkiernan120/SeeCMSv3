<div class="col1">
      <div class="sectiontitle"><h2>Admin</h2></div>
<div class="columns">
<div class="column snav">
<?php

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/admin/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

?>
</div>
<div class="column columnwide">
<table class="stripey adminusers">
<tr><th>Name</th><th>Level</th></tr>

<?php

foreach( $data as $au ) {

  echo "<tr><td>{$au->name}</td><td>{$au->adminuserrole->name}</td><td><a href=\"edit/?id={$au->id}\">Edit user</a></td><td class=\"delete\"><a class=\"delete\" data-adminuserid=\"{$au->id}\"></a></td></tr>";
}

?>

</table>
</div>
</div>
<div class="clear"></div>
</div>

<div class="col2">
  <div class="createpage">
    <a class="createuser" href="edit/">Create new user</a>
    <A class="cancel">Cancel</a>
  </div>
  <div class="support">
    <h2>Support</h2>
    <div class="supportinfo"><?php echo $see->SeeCMS->supportMessage; ?></div>
  </div>
</div>
<div class="clear"></div>

<div id="newadminuser" title="Create new admin user">
<p>User name:<br /><input type="text" id="foldertitle" /></p>
<p>User level:<br /><select><option>Administrator</option><option>Super user</option><option>Content editor</option></select></p>
</div>
<div id="deleteadminuserpopup" title="Delete admin user?"></div>