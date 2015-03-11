<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSController {

  var $see;
  
  var $config;
  
  var $content;
  var $adminauth;
  
  var $language;
  var $cmsRoot;
  
  var $routes;
  
  var $ascendants;
  var $object;
  var $supportMessage;
  var $editContent;
  var $editSettings;
  
  var $redirect;
  
  var $editable;
  
  public function __construct( $see, $config, $install = false ) {
  
    $this->see = $see;
    $this->config = $config;
    
    $this->redirect = $_SESSION['seecms'][$this->see->siteID]['redirect'];

    $this->editContent = array( "Edit<br />content", "Back to live site" );
    $this->editSettings = "Page settings";
    
    // Reset any redirect stuff
    unset( $_SESSION['seecms'][$this->see->siteID]['redirect'] );
    
    $this->routes = array( 'Pages', 'Posts', 'Media', 'Downloads', 'Site users', 'Admin', 'Analytics', 'Add ons' );
    
    if( $config['cmsRoot'] ) {
      $this->cmsRoot = $config['cmsRoot'];
    } else {
      $this->cmsRoot = 'cms/';
    }
    
    if( $config['language'] ) {
      $this->language = $config['language'];
    } else {
      $this->language = 'en';
    }

    $this->supportMessage = $config['supportMessage'];
    
    $this->content = new SeeCMSContentController( $see, $this->language );
    $this->adminauth = new SeeCMSAdminAuthenticationController( $this->see );
    
    if( $install ) {
    
      $this->install();
    }
  }
  
  public function routeManager( $r, $type ) {
  
    // Logout if necessary
    if( $_GET['seecmsLogout'] ) {
      $this->adminauth->logout();
    }
  
    if( $type == 'Dynamic' ) {
      $route = $this->dynamicRouteManager( $r );
    } else {
      $route = $this->staticRouteManager( $r );
    }
    
    return( $route );
  }
  
  private function dynamicRouteManager( $r ) {
  
    if( !$r->primaryroute ) {
      $actualRoute = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( $r->objecttype, $r->objectid, 1 ) );
      if( $actualRoute ) {
        $this->see->redirect( "/{$this->see->rootURL}{$actualRoute->route}", 301 );
      }
    }
    
    if( $this->see->customContentLoading[ $r->objecttype ] ) {
    
      $data = $this->see->plugins[ $this->see->customContentLoading[ $r->objecttype ][ 'plugin' ] ]->{$this->see->customContentLoading[ $r->objecttype ][ 'method' ]}( $r );
      $route = $data['route'];
      $this->content->objectType = $data['objectType'];
      $this->content->objectID = $data['objectID'];
      $this->editContent = $data['objectEditContent'];
      $this->editSettings = $data['objectEditSettings'];
      
      $this->editable = (( $this->adminauth->checkAccess( $data['editableAction'], null, false ) ) ? 1 : 0 );
    
    } else {
    
      $ob = SeeDB::load( $r->objecttype, $r->objectid );
      $this->object = $ob;
      $this->object->title = (( $this->object->title ) ? $this->object->title : $this->object->name );
      $this->object->htmltitle = (( $this->object->htmltitle ) ? $this->object->htmltitle : $this->see->siteTitle." - ".$this->object->title );
      
      // Check website user permission
      $access = true;
      if( !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
        $wugp = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( $r->objecttype, $r->objectid ) );
        if( count( $wugp ) ) {
          $access = false;
          if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {
            foreach( $wugp as $w ) {
              if( $w->websiteusergroup->sharedWebsiteuser[$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id']]->id == $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
                $access = true;
              }
            }
          }
        }
      }
    
      if( !$access ) {
        if( $this->config['websiteUserLoginPage'] ) {
          if( $this->config['websiteUserLoginPage'][0] == '/' ) {
            $redirect = substr_replace( $this->config['websiteUserLoginPage'], '/'.$this->see->rootURL, 0, 1 );
          }
          $_SESSION['restrictedRouteRequest'] = '/'.SeeRouteController::getCurrentRoute();
          
          $this->see->redirect( $redirect );
        }
        die( 'Restricted' );
      } else {
      
        if( $this->config['websiteUserLoginPage'][0] == '/' ) {
          $lp = substr_replace( $this->config['websiteUserLoginPage'], $this->see->rootURL, 0, 1 );
        }
        if( $lp != SeeRouteController::getCurrentRoute() ) {
          unset( $_SESSION['restrictedRouteRequest'] );
        }
      }
      
        
      if( $ob->getMeta( 'type' ) == 'page' ) {
        $this->ascendants = explode( ",", $ob->ascendants );
        $this->ascendants[] = $ob->id;
      } else if( $ob->getMeta( 'type' ) == 'post' ) {
        $category = $ob->sharedCategory;
        if( is_array( $category ) ) {
          $firstCategory = current( $category );
          $categoryPage = SeeDB::load( 'page', $firstCategory->page_id );
          $this->ascendants = explode( ",", $categoryPage->ascendants );
          $this->ascendants[] = $categoryPage->id;
        }
      }
      
      $route['label'] = (( $ob->htmltitle ) ? $ob->htmltitle : $ob->title );
      $route['level'] = SeeHelperController::countPathLevels( $r->route );
      $route['template'][0] = $ob->template;
      
      $this->content->objectType = $r->objecttype;
      $this->content->objectID = $r->objectid;
      $this->editable = (( $this->adminauth->checkAccess( 'action-content-edit', null, false ) ) ? 1 : 0 );
      $status = (( $this->editable ) ? '0,1' : '1' );
      
      if( $ob->status == 0 && isset( $ob->status ) && !$this->editable ) {
      
        SeeRouteController::http404();
      }
      
      if( $ob->redirect ) {
        $redirect = $this->content->loadLinkDetails( $ob->redirect );
        $redirect = $redirect['route'];
        if( $redirect[0] == '/' ) {
          $redirect = substr_replace( $redirect, '/'.$this->see->rootURL, 0, 1 );
        }
        $this->see->redirect( $redirect );
      }
      
      // Collect any content we need
      $content = SeeDB::find( 'content', ' objecttype = ? && objectid = ? && language = ? && status IN ( '.$status.' ) ORDER BY status ASC ', array( $r->objecttype, $r->objectid, $this->language ) );
      foreach( $content as $c ) {
      
        if( !isset( $route['content']['content'.$c->contentcontainer->id] ) ) {
          $method = str_replace( " ", "", $c->contentcontainer->contenttype->type );
          $method[0] = strtolower( $method[0] );
          $route['content']['content'.$c->contentcontainer->id] = $this->content->$method( $c->content, (( $this->editable && $_GET['preview'] ) ? 1 : 0 ), $c->contentcontainer->id, $c->status, $c->contentcontainer->contenttype->fields, $c->contentcontainer->contenttype->settings );
        }
      }
      
      $contentcontainer = SeeDB::findAll( 'contentcontainer' );
      foreach( $contentcontainer as $c ) {
          
        $contentApp = SeeDB::find( 'contentappend', ' objecttype = ? && objectid = ? && language = ? && contentcontainer_id = ? ', array( $r->objecttype, $r->objectid, $this->language, $c->id ) );
        foreach( $contentApp as $ca ) {
          if( $ca->position == 0 ) {
            if(!$route['content']['content'.$c->id]) {
              $emptyContent[$c->id] = 1;
            }
            $route['content']['content'.$c->id] = $ca->content.$route['content']['content'.$c->id];
          } else if( $ca->position == 1 ) {
            $route['content']['content'.$c->id] = $ca->content;
          } else if( $ca->position == 2 ) {
            if(!$route['content']['content'.$c->id]) {
              $emptyContent[$c->id] = 1;
            }
            $route['content']['content'.$c->id] .= $ca->content;
          }
        }
      }
    }
      
    // Load any empty content parts and include edit bar
    if( $this->editable ) {
    
      $this->see->html->css( 'siteoverlay.css', '', '/seecms/css/' );
      $this->see->html->js( 'siteoverlay.js', '', '/seecms/js/' );
    
      if( $_GET['preview'] ) {
        //$contentcontainer = SeeDB::findAll( 'contentcontainer' );
        foreach( $contentcontainer as $c ) {
          if( !isset( $route['content']['content'.$c->id] ) || $emptyContent[$c->id] ) {
            $method = str_replace( " ", "", $c->contenttype->type );
            $method[0] = strtolower( $method[0] );
            $route['content']['content'.$c->id] = $this->content->$method( '', $this->editable, $c->id, 1, $c->contenttype->fields, $c->contenttype->settings ).$route['content']['content'.$c->id];
          }
        }
      }
    }
    
    /* Log analytics */
    SeeCMSAnalyticsController::logVisit( $r->objecttype, $r->objectid, $this->see->siteID );
    
    return( $route );
  }
  
  private function staticRouteManager( $r ) {
  
    $currentCMSSection = $this->currentCMSSection( $this->see->currentRoute );
    
    if( $currentCMSSection != $this->see->currentRoute && $this->see->currentRoute != "{$this->cmsRoot}/login/" ) { 
    
      // Check user is logged in
      if( isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) ) {
      
        // Check user has access to requested bit
        $this->adminauth->checkAccess( $currentCMSSection );
        
      } else {
        $this->see->redirect( "/{$this->see->rootURL}{$this->cmsRoot}/login/" );
      }
    
    } else if( $this->see->currentRoute == "{$this->cmsRoot}/login/" && isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) ) {
      $this->see->redirect( "../" );
    }
  
    return( $r );
  }
  
  private function currentCMSSection( $route ) {
  
    $route = str_replace( $this->see->prepareRoute( $this->cmsRoot ), '', $route );
    return( $route );
  }
  
  public static function makeRoute( $title, $objectID, $objectType, $parentRoute, $primary = 1 ) {
  
    // This page route
    $a = 1;
    $route = $parentRoute.strtolower( SeeFormatController::url( $title )."/" );
    while( SeeDB::findOne( 'route', ' route = ? ', array( $route ) ) ) {
      $route = $parentRoute.strtolower( SeeFormatController::url( $title ).$a."/" );
      $a++;
    }
    
    // Insert route
    SeeCMSController::createRoute( $route, $objectID, $objectType, $primary );
    
    return( $route );
  }
  
  public static function getSetting( $setting ) {
  
    $s = SeeDB::findOne( 'setting', ' name = ? ', array( $setting ) );
    
    return( $s->value );
  }
  
  public static function createRoute( $route, $objectID, $objectType, $primary ) {
  
    // Insert route
    $r = SeeDB::dispense( 'route' );
    $r->route = $route;
    $r->objectid = $objectID;
    $r->objecttype = strtolower( $objectType );
    $r->primaryroute = $primary;
    SeeDB::store( $r );
  }
  
  public function addCMSRoute( $name ) {
  
    $this->routes[] = $name;
  }
  
  public function outputManager( $o ) {
  
    // Needs correct permission checking
    //$editable = (( $this->adminauth->checkAccess( 'action-content-edit', null, false ) ) ? 1 : 0 );
    
    $preview = (( $_GET['preview'] ) ? '' : '?preview=1' );
      
    if( !$_GET['preview'] && $this->editable ) {
      $editButton = "<div class=\"see-cms-toolbar\"><a class=\"see-cms-tool\"><span></span></a><a class=\"see-cms-logo\"></a>".(( is_array( $this->editContent ) ) ? "<a href=\"./{$preview}\" id=\"see-cms-edit\" >{$this->editContent[0]}</a>":"").(( $this->editSettings )?"<a href=\"/{$this->see->rootURL}{$this->cmsRoot}/{$this->content->objectType}/edit/?id={$this->content->objectID}\" id=\"see-cms-settings\" >{$this->editSettings}</a>":"")."</div>";
    } else if( $_GET['preview'] && $this->editable ) {
      $editButton = "<div class=\"see-cms-toolbar\"><a class=\"see-cms-tool\"><span></span></a><a class=\"see-cms-logo\"></a>".(( is_array( $this->editContent ) ) ? "<a href=\"./{$preview}\" id=\"see-cms-edit\" >{$this->editContent[1]}</a>":"").(( $this->editSettings )?"<a href=\"/{$this->see->rootURL}{$this->cmsRoot}/{$this->content->objectType}/edit/?id={$this->content->objectID}\" id=\"see-cms-settings\" >{$this->editSettings}</a>":"")."</div>";
    }
  
    $o = str_replace( '<SEECMSEDIT>', $editButton, $o );
  
    return( $o );
  }
  
  public function breadcrumb() {

  }
  
  public function http404() {
  
    $_SESSION['seecms'][$this->see->siteID]['redirect']['referrer'] = $_SERVER['HTTP_REFERRER'];
    $_SESSION['seecms'][$this->see->siteID]['redirect']['route'] = $this->see->currentRoute;
    $_SESSION['seecms'][$this->see->siteID]['redirect']['reason'] = '404';
    
    if( isset( $this->config['http404page'] ) ) {
      $this->see->redirect( '/'.$this->see->rootURL.$this->config['http404page'] );
    }
  }
  
  private function install() {
    
    if( $_POST['submit'] ) {
      
      $this->see->dbConnect( $_POST['databasehost'], $_POST['databasename'], $_POST['databaseusername'], $_POST['databasepassword'] );
      
      $sql = file_get_contents( "../plugin/SeeCMS/seecms.sql" );

      try {
        SeeDB::exec( $sql );
      } catch (Exception $e) {
        $this->see->redirect( './?error=db' );
      }
      
      // Make an admin user
      $name = $_POST['name'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      $this->adminauth->update( array( 'name' => $name, 'email' => $email, 'password' => $password, 'level' => 1 ) );

      file_put_contents( '../plugin/SeeCMS/install.txt', "Installed on: ".date("Y-m-d H:i:s") );
      
      // UPDATE config
      $config = file_get_contents( "../custom/config.php" );
      $replace = array( '[CMSROOT]', '[ROOTURL]', '[PUBLICFOLDER]', '[DBHOST]', '[DBNAME]', '[DBUSERNAME]', '[DBPASSWORD]', '[THEME]', '[AESKEY]', '[SITEID]', '[SITETITLE]', '[CMSSUPPORTMESSAGE]' );
      $with = array( $_POST['cmsurl'], $_POST['siteurl'], $_POST['publicfolder'], $_POST['databasehost'], $_POST['databasename'], $_POST['databaseusername'], $_POST['databasepassword'], $_POST['theme'], base64_encode( openssl_random_pseudo_bytes( 64 ) ), base64_encode( openssl_random_pseudo_bytes( 32 ) ), str_replace( "'", "\\'", $_POST['sitetitle'] ), str_replace( "'", "\\'", nl2br( $_POST['supportmessage'] ) ) );
      $config = str_replace( $replace, $with, $config );
      file_put_contents( "../custom/config.php", $config );
      
      // Install theme
      if( file_exists( "../custom/{$_POST['theme']}/install/{$_POST['theme']}.sql" ) ) {
        SeeDB::exec( file_get_contents( "../custom/{$_POST['theme']}/install/{$_POST['theme']}.sql" ) );
        unlink( "../custom/{$_POST['theme']}/install/{$_POST['theme']}.sql" );
      }
      
      if( file_exists( "../custom/{$_POST['theme']}/install/manifest.json" ) ) {
        $files = json_decode( file_get_contents( "../custom/{$_POST['theme']}/install/manifest.json" ) );
        
        foreach( $files as $f ) {
          rename( $f->from, $f->to );
        }
      }
      
      if( $_POST['themestuff'] == 'Yes' ) {
        SeeDB::exec( file_get_contents( "../custom/{$_POST['theme']}/install/{$_POST['theme']} Sample.sql" ) );
        unlink( "../custom/{$_POST['theme']}/install/{$_POST['theme']} Sample.sql" );
        
        if( file_exists( "../custom/{$_POST['theme']}/install/manifest Sample.json" ) ) {
          $files = json_decode( file_get_contents( "../custom/{$_POST['theme']}/install/manifest Sample.json" ) );
          
          foreach( $files as $f ) {
            rename( $f->from, $f->to );
          }
        }
      }

      include( "../custom/{$_POST['theme']}/install/install.php" );
      
      @mkdir( 'css' );
      @mkdir( 'js' );
      @mkdir( 'images' );
      @mkdir( 'images/uploads' );
      
      $emailTemplate = SeeDB::findOne('setting', " name = 'email' ");
      $link1 = 'http'.((empty( $_SERVER['HTTPS'])) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}/{$_POST['siteurl']}";
      $link2 = 'http'.((empty( $_SERVER['HTTPS'])) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}/{$_POST['siteurl']}{$_POST['cmsurl']}/";
        
      $emailContent = "<h2>SeeCMS setup complete</h2><hr /><p>Please visit the link below to view your site:<br /><a href=\"{$link1}\">{$link1}</a></p><p>Or this link to login to your CMS:<br /><a href=\"{$link2}\">{$link2}</a></p>";
        
      $email = str_replace( '<EMAILCONTENT>', $emailContent, $emailTemplate->value );
        
      $seeemail = new SeeEmailController();
      $seeemail->sendHTMLEmail( $_POST['email'], $_POST['email'], $email, 'SeeCMS setup complete' );
      
      include '../plugin/SeeCMS/installdone.php';
      die();
      
    } else {
      
      include '../plugin/SeeCMS/install.php';
      die();
    }
  }
}