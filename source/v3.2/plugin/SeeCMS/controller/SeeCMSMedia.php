<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSMediaController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function load() {
  
    $data['media'] = SeeDB::load( 'media', (int)$_GET['id'] );
    
    $size = @getimagesize( "images/uploads/img-original-{$data['media']->id}.{$data['media']->type}" );
    $data['mediaDimensions'] = array( 'width' => (int)$size[0], 'height' => (int)$size[1] );
    
    return( $data );
  }
  
  public function loadForEdit() {
    
    return( $data );
  }
  
  public function create() {
  
    // Check if parent exists
    $mp = SeeDB::load( 'media', $_POST['parentid'] );
    
    if( ( $mp->id && $mp->isfolder ) || $_POST['parentid'] === '0' ) {
  
      $m = SeeDB::dispense( 'media' );
      
      $m->parentid = $_POST['parentid'];
      $m->isfolder = (int)$_POST['isfolder'];
      
      
      if( $m->isfolder ) {
      
        $m->name = $_POST['title'];
        $m->alt = '';
        $m->status = 1;
        $m->description = '';
        $m->type = '';
      
        SeeDB::store( $m );
        
        $ret['done'] = 1;
        $ret['data'] = $this->loadByFolder( $p->parentid );
        
        return( json_encode( $ret ) );
        
      } else {
      
        foreach( $_FILES as $fk => $fv ) {
        
          if( $fv['tmp_name'] ) {
          
            $extO = SeeFileController::getFileExtension( $fv['name'] );
            $ext = strtolower( $extO );
            
            // Reject if not an image or video
            $imgs = array( 'jpeg', 'jpg', 'png', 'gif' );
            $media = array( 'jpeg', 'jpg', 'png', 'gif' );
            if ( !in_array( $ext, $imgs ) && !in_array( $ext, $media ) ) {
              $error = 'Invalid format';
            } else {
            
              list($width, $height, $type, $attr) = getimagesize( $fv['tmp_name'] );

              $m->name = str_replace( ".{$extO}", "", $fv['name'] );
              $m->alt = $m->name;
              $m->description = $m->name;
              $m->status = 1;
              $m->type = $ext;
              
              SeeDB::store( $m );
                
              if ( in_array( $ext, $imgs ) ) {
              
                $image = new SeeImageController();
                $image->prepare( $fv['tmp_name'], "../{$this->see->publicFolder}/images/uploads/img-original-{$m->id}.{$ext}", 2000, 2000, $ext, true );
                $image->prepare( $fv['tmp_name'], "../{$this->see->publicFolder}/images/uploads/img-720-720-{$m->id}.{$ext}", 720, 720, $ext, true );
                $image->prepare( $fv['tmp_name'], "../{$this->see->publicFolder}/images/uploads/img-139-139-{$m->id}.{$ext}", 139, 139, $ext, false, true );
                
                $is = SeeDB::find( 'imagesize', ' theme = ? || theme = ? ORDER BY id ', array( '', $this->see->theme ) );
                foreach( $is as $iss ) {

                  if( $iss->mode == 'crop' ) {
                    $constrain = false;
                    $stretch = true;
                  } else if( $iss->mode == 'resize' ) {
                    $constrain = true;
                    $stretch = false;
                  }
                  
                  if( $iss->identifier ) {
                    $isid = $iss->identifier;
                  } else {
                    $isid = $iss->id;
                  }
                
                  $image->prepare( $fv['tmp_name'], "../{$this->see->publicFolder}/images/uploads/img-{$isid}-{$m->id}.{$ext}", $iss->width, $iss->height, $ext, $constrain, $stretch );
                }
                
              } else {
              
                // Video or swf
              }
            }
            unlink( $fv['tmp_name'] );
          }
        }
      }
    } else {
    
      $error = 'File could not be uploaded';
    }
    
    if( $error ) {

      header('HTTP/1.1 500 Internal Server Error');
      die( $error );
    } else {
    
      die('Done');
    }
  }
  
  public function update( $data, $errors, $settings ) {
  
    // Check permission
    $m = SeeDB::load( 'media', (int)$data['id'] );
    
    if( $m->id ) {
      $m->name = $data['name'];
      $m->alt = $data['alt'];
    }
    
    SeeDB::store( $m );
    $this->see->redirect( "?id={$m->id}" );
  }
  
  public function savefolder() {
  
    // Check permission
    $m = SeeDB::load( 'media', (int)$_POST['id'] );
    
    if( $m->id ) {
      $m->name = $_POST['title'];
      SeeDB::store( $m );
      return( $this->folderTree() );
    }
  }
  
  public function move( $id = 0, $at = '' ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    if( !$at ) {
      $at = $_POST['at'];
    }
  
    // Check if parent exists
    $mp = SeeDB::load( 'media', $at );
    
    if( ( $mp->id || $at === '0' ) && $mp->id != $id ) {
  
      $m = SeeDB::load( 'media', $id );
      
      if( $m->id ) {
      
        $m->parentid = $at;
        SeeDB::store( $m );
        
        return( json_encode( $this->loadForCMS() ) );
      }
    }
  }
  
  public function delete( $id = 0, $recursive = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
      $first = 1;
    }
  
    $m = SeeDB::load( 'media', $id );
    
    if( $m->id ) {
    
      if( $m->isfolder && $first ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] = $m->parentid;
      }
    
      foreach ( glob("../{$this->see->publicFolder}/images/uploads/img-*-{$m->id}.{$m->type}") as $file ) {
        unlink( $file );
      }

      $this->recursiveDelete( $m->id );
      
      SeeDB::trash( $m );
      
      $_POST['id'] = '';
    }
    
    if( $first ) {
      return( json_encode( $this->loadForCMS() ) );
    }
  }
  
  private function recursiveDelete( $parentID ) {

    $media = SeeDB::find( 'media', ' parentid = ? ', array( $parentID ) );
    foreach( $media as $m ) {
      $this->delete( $m->id, 1 );
    }
  }
  
  public function loadForCMS() {
  
    $data['folderTree'] = $this->folderTree();
    $data['media'] = $this->loadByFolder( $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] );
    
    return( $data );
  }
  
  public function folderTree( $parentID = 0, $level = 0 ) {
    
    $parentID = (int)$parentID;
    $mode = (( $_POST['mode'] ) ? $_POST['mode'] : 'default' );
    
    if( !$parentID && $mode == 'default' ) {
      $content = "<h3".((!$_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'])?' class="selected"':'')."><a href=\"#\" class=\"mediafolder\" id=\"folder0\">Media</a></h3>";
    }
    
    $folders = SeeDB::find( 'media', ' parentid = ? && isfolder = ? ORDER BY name ASC ', array( $parentID, 1 ) );
    foreach( $folders as $f ) {

      $ret = $this->folderTree( $f->id, $level+1 );

      if( $mode == 'default' ) {
        $class = 'nochild';
        $class .= (( $f->id == $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] ) ? ' selected' : '' );
        
        $content .= "<li class=\"{$class}\"><a href=\"#\" class=\"mediafolder\" id=\"folder{$f->id}\"><span class=\"name\">{$f->name}</span><span title=\"Move\" class=\"move\"></span><span  title=\"Edit\"class=\"viewedit\"></span><span title=\"Delete\" class=\"delete\"></span><span title=\"Move here\" class=\"target\"></span></a>";
        
        if( $ret ) {
          $content .= "<ul>".$ret."</ul>";
        }
        
        $content .= "</li>";
      } else if( $mode == 'option' ) {
      
        $content .= "<option id=\"folder{$f->id}\">".str_pad('', $level, '-', STR_PAD_LEFT)." {$f->name}</option>";
        
        if( $ret ) {
          $content .= $ret;
        }
      }
    }
    
    if( $mode == 'option' && !$parentID ) {
    
      $content = "<option id=\"folder0\">Media</option>".$content;
    }
    
    return( $content );
  }
  
  public function loadByFolder( $parentID = 0, $mode = 'admin' ) {
  
    $parentID = (int)$parentID;
    if( (int)$_POST['id'] ) {
      $m = SeeDB::load( 'media', (int)$_POST['id'] );
      if( $m->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
    }
    
    if( $_POST['mode'] ) {
      $mode = $_POST['mode'];
    }
    
    $media = SeeDB::find( 'media', ' parentid = ? && isfolder = ? ORDER BY name ', array( $parentID, 0 ) );
    foreach( $media as $m ) {
      
      if( $mode == 'selectimage' ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] = $parentID;
        $content .= "<a href=\"#\" class=\"image\" id=\"i{$m->id}\"><img src=\"/{$this->see->rootURL}images/uploads/img-139-139-{$m->id}.{$m->type}\" alt=\"\" title=\"{$m->name}\" /></a>";
      } else if( $mode == 'data' ) {
        $content[] = array( 'id' => $m->id, 'type' => $m->type, 'name' => $m->name );
      } else {
        $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] = $parentID;
        $content .= "<div class=\"thumb\"><div class=\"overlay\"><p>{$m->name}</p><p>{$m->type}</p><a href=\"#\" id=\"move{$m->id}\" class=\"move\">Move</a><a href=\"edit/?id={$m->id}\" class=\"viewedit\">View/Edit</a><a id=\"deletemedia-{$m->id}\" class=\"delete deletemedia\">Delete</a></div><img src=\"../../images/uploads/img-139-139-{$m->id}.{$m->type}\" alt=\"\" /></div>";
      }
    }
    
    $content = (( $content ) ? $content : '<p><strong>There\'s no media in this folder.</strong></p>' );
    
    return( $content );
  }
  
  public function loadMediaByFolder( $data = '' ) {
  
    $parentID = $data['parentID'];
    $mode = $data['mode'];
    
    return( $this->loadByFolder( $parentID, $mode ) );
  }
  
  public function adminSearch( $keyword ) {
  
    $media = SeeDB::find( 'media', ' isfolder = ? && ( name LIKE ? || alt LIKE ? ) ORDER BY name LIMIT 6 ', array( 0, "%{$keyword}%", "%{$keyword}%" ) );
    foreach( $media as $m ) {
      
      $mp = SeeDB::load( 'media', $m->parentid );
      $r[] = array( 'id' => $m->id, 'name' => $m->name, 'type' => $m->type, 'in' => (( $mp->name ) ? $mp->name : 'Root' ) );
    }
    
    return( $r );
  }
  
  public function selectimageOptions() {
  
    $data['imagesizes'] = $is = SeeDB::find( 'imagesize', ' theme = ? || theme = ? ORDER BY name ', array( '', $this->see->theme ) );
    
    return( $data );
  }
}