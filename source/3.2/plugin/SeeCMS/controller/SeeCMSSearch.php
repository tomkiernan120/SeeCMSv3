<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSSearchController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public static function search() {
  
    $c = SeeDB::find( 'content', ' content LIKE ? GROUP BY objecttype, objectid ', array( "%{$_GET['search']}%" ) );
    
    foreach( $c as $cr ) {
    
      $ob = SeeDB::load( $cr->objecttype, $cr->objectid );
      $r = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( $cr->objecttype, $cr->objectid, 1 ) );
      $content = substr( strip_tags( $cr->content ), 0, 200 )." ...";
      
      $data['searchresults'][] = array( 'title' => $ob->title, 'route' => '/'.$r->route, 'content' => $content );
    }
    
    return( $data );
  }
  
  public function adminSearch() {
  
    $search = trim( $_POST['search'] );
    
    $page = new SeeCMSPageController( $this->see );
    $pages = $page->adminSearch( $search );
    
    if( $pages ) {
      $data = '<div class="column pages"><h2>Pages</h2>';
      foreach( $pages as $d ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/page/edit/?id={$d['id']}\">{$d['title']}</a></h3><p>In section: <strong>{$d['in']}</strong></p></div>";
      }
      $data .= '</div>';
    }
    
    $post = new SeeCMSPostController( $this->see );
    $posts = $post->adminSearch( $search );
    
    if( $posts ) {
      $data .= '<div class="column posts"><h2>Posts</h2>';
      foreach( $posts as $d ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/post/edit/?id={$d['id']}\">{$d['title']}</a></h3><p>Post date: <strong>{$d['posted']}</strong></p></div>";
      }
      $data .= '</div>';
    }
    
    $media = new SeeCMSMediaController( $this->see );
    $medias = $media->adminSearch( $search );
    
    if( $medias ) {
      $data .= '<div class="column media"><h2>Media</h2>';
      foreach( $medias as $m ) {
        $data .= "<div class=\"result\"><img src=\"/{$this->see->rootURL}images/uploads/img-139-139-{$m['id']}.{$m['type']}\" alt=\"\" /><div class=\"title\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/media/edit/?id={$m['id']}\">{$m['name']}</a></h3><p>In folder: {$m['in']}</p></div><div class=\"clear\"></div><!--<div class=\"info\"><p>Size: x</p><p>Author: x</p><p>Uploaded: x</p></div>--></div>";
      }
      $data .= '</div>';
    }
    
    $download = new SeeCMSDownloadController( $this->see );
    $downloads = $download->adminSearch( $search );
    
    if( $downloads ) {
      $data .= '<div class="column downloads"><h2>Downloads</h2>';
      foreach( $downloads as $d ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/download/edit/?id={$d['id']}\"><img src=\"/{$this->see->rootURL}seecms/images/icons/{$d['type']}.png\" alt=\"\" />{$d['name']}</a></h3><p>In folder: {$d['in']}</p></div>";
      }
      $data .= '</div>';
    }
    
    if( $this->see->SeeCMS->additionalSearchObjects ) {
			
      foreach( $this->see->SeeCMS->additionalSearchObjects as $aso ) {
      
        $searchR = $this->see->plugins[$aso['plugin']]->{$aso['pluginMethod']}( $search );
        $data .= $searchR;
      }
    }
  
    return( $data );
  }
}