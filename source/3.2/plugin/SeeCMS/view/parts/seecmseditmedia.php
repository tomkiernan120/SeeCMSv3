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

$f = $see->html->form( $formSettings );

?>

<div class="col1"><div class="sectiontitle"><h2>Edit media</h2></div>


<div class="column columnfull">
	<div class="left">
		<p>Name</p>
		<p><?php $f->text( array( 'name' => 'name', 'value' => $media->name )); ?></p>
		<p>Alt text</p>
		<p><?php $f->text( array( 'name' => 'alt', 'value' => $media->alt )); ?></p>
		
		<hr/>
		<div class="exif">
			<p><strong>Dimensions -</strong> <?php echo "{$mediaDimensions['width']} x {$mediaDimensions['height']}"; ?></p>
			<p><strong>File type -</strong> <?php echo strtoupper( $media->type ); ?></p>
			<!-- <p><strong>Uploaded -</strong> XXX</p>
			<p><strong>Length -</strong> XXX</p>
			<p><strong>Author -</strong> XXX</p> -->
		</div>
	</div>
	<div class="right">
		<img src="/images/uploads/img-720-720-<?php echo $media->id ?>.<?php echo $media->type ?>" alt="" />
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