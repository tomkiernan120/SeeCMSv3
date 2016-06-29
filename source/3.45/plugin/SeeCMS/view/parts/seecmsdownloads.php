<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */


$see->html->css( 'dropzone.css', 'screen', '/seecms/css/' );
$see->html->js( 'dropzone.js', '', '/seecms/js/' );
$see->html->js( 'dropzone-downloads.js', '', '/seecms/js/' );

$userGroups = $data['userGroups'];

?>
<div class="col1">
    <div class="imagesection">
      <div class="route">
        <div class="foldertitle">
          <h3>Folders</h3>
          <h3 class="foldername"></h3>
        </div>
      </div>
      <div class="folders downloadfolders">
        <ul>
        <?php echo $data['folderTree']; ?>
        </ul>
      </div>
      <div class="doclist">      
        <div class="doclistinner">
        <?php echo $data['downloads']; ?>
        </div>
      <div id="downloadsdropzone" class="dropzone"><div class="fallback"><form action="../download/add" encType="multipart/form-data" method="post"><div class="dz-fallback"><p>Your browser is too old to support drag and drop uploads, please consider updating to a newer version.</p><input name="file" type="file" undefined="" /><input id="fallbackparentid" name="parentid" type="hidden" value="<?php echo $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder']; ?>" /><input name="doFallback" type="hidden" value="1" /><input type="submit" value="Upload" /></div></form></div></div>
      </div>
 

    </div>
  </div>
<div class="col2">
  <div class="createpage">
    <a class="createdownloadfolder" href="#">Create folder</a>
  </div>
    <div class="support">
      <h2>Support</h2>
      <div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
    </div>
  </div>
<div class="clear"></div>

<div id="deletedocpopup" title="Delete file?"></div>

<div id="newdownloadfoldertitle" title="Create new folder">
  <p>Folder name:<br /><input type="text" id="foldertitle" /></p>
</div>

<div id="deletedownloadfolderpopup" title="Delete folder?"></div>

<div id="editdownloadfolderpopup" title="Edit folder name">
<?php $f = $see->html->form( array() ); ?>
  <p>Folder name:<br /><input type="text" id="foldertitle2" /></p>
  <p>Folder permissions</p>
<div class="security">
<p><?php $f->checkbox( array( 'name' => "security-allUserAccess", 'id' => "security-allUserAccess", "value" => ((is_array($userGroupPermission))?0:1) ) ); ?> Everyone can access this content</p><hr />
<?php
            
if( count( $userGroups ) ) {

  echo "<p>Only specific groups of registered users can access this content:</p>";

  foreach( $userGroups as $ug ) {
  
    echo "<p>";
    $f->checkbox( array( 'name' => "security-group-{$ug->id}", 'id' => "security-group-{$ug->id}", 'class' => "security-group", "value" => (int)$userGroupPermission[$ug->id] ) );
    echo " {$ug->name}</p>";
  }
}
?>
<hr /><p><?php $f->checkbox( array( 'name' => "security-cascade", 'id' => "security-cascade" ) ); ?> Update permissions on all subfolders and downloads</p>
<?php
$f->close();
?>
</div>
</div>
