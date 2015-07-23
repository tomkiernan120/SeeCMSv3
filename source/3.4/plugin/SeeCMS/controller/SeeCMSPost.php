<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 
class SeeCMSPostController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function load() {
  
    $p = SeeDB::load( 'post', (int)$_GET['id'] );
    return( $p );
  }
  
  public function loadForEdit() {
  
    $data['post'] = $this->load();
    $data['postRoutes'] = SeeDB::find( 'route', ' objecttype = ? && objectid = ? ORDER BY primaryroute DESC ', array( 'post', $data['post']->id ) );
    $data['editError'] = (( $_GET['routeerror'] ) ? 'Error: The URLs could not be updated because one or more of them already exist on another page/post' : '' );
    $data['templates'] = json_decode( SeeCMSSettingController::load( 'pagetemplates' ) );
    
    // Backwards compatibility
    if( !is_array( $data['templates'] ) ) {
      $data['templates'] = unserialize( SeeCMSSettingController::load( 'pagetemplates' ) );
    }
    
    return( $data );
  }
  
  public function create() {
  
    // Check if parent exists
    $pp = SeeDB::load( 'post', $_POST['parentid'] );
    
    if( ( $pp->id && $pp->isfolder ) || $_POST['parentid'] === '0' ) {
  
      $p = SeeDB::dispense( 'post' );
      
      $p->title = $_POST['title'];
      $p->parentid = $_POST['parentid'];
      $p->posttype_id = (int)$_POST['posttype'];
      $p->isfolder = (int)$_POST['isfolder'];
      $p->postorder = 0;
      $p->tags = '';
      $p->date = date("Y-m-d");
      
      $templates = json_decode( SeeCMSSettingController::load( 'pagetemplates' ) );
    
      // Backwards compatibility
      if( !is_array( $templates ) ) {
        $templates = unserialize( SeeCMSSettingController::load( 'pagetemplates' ) );
      }
      
      $p->template = $templates[0];
      
      SeeDB::store( $p );
      
      SeeCMSController::makeRoute( $p->title, $p->id, 'Post', SeeCMSController::getSetting( 'postsURL' ) );
      
      $ret['done'] = 1;
      $ret['data'] = $this->loadByFolder( $p->parentid );
    } else {
    
      $ret['done'] = 0;
    }
    
    return( json_encode( $ret ) );
  }
  
  public function update( $data, $errors, $settings ) {
  
    // Check permission
  
    $p = SeeDB::load( 'post', (int)$data['id'] );
    
    if( $p->id ) {
    
      if( $data['route0'] ) {
      
        foreach( $data as $dk => $dv ) {
        
          if( substr( $dk, 0, 5 ) == 'route' ) {
          
            $routeID = str_replace( 'route', '', $dk );
            $theRoute = $this->see->prepareRoute( $dv );
            
            // Check if the route exists somewhere else
            $r = SeeDB::findOne( 'route', ' route = ? && ( objectid != ? || objecttype != ? ) ', array( $theRoute, $p->id, 'post' ) );
            if( $r ) {
              $routesOK = false;
              break;
            } else {
            
              if( !$data['deleteroute'.$routeID] ) {
                $routesOK = true;
                
                $addRoute[] = array( $theRoute, (( $data['primaryroute'.$routeID] && !$primaryset ) ? 1 : 0 ) );
                
                if( $data['primaryroute'.$routeID] ) {
                  $primarySet = 1;
                }
                
              }
            }
          }
        }
      
        if( $routesOK ) {
        
          // If there's no primary route set use the first one
          if( !$primarySet ) {
            $addRoute[0][1] = 1;
          }
        
          SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'post' " );
          
          foreach( $addRoute as $r ) {
            SeeCMSController::createRoute( $r[0], $p->id, 'post', $r[1] );
          }
        }
      }
      
      $p->sharedCategory = array();
      // Categories
      foreach( $data as $dk => $dv ) {
        
        if( substr( $dk, 0, 9 ) == 'category_' ) {
        
          $category = SeeDB::load( 'category', str_replace( 'category_', '', $dk ) );
          $p->sharedCategory[] = $category;
        }
      }
    
      $p->title = $data['title'];
      $p->standfirst = $data['standfirst'];
      $p->tags = $data['tags'];
      $p->date = $this->see->format->date( (($data['postdate'])?$data['postdate']:time()), "Y-m-d" );
      
      $p->htmltitle = $data['htmltitle'];
      $p->media_id = (int)$data['media_id'];
      $p->template = $data['template'];
      
      $p->metadescription = $data['metadescription'];
      $p->metakeywords = $data['metakeywords'];
      
      $commencementtime = (( $data['commencementtime'] ) ? $data['commencementtime'].":00" : '00:00:00');
      $commencement = strtotime( $data['commencement']." ".$commencementtime );
      $p->commencement = (( $commencement && $data['commencement'] ) ? date( "Y-m-d H:i:s", $commencement ) : '0000-00-00 00:00:00' );
      
      $expirytime = (( $data['expirytime'] ) ? $data['expirytime'].":00" : '00:00:00');
      $expiry = strtotime( $data['expiry']." ".$expirytime );
      $p->expiry = (( $expiry && $data['expiry'] ) ? date( "Y-m-d H:i:s", $expiry ) : '0000-00-00 00:00:00' );
      
      $p->eventstart = $this->see->format->date( $data['eventstartdate']." {$data['starttimehour']}:{$data['starttimeminute']}:00" , "Y-m-d H:i:s" );
			$p->eventend = $this->see->format->date( $data['eventenddate']." {$data['endtimehour']}:{$data['endtimeminute']}:00" , "Y-m-d H:i:s" );
      
      if( !$p->eventstart ) {
        $p->eventstart = '0000-00-00 00:00:00';
      }
      
      if( !$p->eventend ) {
        $p->eventend = '0000-00-00 00:00:00';
      }
      
      SeeDB::store( $p );
      
      // Custom post type data
      if( isset( $this->see->SeeCMS->customPostController[$p->posttype->name]['plugin'] ) ) {
        $customPostController = $this->see->{$this->see->SeeCMS->customPostController[$p->posttype->name]['plugin']};
        $customPostController->saveFields( $p, $data );
      }
      
      $this->see->redirect( "?id={$p->id}".(( $routesOK === false ) ? '&routeerror=1' : '' ) );
    }
    
  }
  
  public function savefolder() {
  
    // Check permission
    $p = SeeDB::load( 'post', (int)$_POST['id'] );
    
    if( $p->id ) {
      $p->title = $_POST['title'];
      SeeDB::store( $p );
      return( $this->folderTree() );
    }
  }
  
  public function status( $id = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    $p = SeeDB::load( 'post', $id );
    $p->status = (( $p->status ) ? 0 : 1 );
    SeeDB::store( $p );
    
    $ret['done'] = 1;
    $ret['data'] = $this->loadByFolder( $p->parentid );
    
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
    $pp = SeeDB::load( 'post', $at );
    
    if( ( $pp->id || $at === '0' ) && $pp->id != $id ) {
  
      $p = SeeDB::load( 'post', $id );
      
      if( $p->id ) {
      
        $p->parentid = $at;
        SeeDB::store( $p );
        
        return( json_encode( $this->loadForCMS() ) );
      }
    }
  }
  
  public function delete( $id = 0, $recursive = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
      $first = 1;
    }

    $p = SeeDB::load( 'post', $id );
    
    if( $p->id ) {
    
      if( $p->isfolder && $first ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] = $m->parentid;
      }
      
      SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'post' " );
      SeeDB::exec( " UPDATE adfcontent SET objecttype = 'postdeleted' WHERE objectid = {$p->id} && objecttype = 'post' " );
    
      $p->deleted = date("Y-m-d H:i:s");
      SeeDB::store( $p );
      
      $this->recursiveDelete( $p->id );
      
      $_POST['id'] = '';
    }
    
    if( $first ) {
      return( json_encode( $this->loadForCMS() ) );
    }
  }
  
  private function recursiveDelete( $parentID ) {

    $posts = SeeDB::find( 'post', ' parentid = ? && deleted = ? ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $posts as $p ) {
      $this->delete( $p->id, 1 );
    }
  }
  
  public function loadForCMS() {
  
    $data['folderTree'] = $this->folderTree();
    $data['posts'] = $this->loadByFolder( $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] );
    $data['posttypes'] = SeeDB::findAll( 'posttype', ' ORDER BY name ' );
    if( count( $data['posttypes'] ) <= 1 ) {
      $data['posttypes'] = '';
    }
    
    return( $data );
  }
  
  public function folderTree( $parentID = 0 ) {
    
    $parentID = (int)$parentID;
    
    if( !$parentID ) {
      $content = "<h3".((!$_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'])?' class="selected"':'')."><a href=\"#\" class=\"postfolder\" id=\"folder0\">All posts</a></h3>";
    }
    
    $folders = SeeDB::find( 'post', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY title ASC ', array( $parentID, '0000-00-00 00:00:00', 1 ) );
    foreach( $folders as $f ) {
      
      $ret = $this->folderTree( $f->id );
      $class = (( $ret ) ? 'child' : 'nochild' );
      $class .= (( $f->id == $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] ) ? ' selected' : '' );
      
      $content .= "<li class=\"{$class}\"><a href=\"#\" class=\"postfolder\" id=\"folder{$f->id}\"><span class=\"name\">{$f->title}</span><span title=\"Move\" class=\"move\"></span><span title=\"Edit post\" class=\"viewedit\"></span><span title=\"Delete\" class=\"delete\"></span><span title=\"Move here\" class=\"target\"></span></a>";
      
      if( $ret ) {
        $content .= "<ul>".$ret."</ul>";
      }
      
      $o .= "</li>";
      
    }
    
    return( $content );
  }
  
  public function postTreeSimple( $parentID = 0, $recurse = 0 ) {
  
    $parentID = (int)$parentID;
    
    $posts = SeeDB::find( 'post', ' parentid = ? && deleted = ? ORDER BY isfolder DESC, title ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $posts as $p ) {
      
      $ret = $this->postTreeSimple( $p->id, 1 );
      
      if( $p->isfolder ) {
        $content .= "<li class=\"folder\"><a href=\"#\">{$p->title}</a>";
      } else {
        $content .= "<li><a href=\"#\" id=\"post-{$p->id}\" class=\"file\">{$p->title} <em>".$this->see->format->date( $p->postdate, 'd M Y' )."</em></a>";
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
  
  public function loadByFolder( $parentID = 0 ) {
  
    $parentID = (int)$parentID;
    if( $_POST['id'] ) {
      $p = SeeDB::load( 'post', (int)$_POST['id'] );
      if( $p->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
    }
    
    $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] = $parentID;
    
    $content = "<ul>";
    
    $posts = SeeDB::find( 'post', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY eventstart ASC, date DESC ', array( $parentID, '0000-00-00 00:00:00', 0 ) );
    foreach( $posts as $p ) {
      
      $date = $this->see->format->date( (($p->eventstart!='0000-00-00 00:00:00')?$p->eventstart:$p->date), "d.m.Y" );
      $content .= "<li><div class=\"page\"><a class=\"name\" href=\"../post/edit/?id={$p->id}\">{$p->title}</a><a class=\"date\" href=\"#\">{$date}</a><a class=\"move\" title=\"Move\" id=\"move{$p->id}\" href=\"#\"></a><a class=\"delete deletepost\" id=\"deletepost-{$p->id}\" title=\"Delete\" href=\"#\"></a>";
      $content .= (( $p->status ) ? "<a class=\"published togglepoststatus\" title=\"Suppress\"  id=\"status{$p->id}\"></a>" : "<a class=\"notpublished togglepoststatus\" title=\"Publish\" id=\"status{$p->id}\"></a>" );
      $content .= (( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00' ) ? "<a class=\"clock\" title=\"".(( $p->commencement != '0000-00-00 00:00:00' ) ? 'Commencement: '.$this->see->format->date($p->commencement, "d M Y H:i")."\n" : '' ).(( $p->expiry != '0000-00-00 00:00:00' ) ? 'Expiry: '.$this->see->format->date($p->expiry, "d M Y H:i")."\n" : '' )."\"></a>" : "" );
      $content .= "</div></li>";
      
    }
    
    $content .= "</ul>";
    
    $content = (( $content != '<ul></ul>' ) ? $content : '<p><strong>There\'s no posts in this folder.</strong></p>' );
    
    return( $content );
  }
  
  function adminSearch( $keyword ) {
  
    $posts = SeeDB::find( 'post', ' deleted = ? && isfolder = ? && title LIKE ? ORDER BY date DESC LIMIT 10 ', array( '0000-00-00 00:00:00', 0, "%{$keyword}%" ) );
    foreach( $posts as $p ) {
      
      $r[] = array( 'id' => $p->id, 'title' => $p->title, 'posted' => $this->see->format->date( $p->date, "d F Y" ) );
    }
    
    return( $r );
  }
  
  function feed( $settings = array() ) {
    
    // GET posts
    $sql = ' deleted = ? && isfolder = ? && status = ? && posttype_id = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) ';
    $sqlParams = array( '0000-00-00 00:00:00', 0, 1, (($settings['postType'])?$settings['postType']:1), '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now );
    
    if( $settings['order'] ) {
      $sqlOrder = ' ORDER BY '.$settings['order'];
    } else {
      $sqlOrder = ' ORDER BY date DESC, id DESC ';
    }
    
    if( $settings['tags'] && $_GET['tag'] ) {
      $sql .= " && tags LIKE ? ";
      $sqlParams[] = "%{$_GET['tag']}%";
    }
    
    if( $settings['archives'] && $_GET['year'] ) {
      $sql .= " && date >= ? && date <= ? ";
      if( $_GET['month'] ) {
        $sqlParams[] = $_GET['year']."-".str_pad( $_GET['month'], 2, "0", STR_PAD_LEFT)."-01";
        $sqlParams[] = $this->see->format->date( $_GET['year']."-".str_pad( $_GET['month'], 2, "0", STR_PAD_LEFT)."-01", "Y-m-t" );
      } else {
        $sqlParams[] = $_GET['year']."-01-01";
        $sqlParams[] = $_GET['year']."-12-31";
      }
    }
    
    if( $settings['defaultDisplay'] && ( !$settings['archives'] || !$_GET['year'] ) && ( !$settings['tags'] || !$_GET['tag'] ) ) {
    
      if( $settings['defaultDisplay'] == 'currentYear' ) {
      
        $sqlAlternateParams = $sqlParams;
      
        $sql .= " && date >= ? && date <= ? ";
        $sqlParams[] = date("Y")."-01-01";
        $sqlParams[] = date("Y")."-12-31";
        
        $sqlAlternateParams[] = (date("Y")-1)."-01-01";
        $sqlAlternateParams[] = (date("Y")-1)."-12-31";
      
      } else if( $settings['defaultDisplay'] == 'currentMonth' ) {
      
        $sql .= " && date >= ? && date <= ? ";
        $sqlParams[] = date("Y-m")."-01";
        $sqlParams[] = date("Y-m-t");
      
      }
    }
    
    if( $settings['category'] ) {
      $category = SeeDB::load( 'category', $settings['category'] );
      $posts = $category->withCondition( $sql.$sqlOrder, $sqlParams )->sharedPost;
    } else {
      $posts = SeeDB::find( 'post', $sql.$sqlOrder, $sqlParams );
    }
    
    if( !is_array( $posts ) && $sqlAlternateParams ) {
      if( $settings['category'] ) {
        $category = SeeDB::load( 'category', $settings['category'] );
        $posts = $category->withCondition( $sql.$sqlOrder, $sqlAlternateParams )->sharedPost;
      } else {
        $posts = SeeDB::find( 'post', $sql.$sqlOrder, $sqlAlternateParams );
      }
    }
    
    if( is_array( $posts ) ) {
    
      $postCount = count( $posts );
    
      if( $settings['limit'] ) {
        $settings['page'] = (((int)$settings['page'])?$settings['page']:1);
        $posts = array_slice( $posts, ($settings['page']-1)*$settings['limit'], $settings['limit'] );
      }
      
      foreach( $posts as $p ) {
      
        $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'post', $p->id, 1 ) );
        $pscontent = '';
        $tagsHTML = '';
        $tags = array();
        
        // Collect any content we need
        $content = SeeDB::find( 'content', ' objecttype = ? && objectid = ? && language = ? && status = ? ORDER BY status ASC ', array( 'post', $p->id, $this->see->SeeCMS->language, 1 ) );
        foreach( $content as $c ) {
        
          if( !isset( $ps['content'.$c->contentcontainer->id] ) ) {
            $method = str_replace( " ", "", $c->contentcontainer->contenttype->type );
            $method[0] = strtolower( $method[0] );
            $pscontent['content'.$c->contentcontainer->id] = $this->see->SeeCMS->content->$method( $c->content, 0, $c->contentcontainer->id, $c->status, $c->contentcontainer->contenttype->fields, $c->contentcontainer->contenttype->settings );
          }
        }
        
        // Sort tags
        if( $p->tags ) {
          foreach( explode( ',', $p->tags ) as $t ) {
            $t = strtolower( trim( $t ) );
            $tagsHTML .= "<a class=\"seecmstag\" href=\"./?tag={$t}\">{$t}</a>, ";
            $tags[].= $t;
          }
          $tagsHTML = "<p class=\"seecmstags\">Tags: {$tagsHTML}</p>";
        }

        $ps[] = array( 'id' => $p->id, 'title' => $p->title, 'media' => $p->media, 'route' => "/{$route->route}", 'date' => $p->date, 'eventStart' => $p->eventstart, 'eventEnd' => $p->eventend, 'standfirst' => $p->standfirst, 'content' => $pscontent, 'tagsHTML' => $tagsHTML, 'tags' => $tags, 'categories' => $p->sharedCategory, 'post' => $p );
      }
    
      if( $settings['pages'] ) {
        $r = array( 'posts' => $ps, 'postCount' => $postCount, 'page' => $settings['page'], 'pages' => ceil( $postCount/$settings['limit'] ) );
      } else {
        $r = $ps;
      }
    }
    
    return( $r );
  }
  
  public function archiveList( $settings = array() ) {
  
    // GET oldest post
    $sql = ' deleted = ? && isfolder = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && posttype_id = ? ';
    $sqlParams = array( '0000-00-00 00:00:00', 0, 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, (($settings['postType'])?$settings['postType']:1) );
    
    if( !isset( $settings['postType'] ) ) {
      $sqlOrder = ' ORDER BY date';
    } else if( $settings['postType'] == 1 ) {
      $sqlOrder = ' ORDER BY date';
    } else if( $settings['postType'] == 2 ) {
      $sqlOrder = ' ORDER BY eventstart';
    }
    
    $post = SeeDB::findOne( 'post', $sql.$sqlOrder, $sqlParams );
    
    if( $post ) {
      list( $endYear, $endMonth, $endDay ) = explode( '-', (($settings['postType']==2)?$post->eventstart:$post->date) );
      $endMonth = (int)$endMonth;
    }
  
    // GET newest post
    $sql = ' deleted = ? && isfolder = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && posttype_id = ? ';
    $sqlParams = array( '0000-00-00 00:00:00', 0, 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, (($settings['postType'])?$settings['postType']:1) );
    
    if( $settings['postType'] == 1 || !$settings['postType'] ) {
      $sqlOrder = ' ORDER BY date DESC';
    } else if( $settings['postType'] == 2 ) {
      $sqlOrder = ' ORDER BY eventend DESC';
    }
    
    $post = SeeDB::findOne( 'post', $sql.$sqlOrder, $sqlParams );
    
    if( $post ) {
      list( $startYear, $startMonth, $startDay ) = explode( '-', (($settings['postType']==2)?$post->eventend:$post->date) );
      $startMonth = (int)$startMonth;
    }
    
    $o .= "<ul>";
    
    for( $year = $startYear; $year >= $endYear; $year-- ) {
    
      $o .= "<li><a href=\"./?year={$year}\">{$year}</a><ul>";
      
      if( $year == $startYear ) {
        $bmonth = $startMonth;
      } else {
        $bmonth = 12;
      }
      
      if( $year == $endYear ) {
        $emonth = $endMonth;
      } else {
        $emonth = 1;
      }
      
      for( $month = $bmonth; $month >= $emonth; $month-- ) {
    
        $o .= "<li><a href=\"./?year={$year}&amp;month={$month}\">".$this->see->format->date( "2000-{$month}-01", "F" )."</a></li>";
      }
      
      $o .= "</ul></li>";
    }
    
    $o .= "</ul>";
    
    
    return( $o );
    
  }
  
  public function rss( $route = '' ) {
  
    $posts = $this->feed( $route['custom'] );
    
    $url = "http://".$_SERVER['HTTP_HOST'].(($this->see->rootURL)?"/".$this->see->rootURL:'');
  
    echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<rss version=\"2.0\">\n<channel>\n";

    echo "<title>{$route['custom']['feedtitle']}</title>\n";
    echo "<link>{$url}</link>\n";
    echo "<description>{$route['custom']['feeddescription']}</description>\n";
    echo "<language>".(($route['custom']['feedlanguage'])?$route['custom']['rsssettings']['feedlanguage']:'en-gb')."</language>\n";
    
    $url = rtrim( $url, '/' );
    
    if( is_array( $posts ) ) {

      foreach( $posts as $post ) {

        echo "<item>\n";
        echo "<title>{$post['title']}</title>\n";
        echo "<link>{$url}{$post['route']}</link>\n";
        echo "<description>{$post['standfirst']}</description>\n";
        echo "</item>\n";

      }
    }

    echo "</channel>\n</rss>\n";
    die();
  }
}