<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSDownloadController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function load() {
  
    $d = SeeDB::load( 'download', (int)$_GET['id'] );
    
    return( $d );
  }
  
  public function loadForEdit() {
    
    $data['download'] = $this->load();
    $data['download']->filesize = SeeFileController::filesize( filesize( "../custom/files/download-{$data['download']->id}.{$data['download']->type}" ) );
    
    $data['userGroups'] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    
    foreach( $data['userGroups'] as $ug ) {
    
      foreach( $ug->ownWebsiteusergrouppermission as $wugp ) {
     
        if( $wugp->objecttype == 'download' && $wugp->objectid == $data['download']->id ) {
          $data['userGroupPermission'][$ug->id] = 1;
        }
      }
    }
    
    $categories = SeeDB::find( 'category', ' objecttype = ? ORDER BY name ', array( 'download' ) );
    
    foreach( $categories as $c ) {
    
      $data['category'][$c->id] = $c->name; 
    }
    
    return( $data );
  }
  
  public function create() {
  
    // Check if parent exists
    $dp = SeeDB::load( 'download', $_POST['parentid'] );
    
    if( ( $dp->id && $dp->isfolder ) || $_POST['parentid'] === '0' ) {
    
      $d = SeeDB::dispense( 'download' );
      
      $d->parentid = $_POST['parentid'];
      $d->isfolder = (int)$_POST['isfolder'];
      
      if( $d->isfolder ) {
      
        $d->name = $_POST['title'];
        $d->type = '';
        $d->status = 1;
        $d->description = '';
      
        SeeDB::store( $d );
      } else {
      
        foreach( $_FILES as $fk => $fv ) {
        
          if( $fv['tmp_name'] ) {
          
            $ext = SeeFileController::getFileExtension( $fv['name'] );
            
            // Reject if an unsafe file
            $invalidformats = array( 'ashx', 'asmx', 'asp', 'aspx', 'axd', 'cer', 'config', 'htaccess', 'jsp', 'php', 'rem', 'rules', 'shtm', 'shtml', 'soap', 'stm', 'xoml' );
            if ( in_array( $ext, $invalidformats ) ) {
              $error = 'Invalid format';
            } else {

              $d->name = str_replace( ".{$ext}", "", $fv['name'] );
              $d->description = $d->name;
              $d->status = (((int)$this->see->SeeCMS->config['defaultDocumentStatus'])?1:0);
              $d->type = strtolower( $ext );
              
              SeeDB::store( $d );
                
              move_uploaded_file( $fv['tmp_name'], "../custom/files/download-{$d->id}.{$ext}" );
            }
          }
        }
      }
      
      if( !$error ) {
      
        $parentGroups = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'download', $dp->id ) );
        
        foreach( $parentGroups as $pg ) {
          $groupsToAdd[] = $pg->websiteusergroup_id;
        }
        
        SeeCMSWebsiteUserController::setPermission( $d->id, 'download', $groupsToAdd );
        
        if( $_POST['doFallback'] ) {
          $this->see->redirect('../downloads/');
        }
        
        if( $_POST['return'] ) {
          
          $ret['done'] = 1;
          $ret['id']   = $d->id;
          $ret['type'] = $d->type;
          return( json_encode( $ret ) );
          
        } else {
        
          die( $this->folderTree() );
        }
      }
      
    } else {
    
      header('HTTP/1.1 500 Internal Server Error');
      die( 'File could not be uploaded' );
    }
  }
  
  public function update( $data, $errors, $settings ) {
  
    // Check permission
    
    $d = SeeDB::load( 'download', (int)$data['id'] );
    
    if( $d->name ) {
      
      foreach( $data as $dk => $dv ) {
      
        if( substr( $dk, 0, 15 ) == 'security-group-' ) {
        
          if( !$data['security-allUserAccess'] ) {
            $groups[] = substr( $dk, 15 );
          }
        }
      }
      
      SeeCMSWebsiteUserController::setPermission( $d->id, 'download', $groups );
      
      $d->name = $data['name'];
      $d->description = $data['description'];
      
      $d->sharedCategory = [];
      
      foreach( $data as $dk => $dv ) {
      
        if( substr( $dk, 0, 9 ) == 'category_' ) {
        
          $c = SeeDB::load( 'category', substr( $dk, 9 ) );
          $d->sharedCategory[] = $c;
        }
      }
      
      SeeDB::store( $d );
    }
    
    
    $this->see->redirect( "./?id={$d->id}" );
  }
  
  public function savefolder() {
  
    // Check permission
    $d = SeeDB::load( 'download', (int)$_POST['id'] );
    
    if( $d->id ) {
      $d->name = $_POST['title'];
      SeeDB::store( $d );
      
      $data = array();
      parse_str($_POST['forms'], $data);
      
      foreach( $data as $dk => $dv ) {
      
        if( substr( $dk, 0, 15 ) == 'security-group-' ) {
        
          if( !$data['security-allUserAccess'] ) {
            $groups[] = substr( $dk, 15 );
          }
        }
      }
      
      SeeCMSWebsiteUserController::setPermission( $d->id, 'download', $groups );
      if( $data['security-cascade'] ) {
        SeeCMSWebsiteUserController::cascadePermission( $d->id, 'download', $groups );
      }
      
      return( $this->folderTree() );
    }
  }
  
  public function status( $id = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    $d = SeeDB::load( 'download', $id );
    $d->status = (( $d->status ) ? 0 : 1 );
    SeeDB::store( $d );
    
    $ret['done'] = 1;
    $ret['data'] = $this->loadByFolder( $d->parentid );
    
    return( json_encode( $ret ) );
  }
  
  public function move( $id = 0, $at = '' ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    if( !$at ) {
      $at = $_POST['at'];
    }
  
    // Check if parent exists
    $dp = SeeDB::load( 'download', $at );
    
    if( ( $dp->id || $at === '0' ) && $dp->id != $id ) {
  
      $d = SeeDB::load( 'download', $id );
      
      if( $d->id ) {
      
        $d->parentid = $at;
        SeeDB::store( $d );
        
        return( json_encode( $this->loadForCMS() ) );
      }
    }
  }
  
  public function delete( $id = 0, $recursive = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
      $first = 1;
    }
  
    $d = SeeDB::load( 'download', $id );
    
    if( $d->id ) {
    
      if( $d->isfolder && $first ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] = $d->parentid;
      }
    
      @unlink( "../custom/files/download-{$d->id}.{$d->type}" );

      $this->recursiveDelete( $d->id );
      
      SeeDB::trash( $d );
      
      $_POST['id'] = '';
    }
    
    if( $first ) {
      return( json_encode( $this->loadForCMS() ) );
    }
  }
  
  private function recursiveDelete( $parentID ) {

    $downloads = SeeDB::find( 'download', ' parentid = ? ', array( $parentID ) );
    foreach( $downloads as $d ) {
      $this->delete( $d->id, 1 );
    }
  }
  
  public function loadForCMS() {
  
    $data['userGroups'] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    $data['folderTree'] = $this->folderTree( 0, $data['userGroups'] );
    $data['downloads'] = $this->loadByFolder( $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] );
    
    return( $data );
  }
  
  public function folderTree( $parentID = 0, $userGroups = null, $perms = null ) {
    
    $parentID = (int)$parentID;
    
    if( !$parentID ) {
      $content = "<li".((!$_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'])?' class="selected"':'')."><a href=\"#\" class=\"downloadfolder\" id=\"folder0\">Root</a></li>";
    }

    if( !$userGroups ) {
      $userGroups = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    }

    if( !isset( $perms ) ) {
      $perms = array();
      
      foreach( $userGroups as $ug ) {
        foreach( $ug->withCondition( ' objecttype = ? ', array( 'download' ) )->ownWebsiteusergrouppermission as $wugp ) {
          
          $perms[ $wugp->objectid ] .= $ug->id.",";
        }
      }
    }
    
    $folders = SeeDB::find( 'download', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY name ASC ', array( $parentID, '0000-00-00 00:00:00', 1 ) );
    foreach( $folders as $f ) {
      
      $ret = $this->folderTree( $f->id );
      $class = (( $ret ) ? 'child' : 'nochild' );
      $class .= (( $f->id == $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] ) ? ' selected' : '' );
      
      $content .= "<li class=\"{$class}\"><a href=\"#\" class=\"downloadfolder\" id=\"folder{$f->id}\"><span class=\"expand\"></span> <span class=\"name\">{$f->name}</span>";
      
      $perm = trim( $perms[ $f->id ], ',' );

      if( $perm ) {
        $content .= "<span title=\"Secure\" class=\"secure\"></span>";
      }
      
      $content .= "<span title=\"Move\" class=\"move\"></span><span title=\"Edit\" data-permission=\"{$perm}\" class=\"viewedit\"></span><span title=\"Delete\" class=\"delete\"></span><span title=\"Move here\" class=\"target\"></span></a>";
      
      if( $ret ) {
        $content .= "<ul>".$ret."</ul>";
      }
      
      $o .= "</li>";
      
    }
    
    return( $content );
  }
  
  public function downloadTreeSimple( $parentID = 0, $recurse = 0 ) {
  
    $parentID = (int)$parentID;
    
    $downloads = SeeDB::find( 'download', ' parentid = ? && deleted = ? ORDER BY isfolder DESC, name ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $downloads as $d ) {
      
      $ret = $this->downloadTreeSimple( $d->id, 1 );
      
      if( $d->isfolder ) {
        $content .= "<li class=\"folder\"><a href=\"#\">{$d->name}</a>";
      } else {
        $content .= "<li><a href=\"#\" id=\"download-{$d->id}\" class=\"file\"><img src=\"/seecms/images/icons/{$d->type}.png\" alt=\"\" />{$d->name}</a>";
      }
      
      if( $ret ) {
        $content .= "<ul>{$ret}</ul>";
      }
      
      $content .= "</li>";
    }
    
    if( !$recurse ) {
      $content = "<ul>{$content}</ul>";
    }
    
    return( $content );
  }
  
  public function downloadFolderArray( $parentID = 0, $d = array(), $level = 0, $etitle = '' ) {
    
    $parentID = (int)$parentID;
    
    $downloads = SeeDB::find( 'download', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY  name ASC ', array( $parentID, '0000-00-00 00:00:00', 1 ) );
    foreach( $downloads as $download ) {
    
      $title = $etitle.(($level)?' > ':'').$download->name;
      $d[$download->id] = $title;
      $d = $this->downloadFolderArray( $download->id, $d, $level+1, $title );
    }
    
    return( $d );
  }
  
  public function loadByFolder( $parentID = 0 ) {
  
    $parentID = (int)$parentID;
    if( (int)$_POST['id'] ) {
      $d = SeeDB::load( 'download', (int)$_POST['id'] );
      if( $d->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
    }
    
    $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] = $parentID;
    
    $content .= "<ul>";
    
    $downloads = SeeDB::find( 'download', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY name ', array( $parentID, '0000-00-00 00:00:00', 0 ) );
    
    foreach( $downloads as $d ) {
      
      $content .= "<li><div class=\"page\"><a class=\"name\" href=\"../download/edit/?id={$d->id}\">{$d->name}</a><a class=\"date\" href=\"#\">".$this->see->format->date( $d->uploaded, "d.m.Y" )."</a><a class=\"icon {$d->type}\" title=\".{$d->type}\" href=\"#\"></a>";
      $content .= (( $d->status ) ? "<a class=\"published toggledownloadstatus\" target=\"Suppress\" id=\"status{$d->id}\"></a>" : "<a class=\"notpublished toggledownloadstatus\" title=\"Publish\" id=\"status{$d->id}\"></a>" );
      //<a class=\"clock\" href=\"#\"></a>
      $content .= "<a class=\"move\" title=\"Move\" id=\"move{$d->id}\" href=\"#\"></a>";

      $p = SeeDB::findOne( 'websiteusergrouppermission', ' objectid = ? && objectType = ? ', array( $d->id, 'download' ) );
      if( $p ) {
        $content .= "<a class=\"secure\" title=\"Secure\" id=\"secure{$d->id}\" href=\"#\"></a>";
      }
      
      $content .= "<a class=\"delete\" title=\"Delete\" id=\"deletedoc-{$d->id}\" href=\"#\"></a></div></li>";
      
    }
    
    $content .= "</ul>";
    
    $content = (( $content != "<ul></ul>" ) ? $content : '<p><strong>There are no downloads in this folder.</strong></p>' );
    
    return( $content );
  }
  
  public function adminSearch( $keyword ) {
  
    $downloads = SeeDB::find( 'download', ' isfolder = ? && ( name LIKE ? || description LIKE ? ) ORDER BY name LIMIT 10 ', array( 0, "%{$keyword}%", "%{$keyword}%" ) );
    foreach( $downloads as $d ) {
      
      $dp = SeeDB::load( 'download', $d->parentid );
      $r[] = array( 'id' => $d->id, 'name' => $d->name, 'type' => $d->type, 'in' => (( $dp->name ) ? $dp->name : 'Root' ) );
    }
    
    return( $r );
  }
  
  public function download() {
    
    
    $f = $this->load();
    
    if( ( !$f->status && !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) ) {
      if( !$_GET['preview'] ) {
        SeeRouteController::http404();
      }
    }
      
    // Check website user permission
    $access = true;
    if( !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
      $wugp = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'download', $_GET['id'] ) );
      if( count( $wugp ) ) {
        $access = false;
        if( (int)$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
          foreach( $wugp as $w ) {
            if( $w->websiteusergroup->sharedWebsiteuser[$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id']]->id == $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
              $access = true;
            }
          }
        }
      }
    }
  
    if( !$access ) {
      if( $this->see->SeeCMS->config['websiteUserLoginPage'] ) {
        if( $this->see->SeeCMS->config['websiteUserLoginPage'][0] == '/' ) {
          $redirect = substr_replace( $this->see->SeeCMS->config['websiteUserLoginPage'], '/'.$this->see->rootURL, 0, 1 );
        }
        $_SESSION['restrictedRouteRequest'] = '/'.SeeRouteController::getCurrentRoute()."?id=".$_GET['id'];
        $this->see->redirect( $redirect );
      }
      die( 'Restricted' );
    }
      
    if( $_GET['id'] ) {
      SeeCMSAnalyticsController::logVisit( 'download', $f->id, $this->see->siteID );
      SeeFileController::passthrough( array( 'name' => "{$f->name}.{$f->type}", 'path' => "../custom/files/download-{$f->id}.{$f->type}" ), (($this->see->SeeCMS->config['inlineFiles'])?true:false) );
    }
  }  
}