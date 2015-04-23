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
          $_SESSION['seecms'][$this->see->siteID]['adminuser']['access'] = unserialize( $au->adminuserrole->config );
          $_SESSION['seecms'][$this->see->siteID]['adminuser']['cmsNavigation'] = unserialize( $au->adminuserrole->cmsnavigation );
          $this->see->redirect( "../" );
        }
      }
      
      if( !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
        $ret['errors']['email'] = 'Oops. Your login details are incorrect.';
      }
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
  
  public function loadEmail( $id ) {
  
    $au = SeeDB::load( 'adminuser', $id );
    
    return( $au->email );
  }
}