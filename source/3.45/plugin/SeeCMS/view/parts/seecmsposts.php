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
    <div class="imagesection">
      <div class="route">
        <div class="foldertitle">
          <h3>Folders</h3>
          <h3 class="foldername"></h3>
        </div>
      </div>
      <div class="folders">
        <?php echo $data['folderTree']; ?>
      </div>
      <div class="newslist">
        <div class="newslistinner">
          <?php echo $data['posts']; ?>
        </div>
      </div>
    </div>  
  </div>  
  <div class="col2">
    <div class="createpage">
      <a class="createfolder" href="#">Create folder</a>
      <a class="createpost" href="#">Create a new post</a>
    </div>
    <div class="support">
      <h2>Support</h2>
      <div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?> 
      </div>
    </div>
  </div>
  <div class="clear"></div>
<div id="newposttitle" title="Create new post">
<p>Post title:<br /><input type="text" id="posttitle" /></p>
<?php 

if( is_array( $data['posttypes'] ) ) {

  echo '<p>Post type:<br /><select id="posttype">';
  foreach( $data['posttypes'] as $p ) {
    echo "<option value=\"{$p->id}\">{$p->name}</option>";
  }
  echo '</select></p>';
} else {
  echo '<input type="hidden" id="posttype" value="1" />';
}

?>
</div>
<div id="newfoldertitle" title="Create new folder">
<p>Folder title:<br /><input type="text" id="foldertitle" /></p>
</div>
<div id="deletepostfolderpopup" title="Delete folder?"></div>
<div id="deletepostpopup" title="Delete post?"></div>
<div id="editpostfolderpopup" title="Edit folder name">
<p>Folder title:<br /><input type="text" id="foldertitle2" /></p>
</div>
