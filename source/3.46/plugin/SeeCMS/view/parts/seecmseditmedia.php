<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->js( 'jquery.Jcrop.min.js', '', '/seecms/js/' );
$see->html->js( 'jcropcontroller.js', '', '/seecms/js/' );
$see->html->css( 'jquery.Jcrop.min.css', 'screen', '/seecms/css/' );

$media = $data['media'];
$mediaDimensions = $data['mediaDimensions'];
$imageSizes = $data['imageSizes'];

$formSettings['controller']['name'] = 'SeeCMSMedia';
$formSettings['controller']['method'] = 'update';

$formSettings['validate']['title']['validate'] = 'required';
$formSettings['validate']['title']['error'] = 'Please enter a title.';

$formSettings['attributes']['enctype'] = 'multipart/form-data';


?>

<div class="col1"><div class="sectiontitle"><h2>Edit media</h2></div>

<?php $f = $see->html->form( $formSettings ); ?>

<div class="column columnfull twocolumnfull">
	<div class="left">
		<p>Name</p>
		<p><?php $f->text( array( 'name' => 'name', 'value' => $media->name )); ?></p>
		<p>Alt text</p>
		<p><?php $f->text( array( 'name' => 'alt', 'value' => $media->alt )); ?></p>
		
		<hr/>
		<div class="exif">
<?php 
if( $media->type == 'mp4' ) {
  echo "<p><strong>Embed code -</strong><br /><textarea rows=\"6\">&lt;video width=\"720\" controls&gt;&lt;source src=\"&#47;images/uploads/vid-{$media->id}-{$media->pathmodifier}.{$media->type}\" type=\"video/mp4\"&gt;&lt;/video&gt;</textarea></p>";
} else {
	echo "<p><strong>Original image dimensions</strong><br />{$mediaDimensions['width']} x {$mediaDimensions['height']}</p>";
} 
?>
			<p><strong>File type</strong><br /><?php echo strtoupper( $media->type ); ?></p>
		</div>
<hr />
<div class="seecmsuploadnewversion">
<p>Select a replacement file:<br />
<?php $f->file( array( 'name' => 'newfile' )); ?>
</p>
</div>
<?php

$adfs = SeeDB::find( 'adf', ' objecttype = ? && ( ( objectid = ? && `cascade` = ? ) || ( ( objectid = ? || objectid = ? ) && `cascade` = ? ) ) && ( theme = ? || theme = ? ) ', array( 'media', $media->id, 0, $media->parentid, 0, 1, '', $see->theme ) );
$cc = new SeeCMSContentController( $see, $see->SeeCMS->language );

if( is_array( $adfs ) ) {

  echo "<hr /><h2>Custom data</h2>";

  foreach( $adfs as $adf ) {

    $cc->objectType = 'media';
    $cc->objectID = $media->id;
    
    $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'media', $media->id, $adf->id, $see->SeeCMS->language ) );

    echo '<div class="adf">';
    echo "<h3 id=\"editable{$adf->id}\" class=\"editcontent editcontentADF adfpopup\">{$adf->title}</h3>";
    echo $cc->makeEditPart( $adf->id, 'ADF', $content->content, 1, true );
    $adfpopup .= $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, true )."\r\n";
    
    echo '</div>';
  }
}

?>
    
	</div>
	<div class="right">
<?php 
if( $media->type == 'mp4' ) {
  
	echo "<video width=\"720\" controls><source src=\"/images/uploads/vid-{$media->id}-{$media->pathmodifier}.{$media->type}\" type=\"video/mp4\"></video>";
  
} else {
  
  echo "<p>Preview: <select class=\"editimagesizeselect\" style=\"display: inline; float: none; width: auto;\" id=\"seecmsimagesize\"><option value=\"original\">Original image size</option>";

  foreach( $imageSizes as $is ) {

    echo "<option data-mode=\"{$is->mode}\" value=\"{$is->id}\">{$is->name}</option>";
  }

  // IMAGE RECROP
  echo "</select>";

  echo "<a style=\"display: none;\" class=\"recropimagebutton\" href=\"#\">Recrop image</a>";

  echo "</p>";
  echo "<img class=\"seecmspreviewimage\" src=\"/images/uploads/img-original-{$media->id}.{$media->type}?r=".rand(0,10000)."\" alt=\"\" />";
  
}
?>
		
	</div>
</div>
</div>

	<div class="col2">
		<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'value' => 'Save changes', 'class' => 'save' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $media->id ) ); ?><span><i class="fa fa-floppy-o" aria-hidden="true"></i></span></div>
		<div class="support">
			<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
			<div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
		</div>
	</div>
	<div class="clear"></div>
  
<?php 

$f->close();

echo $adfpopup;

echo "<div class=\"recropoverlay\" style=\"display: none\"></div><div class=\"recropimagewindow\" style=\"display: none\"><div class=\"heading\">";
echo "<h3>Recrop image</h3>";
echo "<a class=\"close-window\" href=\"#\">x</a>";
echo "</div><div class=\"main\"><div class=\"inner\">";
echo "<p>Please select the area you wish to crop/resize to <a class=\"doneRecrop\" href=\"#\">Done</a></p>";
echo "<div class=\"original-image-container crop-image\">";
echo "<img id=\"jcrop-target\" src =\"../../../images/uploads/img-original-{$media->id}.{$media->type}?r=".rand(0,10000)."\" alt=\"\" />";
echo "</div></div></div></div>";
