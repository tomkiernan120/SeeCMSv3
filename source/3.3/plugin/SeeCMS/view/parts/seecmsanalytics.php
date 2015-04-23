<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
?>	
  
  <div class="col1">
		<div class="statsheader">
			<h2><?php echo "{$data['displayMonth']} {$data['displayYear']}"; ?> - Overview</h2>
			<!--<a href="#" class="overview">View all stats</a>-->

<?php

$formSettings = array();

$f = $see->html->form( $formSettings );

$f->select( array( 'name' => 'month', 'value' => $data['month'] ), array( 'options' => SeeHelperController::monthRange(1,12) ) );
$f->select( array( 'name' => 'year', 'value' => $data['year'] ), array( 'options' => SeeHelperController::yearRange(2014,date("Y")), 'optionValueOnly' => true ) );

$f->submit( array( 'value' => 'Go', 'id' => 'submit' ) );

$f->close();

?>

		</div>
		<div class="stats">

			<div class="piecharts">
				<h2>Analytics at a glance</h2>
        
				<div class="pie"><div class="pieinner"><h2><?php echo $data['visitors']; ?></h2><p>Visitors</p></div></div>
        <div class="pie"><div class="pieinner"><h2><?php echo $data['uniqueVisitors']; ?></h2><p>Unique visitors</p></div></div>
        <div class="pie"><div class="pieinner"><h2><?php echo $data['averageVisit']; ?></h2><p>Average visit time</p></div></div>
        
        <!--
				<div class="pie" style="
			background-image: linear-gradient(126deg, transparent 50%, #e8e8e8 50%), 
			linear-gradient(90deg, #e8e8e8 50%, transparent 50%);"><div class="pieinner"><h2>+350</h2><p>Visitors*</p></div></div>
			<div class="pie" style="
			background-image: linear-gradient(180deg, transparent 50%, #c2d347 50%),
      linear-gradient(90deg, #e8e8e8 50%, transparent 50%);"><div class="pieinner"><h2>+75</h2><p>Unique visitors*</p></div></div>
      <div class="pie" style="
			background-image: linear-gradient(234deg, transparent 50%, #c2d347 50%),
      linear-gradient(90deg, #e8e8e8 50%, transparent 50%);"><div class="pieinner"><h2>+32mins</h2><p>Average visit time*</p></div></div>

       	0-50% 3.6x% + 90
       	> 50% 3.6x% - 90 switch transparent #e8e8e8 to transparent #c2d347
       	100% background none
       -->
       <!--<p class="disclaimer">*vs this time last month</p>-->
			</div>
      <!--
			<div class="visitors">
				<h2>Visitors this month</h2>
				<div class="bars">

					<div class="bar" style="height: 100%">
						<div class="angle"></div>
						<p>13,045</p>
					</div>
					<div class="bar" style="height: 10.11%">
						<div class="angle"></div>
						<p>1,320</p>
					</div>
				</div>
				<div class="titles">
					<div class="title"><p>Visitors</p></div>
					<div class="title"><p>Unique visitors</p></div>
				</div>
			</div>
      -->
			<div class="clear"></div>
			
		</div>
		<hr/>
		<h2>Visits by day of the month</h2>
    <table class="stripey">
<?php

foreach( $data['visitsByDay'] as $d => $v ) {

  echo "<tr><th>{$d} {$data['displayMonth']} {$data['displayYear']}</th><td>{$v}</td></tr>";
}

?>
    </table>
<hr/>
<h2>Most popular content</h2>
<table class="stripey">
<?php

foreach( $data['contentViews'] as $k => $v ) {

  $ob = explode( '-', $k );
  $o = SeeDB::load( $ob[0], $ob[1] );
  $name = (( $o->name ) ? $o->name : $o->title );
  echo "<tr><th>{$name}</th><td>{$v}</td></tr>";
}

?>
</table>
<hr/>
<h2>Visits by browser</h2>
<div class="stats">
<div class="barfull">
<div class="bars">

<?php

$left = 100/count( $data['visitsByBrowserPercent'] );
$count = 0;

foreach( $data['visitsByBrowserPercent'] as $bsk => $bsp ) {
	//$bspheight = $bsp*2;
  $nLeft = $left*$count;
  echo "<div class=\"bar\" style=\"height: {$bsp}%; left: {$nLeft}%\"><div class=\"angle\"></div><p>{$bsp}%</p><h3>{$bsk}</h3></div>";
  $count++;
}

?>

</div>
</div>
</div>

		
	</div>
	<div class="col2">
		<div class="support">
			<h2>Support</h2>
			<div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
		</div>
	</div>