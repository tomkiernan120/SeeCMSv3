<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
      
$see->html->css( 'editor.css', 'screen', '/seecms/css/' );

$page = $data['page'];
$routes = $data['pageRoutes'];
$userGroups = $data['userGroups'];
$userGroupPermission = $data['userGroupPermission'];
$templates = $data['templates'];

$formSettings['controller']['name'] = 'SeeCMSPage';
$formSettings['controller']['method'] = 'update';

$formSettings['validate']['title']['validate'] = 'required';
$formSettings['validate']['title']['error'] = 'Please enter a title.';

$timeRange = SeeHelperController::timeRange( 0, 23, 1, 15, true );
$timeRange[] = '23:59';

$f = $see->html->form( $formSettings );

?>
<div class="col1"><div class="sectiontitle"><h2>Page details<?php echo(( $data['editError'] ) ? " - {$data['editError']}" : "" ); ?></h2></div>
<div class="columns">

<div class="column">
				<div class="section">
					<h2>Page information</h2>
					<div class="sg_input">
						<p>Page title</p>
						<p><?php $f->text( array( 'name' => 'title', 'value' => $page->title  ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>HTML title</p>
						<p><?php $f->text( array( 'name' => 'htmltitle', 'value' => $page->htmltitle, 'class' => 'count' ) ); ?> <span><span id="count">0</span> chars</span></p>
					</div>
				</div>
				<div class="section">
					<h2>Search engine optimisation</h2>
					<div class="sg_input">
						<p>Page description</p>
						<p><?php $f->textarea( array( 'name' => 'metadescription', 'rows' => 5, 'cols' => 38, 'value' => $page->metadescription ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Page keywords</p>
						<p><?php $f->textarea( array( 'name' => 'metakeywords', 'rows' => 5, 'cols' => 38, 'value' => $page->metakeywords ) ); ?></p>
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
							<p><?php $f->select( array( 'name' => 'template', 'value' => $page->template ), array( 'options' => $templates, 'optionValueOnly' => true ) ); ?></p>
						</div>
					</div>
				</div>

        <div class="adf">
          <h3>Commencement / expiry</h3>
					<div class="sg_input">
						<p>Commencement date</p>
						<p><?php $f->text( array( 'name' => 'commencement', 'id' => 'commencement', 'class' => 'datepicker', 'value' => $see->format->date( $page->commencement, "d M Y" ) ) ); ?>
							<?php $f->select( array( 'name' => 'commencementtime', 'class' => 'time', 'value' => $see->format->date( $page->commencement, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) ); ?><a href="#" class="cleardate"></a></p>
					</div>
					<div class="sg_input">
						<p>Expiry date</p>
						<p><?php $f->text( array( 'name' => 'expiry', 'id' => 'expiry', 'class' => 'datepicker', 'value' => $see->format->date( $page->expiry, "d M Y" ) ) ); ?>
							<?php $f->select( array( 'name' => 'expirytime', 'class' => 'time', 'value' => $see->format->date( $page->expiry, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) ); ?>
						 <a href="#" class="cleardate"></a></p>
					</div>
        
        </div>
        
        <div class="adf">
					<h3>Visibility</h3>
					<div class="sg_checkboxes">		
						<p><?php $f->checkbox( array( 'name' => 'hidefromnavigation', 'id' => 'hidefromnavigation', 'value' => (( $page->visibility >= 2 ) ? 1 : 0 ) ) ); ?></p>
						<p>Hide page from navigation</p>
					</div>
					<div class="sg_checkboxes">		
						<p><?php $f->checkbox( array( 'name' => 'hidefromsitemap', 'id' => 'hidefromsitemap', 'value' => (( $page->visibility == 3 ) ? 1 : 0 ) ) ); ?></p>
						<p>Hide page from sitemap</p>
					</div>
        </div>
        
				<div class="adf">
					<h3>Page URLs</h3>
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
	              echo "</div><div class=\"clear\"></div>";
	              echo "<a href=\"#\" class=\"addnewroute\">Add route</a>";
                echo "<script>var nextroute = {$routeCounter}; var routeHTML = '<div class=\"route\"><p><input type=\"text\" id=\"routeXXX\" value=\"\" name=\"routeXXX\"><span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleterouteXXX\"><span class=\"checkbox\"></span></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryrouteXXX\" ><span class=\"checkbox\"></span></span></p></div>'; var routeHTMLHead = '<div class=\"url url1\"><p>Secondary URLs</p></div><div class=\"url url2\"><p>Delete?</p></div><div class=\"url url3\"><p>Make Primary?</p></div><div class=\"clear\"></div>';</script>";
            ?>
					</div>
				</div>
        
				<div class="adf">
					<h3>Security</h3>
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
 
<hr /><p><?php $f->checkbox( array( 'name' => "security-cascade", 'id' => "security-cascade" ) ); ?> Update permissions on subpages</p>
 
					</div>
				</div>
        
<div class="adf">
  <h3>Redirect</h3>
  <div class="redirect">
    <a href="#" id="seecmsredirectlink">
    <?php if( $data['redirectDetails'] ) { echo "Currently redirecting to: ".(($data['redirectDetails']['name'])?$data['redirectDetails']['name']:$data['redirectDetails']['route']); } else { echo 'Select a link'; } ?>
    </a>
    <br />
    <a id="seecmsremoveredirect" href="#">
    <?php if( $data['redirectDetails'] ) { echo "Remove redirect"; } ?>
    </a>
    <?php $f->hidden( array( 'name' => 'redirect', 'id' => 'redirect', 'value' => $page->redirect ) ); ?>
  </div>
</div>
        
<?php

$adfs = SeeDB::find( 'adf', ' objecttype = ? && ( ( objectid = ? && `cascade` = ? ) || ( objectid IN ( '.$page->ascendants.",{$page->id}".' ) && `cascade` = ? ) || ( objectid IN ( '.$page->ascendants.' ) && `cascade` = ? && objectid != ? ) ) && ( theme = ? || theme = ? ) ', array( 'page', $page->id, 0, 1, 2, $page->id, '', $see->theme ) );
$cc = new SeeCMSContentController( $see, $see->SeeCMS->language );
foreach( $adfs as $adf ) {

  $exclude = explode( ",", $adf->exclude );
  if( !in_array( $page->id, $exclude ) ) {

    $cc->objectType = $r->objecttype;
    $cc->objectID = $r->objectid;
    
    $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'page', $page->id, $adf->id, $see->SeeCMS->language ) );

    echo '<div class="adf">';
    echo "<h3 id=\"editable{$adf->id}\" class=\"editcontent editcontentADF adfpopup\">{$adf->title}</h3>";
    echo $cc->makeEditPart( $adf->id, 'ADF', $content->content, 1, true );
    
    $adfpopup .= $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, true )."\r\n";
    
    echo '</div>';
  }
}

?>
        
        
			</div>
      
</div></div>


	<div class="col2">
		<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $page->id ) ); ?></div>
		<div class="editpage">
			<a class="editpage" href="<?php echo (( $route->route != '/' ) ? '/' : '' ).$route->route; ?>?preview=1">Preview/edit page</a>
		</div>
		<div class="support">
			<h2>Support</h2>
			<div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
		</div>
	</div>
  
<?php 

$f->close();

echo $adfpopup;

?>
<div class="selectseecmsredirectlink" id="selectseecmsredirectlink" title="Select link" style="display: none;">
<?php echo $data['linkSelector']; ?>
</div>
<div class="clear"></div>