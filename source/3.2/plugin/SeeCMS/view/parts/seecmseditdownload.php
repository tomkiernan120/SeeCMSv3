<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$download = $data['download'];
$userGroups = $data['userGroups'];
$userGroupPermission = $data['userGroupPermission'];

$formSettings['controller']['name'] = 'SeeCMSDownload';
$formSettings['controller']['method'] = 'update';

$formSettings['validate']['name']['validate'] = 'required';
$formSettings['validate']['name']['error'] = 'Please enter a title.';

$f = $see->html->form( $formSettings );

?>

<div class="col1"><div class="sectiontitle"><h2>Edit download</h2></div>



<div class="column columnfull">
	<div class="left">
		<p>Download name</p>
		<p><?php $f->text( array( 'name' => 'name', 'value' => $download->name )); ?></p>
		<p>Download description</p>
		<p><?php $f->textarea( array( 'name' => 'description', 'value' => $download->description )); ?></p>
    <p><a href="/seecmsfile/?id=<?php echo $download->id; ?>">Download file</a></p>
    <!--
		<h2>Usage</h2>
		<p><strong>Pages:</strong><br/>Does not appear on any pages ??</p>
		<p><strong>Posts:</strong><br/>Does not appear on any posts ??</p>
		-->
		<hr/>
		<div class="exif">
			<p><strong>File size -</strong> <?php echo $download->filesize; ?></p>
			<p><strong>File type -</strong> <?php echo strtoupper( $download->type ); ?></p>
			<p><strong>Uploaded -</strong> <?php echo $see->format->date( $download->uploaded, "d M y / H:i:s" ); ?></p>
		</div>
	</div>
  
<div class="right">
<p>Document permissions</p>
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
} else {
  echo "<p><strong>Please add some site user groups if you want to set permissions</strong></p>";
}

?>
</div>
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