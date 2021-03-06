<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->start();
$see->html->css( 'editor.css', 'screen', '/seecms/css/' );

$see->html->js( 'jquery-1.11.1.min.js', '', '/seecms/js/' );
$see->html->js( 'editorpopup.js', '', '/seecms/js/' );
$see->html->js( 'loadfunctions.js', '', '/seecms/js/' );

?>
<script>

var cmsURL;

function insertContent() {

  var ext = selectedItemSRC.split('.').pop(); 
  var size = $('#imagesize').val().split('~'); 
  if( size[2] ) {
    var sizeid = size[2];
  } else {
    var sizeid = size[1];
  }
  
  window.parent.tinyMCE.activeEditor.execCommand( 'mceInsertContent', 0, '<img alt="" class="' + $('#imagealign').val() + '" src="' + args.url + 'images/uploads/img-' + sizeid + '-' + selectedItem.replace('i','') + '.'+ ext +'" />' );
  top.tinymce.activeEditor.windowManager.close();
}

$( function() {

  loadMediaByFolder( '../../', 'selectimage' );
  loadMediaFolders( '../../', 'option', 'imageFolder' );
  
  $('#imageFolder').on( 'change', function() {
    
    mediafolder = $('#imageFolder option:selected').attr('id').replace('folder','');
    loadMediaByFolder( '../../', 'selectimage' );
  })
  
});


</script>

<div class="popup">
<div class="select selectImage">
<p><select id="imageFolder"></select></p>
</div>
<div class="medialistinner folders"></div>
<div class="clear"></div>
<div class="finalstep">
	<div class="select selectmelast">
<p>
<select id="imagesize">
	<option value="original">Select image size</option>
<?php

  foreach( $data['imagesizes'] as $is ) {
  
    echo "<option value=\"size~{$is->id}~{$is->identifier}\">{$is->name}</option>";
  }

?>
</select>
</p>
<p><select id="imagealign">
	<option value="seecmsimagedefault">Default</option>
	<option value="seecmsimageleft">Left</option>
	<option value="seecmsimagecentre">Centre</option>
	<option value="seecmsimageright">Right</option>
</select></p>
</div>
	<a class="commit" href="#" onclick="insertContent()">Insert image</a>
</div>
</div>
