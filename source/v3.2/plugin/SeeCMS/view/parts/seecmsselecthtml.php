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

?>

<script>

args = top.tinymce.activeEditor.windowManager.getParams();
var currentnode = args.node;
var currentnodeSet = false;

$(document).ready(function(){
  if( currentnode.outerHTML.search("class=\"seecmshtml\"") > 0 ) {
    currentnodeSet = true;
    $('#html').val( currentnode.innerHTML );
  }
});

function insertHTML() {
  
  window.parent.tinyMCE.activeEditor.execCommand( 'mceInsertContent', 0, "<div class=\"seecmshtml mceNonEditable\">" + $('#html').val() + "</div><p></p>" );
  top.tinymce.activeEditor.windowManager.close();
}
</script>

<div class="popup">

<p><textarea id="html"></textarea></p>
<a class="commit" href="#" onclick="insertHTML()">Insert HTML</a>
</div>