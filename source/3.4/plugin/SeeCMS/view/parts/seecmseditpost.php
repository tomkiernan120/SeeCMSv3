<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$post = $data['post'];
$routes = $data['postRoutes'];
$templates = $data['templates'];

$formSettings['controller']['name'] = 'SeeCMSPost';
$formSettings['controller']['method'] = 'update';

$formSettings['validate']['title']['validate'] = 'required';
$formSettings['validate']['title']['error'] = 'Please enter a title.';

$timeRange = SeeHelperController::timeRange( 0, 23, 1, 15, true );
$timeRange[] = '23:59';

$f = $see->html->form( $formSettings );

?>

<div class="col1"><div class="sectiontitle"><h2>Post details<?php echo(( $data['editError'] ) ? " - {$data['editError']}" : "" ); ?></h2></div>
<div class="columns">

<div class="column">
				<div class="section">
					<h2>Post information</h2>
					<div class="sg_input">
						<p>Title</p>
						<p><?php $f->text( array( 'name' => 'title', 'value' => $post->title  ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>HTML title</p>
						<p><?php $f->text( array( 'name' => 'htmltitle', 'value' => $post->htmltitle, 'class' => 'count' ) ); ?> <span><span id="count">0</span> chars</span></p>
					</div>
          
					<?php

					if( $post->posttype_id == 1 ){ 
					
					?> 
         
					<div class="sg_input">
						<p>Post date</p>
						<p><?php $f->text( array( 'name' => 'postdate', 'id' => 'postdate', 'class' => 'datepicker', 'value' => $see->format->date( $post->date, "d M Y" ) ) ); ?></a></p>
					</div>

					<?php

					} else if( $post->posttype_id == 2 ) {
					
					?>

					<h2>Event settings</h2>
          <?php $f->hidden( array( 'name' => 'postdate', 'id' => 'postdate', 'value' => date("Y-m-d") ) ); ?>
					<div class="sg_input">
						<p>Event start date</p>
						<p><?php $f->text( array( 'name' => 'eventstartdate', 'id' => 'eventstartdate', 'class' => 'datepicker', 'value' => $see->format->date( $post->eventstart, "d M Y" ) ) ); ?></p>
					</div>					
					<div class="sg_input">
						<p>Event start time</p>
						<p><?php $f->select( array( 'name' => 'starttimehour', 'class' => 'small', 'value' => $see->format->date( $post->eventstart, "H" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,23) ) ); ?><?php $f->select( array( 'name' => 'starttimeminute', 'class' => 'small', 'value' => $see->format->date( $post->eventstart, "i" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,59) ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Event end date</p>
						<p><?php $f->text( array( 'name' => 'eventenddate', 'id' => 'eventenddate', 'class' => 'datepicker', 'value' => $see->format->date( $post->eventend, "d M Y" ) ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Event end time</p>
						<p><?php $f->select( array( 'name' => 'endtimehour', 'class' => 'small', 'value' => $see->format->date( $post->eventend, "H" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,23) ) ); ?><?php $f->select( array( 'name' => 'endtimeminute', 'class' => 'small', 'value' => $see->format->date( $post->eventend, "i" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,59) ) ); ?></p>
					</div>
					<hr/>
          
					<?php } ?>

					<div class="sg_input">
						<p>Introduction</p>
						<p><?php $f->textarea( array( 'name' => 'standfirst', 'class' => 'standfirst', 'value' => $post->standfirst ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Tags</p>
						<p><?php $f->textarea( array( 'name' => 'tags', 'class' => 'standfirst', 'value' => $post->tags ) ); ?></p>
					</div>
          
          <?php
          
          // Custom post type
          if( isset( $see->SeeCMS->customPostController[$post->posttype->name]['plugin'] ) ) {
            $customPostController = $see->{$see->SeeCMS->customPostController[$post->posttype->name]['plugin']};
            echo $customPostController->editFields( $f, $post );
          }
          
          ?>
          
				</div>
				<div class="section"></div>
				<div class="section">
					<h2>Search engine optimisation</h2>
					<div class="sg_input">
						<p>Post description</p>
						<p><?php $f->textarea( array( 'name' => 'metadescription', 'rows' => 5, 'cols' => 38, 'value' => $post->metadescription ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Post keywords</p>
						<p><?php $f->textarea( array( 'name' => 'metakeywords', 'rows' => 5, 'cols' => 38, 'value' => $post->metakeywords ) ); ?></p>
					</div>
				</div>
			</div>
      
			<div class="column">
				<div class="section">
					<h2>Settings</h2>
					<div class="template">
						<div class="thumbnail">
							<img src="/seecms/images/templates/home.gif" alt="" />
						</div>
						<div class="templateselect">
							<p>Template</p>
							<p><?php $f->select( array( 'name' => 'template', 'value' => $post->template ), array( 'options' => $templates, 'optionValueOnly' => true ) ); ?></p>
						</div>
					</div>
					<p>Add thumbnail</p>
					<div class="thumbnail postthumbnail">
<?php

if( $post->media_id ) {

  echo "<img src=\"/images/uploads/img-139-139-{$post->media->id}.{$post->media->type}\" />";
}

echo "</div>";

$f->hidden( array( 'name' => 'media_id', 'id' => 'media_id', 'value' => $post->media_id ) );
?>
					<div class="buttons">
						<a href="#" class="addthumb" id="seecmspostmedia">Add/change</a>
						<a href="#" class="addthumb" id="seecmspostremovemedia">Remove</a>
					</div>
					<div class="clear"></div>
					<div class="sg_input">
						<p>Commencement date</p>
						<p>
							<?php $f->text( array( 'name' => 'commencement', 'id' => 'commencement', 'class' => 'datepicker', 'value' => $see->format->date( $post->commencement, "d M Y" ) ) ); ?>
							<?php $f->select( array( 'name' => 'commencementtime', 'class' => 'time', 'value' => $see->format->date( $post->commencement, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) ); ?>
							<a href="#" class="cleardate"></a>
						</p>

					</div>
					<div class="sg_input">
						<p>Expiry date</p>
						<p><?php $f->text( array( 'name' => 'expiry', 'id' => 'expiry', 'class' => 'datepicker', 'value' => $see->format->date( $post->expiry, "d M Y" ) ) ); ?>
							<?php $f->select( array( 'name' => 'expirytime', 'class' => 'time', 'value' => $see->format->date( $post->expiry, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) ); ?>
						 <a href="#" class="cleardate"></a></p>
					</div>
					<hr />
<?php

if( $post->posttype->ownCategory ) {

  echo "<h3>Categories</h3>";
  foreach( $post->posttype->ownCategory as $category ) {
  
    echo "<p>{$category->name}: ";
		$f->checkbox( array( 'name' => "category_{$category->id}", 'value' => $post->sharedCategory[$category->id] ) );
    echo "</p>";
  }
  
  echo "<hr />";
}

?>
				</div>
				<div class="adf">
					<h3>Post URLs</h3>
					<div class="pageurls">
						<?php
              $routeCounter = 0;
              foreach( $routes as $r ) {
              
   
                if( $routeCounter == 0 ) {
                
                  echo '<div class="pageurlsinner"><p>Primary URL</p><p>';
                  $f->text( array( 'name' => "route{$routeCounter}", 'value' => $r->route, 'id' => "route{$routeCounter}" ) );
                  echo "</p>";
                  $route = $r;
                }
								

                echo (( $routeCounter == 1 ) ? '<div class="url url1"><p>Secondary URLs</p></div><div class="url url2"><p>Delete?</p></div><div class="url url3"><p>Make Primary?</p></div><div class="clear"></div>' : '' );

                if( $routeCounter >= 1 ) {
                  echo "<div class=\"route\"><p>";
                  $f->text( array( 'name' => "route{$routeCounter}", 'value' => $r->route, 'id' => "route{$routeCounter}" ) );
                
                  echo "<span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleteroute{$routeCounter}\" /></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryroute{$routeCounter}\" /></span>";
                  echo "</p></div>";
     						}
                
                $routeCounter++;

              }
	              echo "<div class=\"clear\"></div>";
	              echo "<a href=\"#\" class=\"addnewroute\">Add route</a>";
                echo "<script>var nextroute = {$routeCounter}; var routeHTML = '<div class=\"route\"><p><input type=\"text\" id=\"routeXXX\" value=\"\" name=\"routeXXX\"><span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleterouteXXX\"><span class=\"checkbox\"></span></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryrouteXXX\" ><span class=\"checkbox\"></span></span></p></div>'; var routeHTMLHead = '<div class=\"url url1\"><p>Secondary URLs</p></div><div class=\"url url2\"><p>Delete?</p></div><div class=\"url url3\"><p>Make Primary?</p></div><div class=\"clear\"></div>';</script>";
            ?>
					</div>
				</div>
			</div>
      
<?php

$adfs = SeeDB::find( 'adf', ' objecttype = ? && ( ( objectid = ? && `cascade` = ? ) || ( ( objectid = ? || objectid = ? ) && `cascade` = ? ) ) && ( theme = ? || theme = ? ) ', array( 'post', $post->id, 0, $post->parentid, 0, 1, '', $see->theme ) );
$cc = new SeeCMSContentController( $see, $see->SeeCMS->language );
foreach( $adfs as $adf ) {

  $cc->objectType = $r->objecttype;
  $cc->objectID = $r->objectid;
  
  $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'post', $post->id, $adf->id, $see->SeeCMS->language ) );

  echo '<div class="adf">';
  echo "<h3 id=\"editable{$adf->id}\" class=\"editcontent editcontentADF adfpopup\">{$adf->title}</h3>";
  echo $cc->makeEditPart( $adf->id, 'ADF', $content->content, 1, true );
  
  $adfpopup .= $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, true )."\r\n";
  
  echo '</div>';
}

?>

	</div>
</div></div>

	<div class="col2">
		<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'value' => 'Save changes', 'class' => 'save' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $post->id ) ); ?></div>
		<div class="editpage">
      <a class="editpage" href="<?php echo '/'.$route->route; ?>?preview=1">Preview/edit post</a>
		</div>
		<div class="support">
			<h2>Support</h2>
			<div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
		</div>
	</div>
	<div class="clear"></div>
<div class="selectpostimage" id="selectpostimage" title="Select image" style="display: none;">
<div class="adfimages"><div class="select selectImage"><p><select id="imageFolder"></select></p></div><div class="medialistinner folders"></div><div class="clear"></div></div>
</div>
<?php 

$f->close();
echo $adfpopup;

?>