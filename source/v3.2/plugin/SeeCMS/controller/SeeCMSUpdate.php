<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSUpdateController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function update( $name ) {
  
    $version = SeeDB::findOne( 'setting', ' name = ? ', array( 'version' ) );
    if( !$version ) {
      $version = SeeDB::dispense( 'setting' );
      $version->name = 'version';
    }
    
    $data = json_decode( file_get_contents( "http://update.seecms.net/update/?v={$version->value}".(($_GET['apply'])?'&loadUpdateFile=1':'') ) );
    
    if( !is_object( $data ) ) {
      
      $data = "<p>There are no updates currently available.</p>";
    }
    
    if( $_GET['apply'] ) {
      if( is_object( $data ) ) {
        foreach( $data->release as $rK => $release ) {

          foreach( $data->release->{$rK}->update as $update ) {

            $updateType = $update->type;
            $updateData = json_decode( base64_decode( $update->data ) );
            $data->release->$rK->notes .= $this->{$update->type}( $updateData );
          }
        
          $data->release->$rK->notes .= "<p><strong>Update applied</strong></p>";
          $version->value = $release->version;
          SeeDB::store( $version );
        }
      }
    }
    
    return( $data );
  }
  
  private function file( $data ) {
    if( $this->see->publicFolder ) {
      $data->filepath = str_replace( "public/", "{$data->filepath}/", $data->filepath );
    }
    file_put_contents( "../{$data->filepath}{$data->filename}", base64_decode( $data->content ) );
  }
  
  private function mysql( $data ) {
    
    try {
      SeeDB::exec( base64_decode( $data->content ) );
    } catch(Exception $e) {
    }
  }
}