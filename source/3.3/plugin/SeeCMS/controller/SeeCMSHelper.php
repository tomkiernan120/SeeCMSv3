<?php

class SeeCMSHelperController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function breadcrumb() {
  
    return( $this->see->SeeCMS->ascendants );
  }
  
  public function breadcrumbHTML( $settings ) {
  
    $message = (( isset( $settings['message'] ) ) ? $settings['message'] : 'You are here: ' );
    $divider = (( isset( $settings['divider'] ) ) ? $settings['divider'] : ' &gt; ' );
    $home = (( isset( $settings['home'] ) ) ? $settings['home'] : 'Home' );
  
    $bc = $this->breadcrumb();
    
    $html = "<p>{$message}";
    
    if( is_array( $bc ) ) {
      foreach( $bc as $p ) {
      
        if( (int)$p ) {
        
          $page = SeeDB::load( 'page', $p );
          if( $page->id ) {
            $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryRoute = ? ', array( 'page', $page->id, 1 ) );
            
            if( ( $this->see->SeeCMS->object->id == $page->id && $this->see->SeeCMS->object->getMeta('type') == 'page' ) || $this->see->SeeCMS->object->getMeta('type') == 'post' ) {
              $html .= "{$divider}<span class=\"selected\">{$page->title}</span>";
            } else {
              $html .= "{$divider}<a href=\"/{$route->route}\">{$page->title}</a>";
            }
          }
          
        } else if ( $p == 0 ) {
          
            $html .= "<a href=\"/\">{$home}</a>";
          }
      }
    }
    
    $html .= "</p>";
    
    return( $html );
  }
}