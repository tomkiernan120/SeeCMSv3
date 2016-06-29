<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSAdminAuthenticationController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function checkAccess( $currentCMSSection, $context = null, $redirectoOnFail = true ) {
  
    // Check user has access to requested bit
    $accessToSection = (($currentCMSSection)?$currentCMSSection:'ROOT');
    
    if( isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['access'][$accessToSection] ) ) {
      $access = $_SESSION['seecms'][$this->see->siteID]['adminuser']['access'][$accessToSection];
      $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] = $access;
      
      if( !$access ) {
        if( $redirectoOnFail ) {
          $this->see->redirect( "/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/" );
        } else {
          return( false );
        }
      } else {
        return( $access );
      }
    }
  }
  
  public function checkContextAccess( $objectType, $objectID ) {
    
    if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] < 5 ) {
        
      $accessLevel = $_SESSION['seecms'][$this->see->siteID]['adminuser']['accessadvanced'][$objectType][$objectID];
      
    } else {
      
      $accessLevel = 50;
    }
    
    return( $accessLevel );
  }
  
  public function login( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      $au = SeeDB::findOne( 'adminuser', ' email = ? ', array( $data['email'] ) );
      if( $au ) {
      
        if( $au->passwordformat != 'hash' ) {
        
          $password = $this->see->security->decAES256( $au->password );
          $passwordCheck = (($password==$data['password'])?true:false);
        } else {
          
          $passwordCheck = password_verify($data['password'],$au->password);
        }
        
        if( $passwordCheck ) {
        
          $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] = $au->id;
          $_SESSION['seecms'][$this->see->siteID]['adminuser']['name'] = $au->name;
          $_SESSION['seecms'][$this->see->siteID]['adminuser']['email'] = $au->email;
          
          if( $au->adminuserrole->config[0] == 'a' ) { // Serialized array
          
            $_SESSION['seecms'][$this->see->siteID]['adminuser']['access'] = unserialize( $au->adminuserrole->config );
            $_SESSION['seecms'][$this->see->siteID]['adminuser']['cmsNavigation'] = unserialize( $au->adminuserrole->cmsnavigation );
            
          } else { // json
            
            $_SESSION['seecms'][$this->see->siteID]['adminuser']['access'] = json_decode( $au->adminuserrole->config, true );
            $_SESSION['seecms'][$this->see->siteID]['adminuser']['cmsNavigation'] = json_decode( $au->adminuserrole->cmsnavigation, true );
          }
          
          if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
            
            foreach( $au->sharedAdminusergroup as $ag ) {
              
              foreach( $ag->ownAdminusergrouppermission as $augp ) {
                
                $_SESSION['seecms'][$this->see->siteID]['adminuser']['accessadvanced'][$augp->objecttype][$augp->objectid] = $augp->accesslevel;
              }
            }
          }
          
          if( $data['remotelogin'] ) {
            
            ob_clean();
            die( 'Done' );
          } else {
            
            $this->see->redirect( "../" );
          }
        }
      }
      
      if( $data['remotelogin'] ) {
        
        ob_clean();
        die( 'Error' );
      } else if( !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
        $ret['errors']['email'] = 'Oops. Your login details are incorrect.';
      }
    } else if( $data['remotelogin'] ) {
      
      ob_clean();
      die( 'Error' );
    }
    
    return( $ret );
  }
  
  public function logout() {
  
    $_SESSION['seecms'][$this->see->siteID]['adminuser'] = '';
    $this->see->redirect( './' );
  }
  
  public function update( $data, $id = 0 ) {
  
    if( !$id ) {
    
      $id = (int)$data['id'];
    }
  
    $au = SeeDB::load( 'adminuser', $id );
    
    $au->name = $data['name'];
    $au->email = $data['email'];
    $au->adminuserrole_id = $data['level'];
    
    if( $data['password'] ) {
      $au->password = password_hash( $data['password'], PASSWORD_BCRYPT );
      $au->passwordformat = 'hash';
    }
    
    SeeDB::store( $au );
  }
  
  public function loadForEdit() {
  
    $data['user'] = $this->load();
    $roles = SeeDB::findAll( 'adminuserrole', ' ORDER BY name ' );
    foreach( $roles as $r ) {
    
      $data['roles'][$r->id] = $r->name;
    }
    
    return( $data );
  }
  
  public function load() {
  
    $au = SeeDB::load( 'adminuser', (int)$_GET['id'] );
    
    return( $au );
  }
  
  public function loadAll() {
  
    $au = SeeDB::findAll( 'adminuser', ' ORDER BY name ');
    
    return( $au );
  }
  
  public function delete() {
  
    $au = SeeDB::load( 'adminuser', $_POST['id'] );
    SeeDB::trash( $au );
    echo 'Done';
  }
  
  public function deleteGroup() {
  
    $ag = SeeDB::load( 'adminusergroup', $_POST['id'] );
    SeeDB::trash( $ag );
    echo 'Done';
  }
  
  public function loadEmail( $id ) {
  
    $au = SeeDB::load( 'adminuser', $id );
    
    return( $au->email );
  }
  
  public function loadAllGroups() {
  
    $ag = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ');
    
    return( $ag );
  }  
  
  public function loadGroupForEdit( $id = 0 ) {
  
    if( !$id ) {
      $id = $_GET['id'];
    }
  
    $d['group'] = SeeDB::load( 'adminusergroup', $id );
    $d['users'] = $this->loadAll();
    
    return( $d );
  }
  
  public function updateGroup( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      $g = SeeDB::load( 'adminusergroup', $data['id'] );
      $g->name = $data['name'];
      
      SeeDB::store( $g );
      
      $g->sharedAdminuser = array();
      
      foreach( $data as $dK => $dV ) {
      
        if( substr( $dK, 0, 16 ) == 'seecmsadminuser-' ) {
        
          $au = SeeDB::load( 'adminuser', substr( $dK, 16 ) );
          $g->sharedAdminuser[] = $au;
        }
      }
      
      SeeDB::store( $g );
      $this->see->redirect( "./?id={$g->id}" );
    }
    
    return( $errors );
  } 
  
  public static function setPermission( $objectID, $objectType, $groups ) {
  
    // Delete old permissions
    SeeDB::exec( " DELETE FROM adminusergrouppermission WHERE objectid = {$objectID} && objecttype = '{$objectType}' " );
  
    if( is_array( $groups ) ) {
      foreach( $groups as $gID => $gAL ) {
        // Insert permission
        $gp = SeeDB::dispense( 'adminusergrouppermission' );
        $gp->objectid = $objectID;
        $gp->objecttype = strtolower( $objectType );
        $gp->adminusergroup_id = $gID;
        $gp->accesslevel = $gAL;
        SeeDB::store( $gp );
      }
    }
  }
  
  public static function cascadePermission( $objectID, $objectType, $groups ) {
  
    $obs = SeeDB::find( $objectType, ' parentid = ? && deleted = ? ', array( $objectID, '0000-00-00 00:00:00' ) );
    
    if( is_array( $obs ) ) {
      foreach( $obs as $o ) {
        // Insert permission
        SeeCMSAdminAuthenticationController::setPermission( $o->id, $objectType, $groups );
        // Cascade again
        SeeCMSAdminAuthenticationController::cascadePermission( $o->id, $objectType, $groups );
      }
    }
  }
  
}