<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$media = $data['media'];
$mediaDimensions = $data['mediaDimensions'];

$formSettings['controller']['name'] = 'SeeCMSMedia';
$formSettings['controller']['method'] = 'update';

$formSettings['validate']['title']['validate'] = 'required';
$formSettings['validate']['title']['error'] = 'Please enter a title.';


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
	echo "<p><strong>Dimensions -</strong>{$mediaDimensions['width']} x {$mediaDimensions['height']}</p>";
} 
?>
			<p><strong>File type -</strong> <?php echo strtoupper( $media->type ); ?></p>
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
  echo "<img src=\"/images/uploads/img-720-720-{$media->id}.{$media->type}\" alt=\"\" />";
}
?>
		
	</div>
</div>
</div>

	<div class="col2">
		<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'value' => 'Save changes', 'class' => 'save' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $media->id ) ); ?></div>
		<div class="support">
			<h2>Support</h2>
			<div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
		</div>
	</div>
	<div class="clear"></div>
  
<?php 

$f->close();

echo $adfpopup;
