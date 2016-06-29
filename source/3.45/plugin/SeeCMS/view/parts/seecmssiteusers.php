<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 
echo '<div class="col1"><div class="sectiontitle"><h2>Site users</h2></div><div class="columns"><div class="column snav">';

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/siteusers/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

echo '</div><div class="column columnwide"><table class="order users"><thead>';

echo '<tr>';

foreach( $data['fields'] as $f ) {
  
  echo "<th>{$f->title}</th>";
}

echo '</tr></thead><tbody>';

foreach( $data['au'] as $wu ) {

  echo "<tr>";
  
  foreach( $data['fields'] as $f ) {
    
    $fd = (( $f->type == 'adf' ) ? $wu->adfs->{$f->field} : $wu->{$f->field} );
    
    if( $f->format ) {
      
      $fd = $see->format->{$f->format}( $fd, $f->formatParameters );
    }
    
    echo "<td>{$fd}</td>";
  }
  
  echo "<td><a href=\"editusers/?id={$wu->id}\">Edit</a></td><td class=\"delete\"><a class=\"delete\" data-siteuserid=\"{$wu->id}\"></a></td>";
  
  if( $wu->activation ) {
    echo "<td class=\"activate\"><a class=\"activate\" data-siteuserid=\"{$wu->id}\" data-siteuseremail=\"{$wu->email}\" data-siteuseractivation=\"{$wu->activation}\"></a></td>";
  } else {
    echo "<td class=\"deactivate\"><a class=\"deactivate\" data-siteuserid=\"{$wu->id}\" data-siteuseremail=\"{$wu->email}\" ></a></td>";
  }
  
  echo "</tr>";
}

?>
</tbody>
</table>
</div>
</div>

<div class="clear"></div>
</div>

<div class="col2">
<div class="createpage">
<a class="createuser" href="editusers/">Create new user</a>
</div>
<div class="support">
<h2>Support</h2> 
<div class="supportinfo">
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>
<div id="deleteuserpopup" title="Delete user?"></div>