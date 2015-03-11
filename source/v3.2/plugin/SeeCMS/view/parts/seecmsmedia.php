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
$see->html->js( 'dropzone-media.js', '', '/seecms/js/' );

$see->html->js( '', 'skipInitialMediaLoad = 1;', '' );

?>
  <div class="col1">
    <div class="imagesection">
      <div class="route">
        <div class="foldertitle">
          <h3>Folders</h3>
          <div class="title"><p>Folder name</p></div>
          
        </div>
        
      </div>
      <div class="folders">
        <?php echo $data['folderTree']; ?>
      </div>
      <div class="images">
        <div class="medialistinner">
          <?php echo $data['media']; ?>
        </div>

        <div class="clear"></div>
        <div id="mediadropzone" class="dropzone"></div>

      </div>

    <div class="clear"></div>
    </div>
  </div>
  <div class="col2">
    <div class="createpage">
      <a class="createmediafolder" href="#">Create folder</a>
    </div>
    <div class="support">
      <h2>Support</h2> 
      <div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
    </div>
  </div>
  <div class="clear"></div>
  
<div id="newmediafoldertitle" title="Create new folder">
  <p>Folder title:<br /><input type="text" id="foldertitle" /></p>
</div>

<div id="deletemediafolderpopup" title="Delete folder?"></div>

<div id="editmediafolderpopup" title="Edit folder name">
<p>Folder title:<br /><input type="text" id="foldertitle2" /></p>
</div>

<div id="deletemediapopup" title="Delete file?"></div>