<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSWebsiteUserController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function loadAll() {
  
    $au = SeeDB::findAll( 'websiteuser', ' ORDER BY surname, forename ' );
    
    return( $au );
  }
  
  public function loadForEdit( $id = 0 ) {
  
    if( !$id ) {
      $id = $_GET['id'];
    }
  
    $d['user'] = SeeDB::load( 'websiteuser', $id );
    $d['groups'] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    
    return( $d );
  }
  
  public function delete() {
  
    $wu = SeeDB::load( 'websiteuser', $_POST['id'] );
    SeeDB::trash( $wu );
    
    // Delete ADFs
    SeeDB::exec( " DELETE FROM adfcontent WHERE objectid = ? && objecttype = 'websiteuser' ", array( $_POST['id'] ) );
    
    echo 'Done';
  }
  
  public function login( $data, $errors, $settings ) {
  
    if( !$errors && $data['seecmswebsiteuserpassword'] ) {
    
      $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $data['seecmswebsiteuseremail'] ) );
      if( $u ) {
        if( $u->passwordformat == 'md5' ) {
        
          $data['seecmswebsiteuserpassword'] = md5( $data['seecmswebsiteuserpassword'] );
          $password = strtolower( $u->password );
          $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
        } else if( $u->passwordformat == 'aes256' ) {
        
          $password = $this->see->security->decAES256( $u->password );
          $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
        } else {
          
          $passwordCheck = password_verify($data['seecmswebsiteuserpassword'],$u->password);
        }
        
        if( $passwordCheck ) {
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] = $u->id;
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['forename'] = $u->forename;
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['surname'] = $u->surname;
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['email'] = $u->email;
        
          $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
          if( is_array( $adfs ) ) {
            foreach( $adfs as $adf ) {
              $adfs[] = $adf->id;
            }
            $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
            $_SESSION['seecms'][$this->see->siteID]['websiteuser']['adf'] = $cc->loadADFcontent( array( 'objectid' => $u->id, 'type' => 'websiteuser', 'adfs' => $adfs ) );
          }
          
          $ret['result'] = 1;
          
           if( isset( $_SESSION['restrictedRouteRequest'] ) ) {
            $redirect = $_SESSION['restrictedRouteRequest'];
          } else if( isset( $settings['settings']['successredirect'] ) ) {
            $redirect = $settings['settings']['successredirect'];
          }
          
          if( isset( $redirect ) ) {
          
            if( $redirect == '/' ) {
              $redirect = substr_replace( $redirect, '/'.$this->see->rootURL, 0, 1 );
            }
            $this->see->redirect( $redirect );
          }
        }
      }
      
      if( !$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
        $ret['errors']['email'] = 'Login details incorrect.';
      }
    }
    
    return( $ret );
  }
  
  public function update( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      $u = SeeDB::load( 'websiteuser', $data['id'] );
      
      $u->title = $data['seecmswebsiteusertitle'];
      $u->forename = $data['seecmswebsiteuserforename'];
      $u->surname = $data['seecmswebsiteusersurname'];
      $u->organisation = $data['seecmswebsiteuserorganisation'];
      $u->address1 = $data['seecmswebsiteuseraddress1'];
      $u->address2 = $data['seecmswebsiteuseraddress2'];
      $u->address3 = $data['seecmswebsiteuseraddress3'];
      $u->city = $data['seecmswebsiteusercity'];
      $u->region = $data['seecmswebsiteuserregion'];
      $u->postcode = $data['seecmswebsiteuserpostcode'];
      $u->country = $data['seecmswebsiteusercountry'];
      $u->email = $data['seecmswebsiteuseremail'];
      $u->telephone = $data['seecmswebsiteusertelephone'];
      
      if( $data['seecmswebsiteuserpassword'] ) {

          $u->password = password_hash( $data['seecmswebsiteuserpassword'], PASSWORD_BCRYPT );
          $u->passwordformat = 'hash';
      } else if( !$u->password ) {
        
        $u->password = password_hash( base64_encode( openssl_random_pseudo_bytes( 32 ) ), PASSWORD_BCRYPT );
        $u->passwordformat = 'hash';
      }
      
      SeeDB::store( $u );
      
      foreach( $data as $dK => $dV ) {
      
        if( substr( $dK, 0, 23 ) == 'seecmswebsiteusergroup-' ) {
        
          $wug = SeeDB::load( 'websiteusergroup', substr( $dK, 23 ) );
          $u->sharedWebsiteusergroup[] = $wug;
        }
      }
      
      SeeDB::store( $u );
      
      // ADFs
      $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
      $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
      if( is_array( $adfs ) ) {
        foreach( $adfs as $adf ) {
          $data = array();
          foreach( $_POST as $pfK => $pfD ) {
            if( strstr( $pfK, "adf{$adf->id}-" ) ) {
              $data[$pfK] = $pfD;
            }
          }
          
          $cc->edit( array( 'settingsScreen' => 1, 'objectType' => 'websiteuser', 'objectID' => $u->id, 'containerID' => $adf->id, 'data' => $data ), true );
        }
      }
      
      $this->see->redirect( "./?id={$u->id}" );
    }
    
    return( $errors );
  }
  
  public function selfUpdate( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      if( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
      
        $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] );
      } else {
      
        $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $data['seecmswebsiteuseremail'] ) );
        if( $u->id ) {
        
          $ret['errors']['seecmswebsiteuseremail'] = 'You are already registered.';
        }
      }
        
      if( $data['seecmswebsiteuserpassword'] != $data['seecmswebsiteuserconfirmpassword'] ) {
      
        $ret['errors']['seecmswebsiteuserconfirmpassword'] = 'Please ensure the password and confirm password fields match.';
      }
      
      if( !is_array( $ret['errors'] ) ) {
        
        if( !$u->id ) {
          $u = SeeDB::dispense( 'websiteuser' );
          $newUser = true;
        }
        
        $u->title = $data['seecmswebsiteusertitle'];
        $u->forename = $data['seecmswebsiteuserforename'];
        $u->surname = $data['seecmswebsiteusersurname'];
        $u->organisation = $data['seecmswebsiteuserorganisation'];
        $u->address1 = $data['seecmswebsiteuseraddress1'];
        $u->address2 = $data['seecmswebsiteuseraddress2'];
        $u->address3 = $data['seecmswebsiteuseraddress3'];
        $u->city = $data['seecmswebsiteusercity'];
        $u->region = $data['seecmswebsiteuserregion'];
        $u->postcode = $data['seecmswebsiteuserpostcode'];
        $u->country = $data['seecmswebsiteusercountry'];
        $u->email = $data['seecmswebsiteuseremail'];
        $u->telephone = $data['seecmswebsiteusertelephone'];
        
        if( $data['seecmswebsiteuserpassword'] ) {
        
          $changePassword = true;
        
          if( $u->id ) {
          
            $changePassword = false;
          
            if( $u->passwordformat == 'md5' ) {
            
              $data['seecmswebsiteuserpassword'] = md5( $data['seecmswebsiteuserpassword'] );
              $password = strtolower( $u->password );
              $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
            } else if( $u->passwordformat == 'aes256' ) {
        
              $password = $this->see->security->decAES256( $u->password );
              $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
            } else {
              
              $passwordCheck = password_verify($data['seecmswebsiteuserpassword'],$u->password);
            }
            
            if( $passwordCheck ) {
              $changePassword == true;
            }
              
          }
        
          if( $changePassword ) {
            $u->password = password_hash( $data['seecmswebsiteuserpassword'], PASSWORD_BCRYPT );
            $u->passwordformat = 'hash';
          }
        }
        
        SeeDB::store( $u );
        
        if( $newUser ) {
        
          $wug = SeeDB::find( 'websiteusergroup', ' autoaddnewusers = ? ', array( 1 ) );
          foreach( $wug as $g ) {
          
            $u->sharedWebsiteusergroup[] = $g;
          }
        }
            
        SeeDB::store( $u );
        
        $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] = $u->id;
        $_SESSION['seecms'][$this->see->siteID]['websiteuser']['forename'] = $u->forename;
        $_SESSION['seecms'][$this->see->siteID]['websiteuser']['surname'] = $u->surname;
        $_SESSION['seecms'][$this->see->siteID]['websiteuser']['email'] = $u->email;
        $ret['result'] = 1;
          
        if( $settings['settings']['successredirect'] ) {
        
          $this->see->redirect( $settings['settings']['successredirect'] );
        }
      }
    }
    
    return( $ret );
  }
  
  public function logout() {
  
    $_SESSION['seecms'][$this->see->siteID]['websiteuser'] = '';
  }
  
  public function loginForm( $settings ) {
  
    if( $_GET['logout'] ) {
    
      $this->logout();
      $this->see->redirect( './' );
    }
  
    if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) && !$_GET['preview'] && isset( $settings['redirect'] ) && !$settings['onlyRedirectOnLogin'] ) {
    
      $this->see->redirect( $settings['redirect'] );
    }
  
    $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
		$formSettings['controller']['method'] = 'login';
    $formSettings['controller']['class'] = "seecmswebsiteuserlogin";
    $formSettings['controller']['settings']['successredirect'] = @$settings['redirect'];

		$formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'email';
		$formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter a valid email address.';
		$formSettings['validate']['seecmswebsiteuserpassword']['validate'] = 'required';
		$formSettings['validate']['seecmswebsiteuserpassword']['error'] = 'Please enter your password.';
    
    $formSettings['validate']['address4']['validate'] = 'mustbe=';
    
    ob_start();
    
    $f = $this->see->html->form( $formSettings ); 
    
    if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {
    
      ob_clean();
      echo "<p class=\"seecmsloggedinmessage\">{$settings['loggedInMessage']}</p>";
    
    } else {

      echo "<div class=\"input\"><label for=\"email\">Email address</label><br />";
      $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => '') );
      echo "</div><div class=\"input\"><label for=\"password\">Password</label><br />";
      $f->password( array( 'name' => 'seecmswebsiteuserpassword', 'value' => '') );
      echo "</div><div style=\"display: none;\" class=\"input\" id=\"seecmssc\">";
      /* spam catcher */ 
      $f->text( array( 'name' => 'seecmssc', 'value' => '') );
      echo "</div><div class=\"clear\"></div>";
      $f->submit( array( 'name' => 'seecmswebsiteuserlogin', 'class' => 'submitbutton', 'value' => 'Login') );
      $f->close();
    
    }
    
    return( ob_get_clean() );
  }
  
  public function registerForm( $settings ) {

    $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
    $formSettings['controller']['method'] = 'selfUpdate';
    $formSettings['controller']['class'] = "seecmswebsiteuserregister";
    $formSettings['controller']['settings']['successredirect'] = $settings['redirect'];

    $formSettings['validate']['seecmswebsiteuserforename']['validate'] = 'required';
    $formSettings['validate']['seecmswebsiteuserforename']['error'] = 'Please enter your first name.';
    $formSettings['validate']['seecmswebsiteusersurname']['validate'] = 'required';
    $formSettings['validate']['seecmswebsiteusersurname']['error'] = 'Please enter your last name.';
    $formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'email';
    $formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter a valid email address.';
    $formSettings['validate']['seecmswebsiteusertelephone']['validate'] = 'required';
    $formSettings['validate']['seecmswebsiteusertelephone']['error'] = 'Please enter a contact telephone number.';
    
    if( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
      
      $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID][$this->see->siteID]['websiteuser']['id'] );
    } else {
    
      $formSettings['validate']['seecmswebsiteuserpassword']['validate'] = 'required';
      $formSettings['validate']['seecmswebsiteuserpassword']['error'] = 'Please enter a password.';
      $formSettings['validate']['seecmswebsiteuserconfirmpassword']['validate'] = 'required';
      $formSettings['validate']['seecmswebsiteuserconfirmpassword']['error'] = 'Please confirm your password.';
    }
    
    $formSettings['validate']['seecmswebsiteuseraddress4']['validate'] = 'mustbe=';
    
    ob_start();

    echo '<div class="seecmswebsiteuserregisteruser">';
    $f = $this->see->html->form( $formSettings );
		
		echo '<div class="clear"></div>';
		echo '<h2>Your details</h2>';
		echo '<div class="input"><label for="seecmswebsiteusertitle">Title</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusertitle', 'value' => $u->title ) );
    echo '</div><div class="clear"></div>';
		echo '<div class="input"><label for="seecmswebsiteuserforename">First Name*</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserforename', 'value' => $u->forename ) );
    echo '</div><div class="input"><label for="seecmswebsiteusersurname">Last Name*</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusersurname', 'value' => $u->surname ) );
    echo '</div><div class="input"><label for="seecmswebsiteuserorganisation" style="line-height: 100%">Company / Organisation</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserorganisation', 'value' => $u->organisation ) );
    echo '</div><div class="clear"></div><div class="input"><label for="seecmswebsiteusertelephone">Telephone*</label><br/>';
    $f->text( array( 'name' => 'seecmswebsiteusertelephone', 'value' => $u->telephone ) );
    echo '</div><div class="input"><label for="seecmswebsiteuseremail">Email*</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => $u->email ) );
    echo '</div><div class="clear"></div><hr /><h2>Address</h2>';
		echo '<div class="input"><label for="seecmswebsiteuseraddress1">Address 1</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress1', 'value' => $u->address1 ) );
    echo '</div><div class="input"><label for="seecmswebsiteuseraddress2">Address 2</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress2', 'value' => $u->address2 ) );
    echo '</div><div class="input"><label for="seecmswebsiteuseraddress3">Address 3</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress3', 'value' => $u->address3 ) );
    echo '</div><div style="display: none;" class="input" id="seecmswebsiteuseraddress4">';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress4', 'value' => '') );
    echo '</div><div class="input"><label for="seecmswebsiteusercity">Town/City</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusercity', 'value' => $u->city ) );
    echo '</div><div class="input"><label for="seecmswebsiteuserregion">Region</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserregion', 'value' => $u->region ) );
    echo '</div><div class="input"><label for="seecmswebsiteuserpostcode">Postcode</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserpostcode', 'value' => $u->postcode ) );
    echo '</div><div class="input"><label for="seecmswebsiteusercountry">Country</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusercountry', 'value' => $u->country ) );
    echo '</div><div class="clear"></div><hr /><h2>Registration details</h2>';
		
    $pRequired = '*';
    
    if( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
      echo "<p>Please leave these fields blank unless you'd like to set a new password.</p>";
      $pAppend = 'New ';
      $pRequired = '';
      
      echo '<div class="input"><label for="seecmswebsiteuserpasswordcurrent">Current password'.$pRequired.'</label><br />';
      $f->password( array( 'name' => 'seecmswebsiteuserpassword', 'value' => '') );
      echo '</div><div class="clear"></div>';
    }
    
		echo '<div class="input"><label for="seecmswebsiteuserpassword">Password'.$pRequired.'</label><br />';
    $f->password( array( 'name' => 'seecmswebsiteuserpassword', 'value' => '') );
    echo '</div><div class="input"><label for="seecmswebsiteuserconfirmpassword" style="line-height: 100%">Confirm password'.$pRequired.'</label><br />';
    $f->password( array( 'name' => 'seecmswebsiteuserconfirmpassword', 'value' => '') );
    echo '</div><div class="clear"></div>';
		
		$f->hidden( array( 'name' => 'r', 'value' => $settings['redirect'] ) );
		$f->submit( array( 'name' => 'submit', 'class' => 'submitbutton', 'value' => ((!$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'])?'Register':'Update') ) );
    
		echo '<div class="clear"></div>';
		$f->close();
    echo '</div>';
    
    return( ob_get_clean() );
  }
  
  public function passwordRecoveryForm( $settings ) {
  
    if( ( $_GET['email'] && $_GET['passwordrecovery'] ) || ( $_POST['seecmswebsiteuserpasswordrecoveryemail'] && $_POST['seecmswebsiteuserpasswordrecoverycode'] ) ) {
    
      if( $_GET['email'] ) {
        $email = $_GET['email'];
        $passwordrecovery = $_GET['passwordrecovery'];
      } else {
        $email = $_POST['seecmswebsiteuserpasswordrecoveryemail'];
        $passwordrecovery = $_POST['seecmswebsiteuserpasswordrecoverycode'];
      }
    
      $u = SeeDB::findOne( 'websiteuser', ' email = ? && passwordrecovery = ? ', array( $email, $passwordrecovery ) );
      if( $u ) {
    
        echo $this->changePasswordForm( $settings );
      } else {
      
        echo "<p style=\"seecmspasswordrecoveryerror\">Sorry. Those details are incorrect. Please try again.</p>";
      }
    
    } else {
  
      $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
      $formSettings['controller']['method'] = 'passwordRecovery';
      $formSettings['controller']['class'] = "seecmswebsiteuserpasswordrecovery";
      $formSettings['controller']['settings']['emailFrom'] = $settings['emailFrom'];
      $formSettings['controller']['settings']['emailSubject'] = $settings['emailSubject'];

      $formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'email';
      $formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter a valid email address.';
      
      $formSettings['validate']['seecmssc']['validate'] = 'mustbe=';
      
      ob_start();
      
      $f = $this->see->html->form( $formSettings ); 
      
      if( $f->returndata['complete'] ) {
      
        ob_clean();
        echo "<p style=\"seecmspasswordrecorymessage\">{$settings['recoveryEmailSentThankYouMessage']}</p>";
      
      } else {

        echo "<div class=\"input\"><label for=\"email\">Email address</label><br />";
        $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => '') );
        echo "</div><div style=\"display: none;\" class=\"input\" id=\"seecmssc\">";
        /* spam catcher */ 
        $f->text( array( 'name' => 'seecmssc', 'value' => '') );
        echo "</div><div class=\"clear\"></div>";
        $f->submit( array( 'name' => 'seecmswebsiteuserpasswordrecovery', 'class' => 'submitbutton', 'value' => 'Recover password') );
        $f->close();
      
      }
    }
    
    return( ob_get_clean() );
  }
  
  public function changePasswordForm( $settings ) {
  
    $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
    $formSettings['controller']['method'] = 'changePassword';
    $formSettings['controller']['class'] = "seecmswebsiteuserpasswordrecovery";

    $formSettings['validate']['seecmswebsiteuserpassword']['validate'] = 'minlength=8';
    $formSettings['validate']['seecmswebsiteuserpassword']['error'] = 'Please ensure your password is a minimum of 8 characters';
    
    $formSettings['validate']['seecmssc']['validate'] = 'mustbe=';
    
    ob_start();
    
    $f = $this->see->html->form( $formSettings ); 
    
    if( $f->returndata['complete'] ) {
    
      ob_clean();
      echo "<p style=\"seecmspasswordrecorymessage\">{$settings['passwordChangedThankYouMessage']}</p>";
    
    } else {

      echo "<div class=\"input\"><label for=\"email\">Password</label><br />";
      $f->password( array( 'name' => 'seecmswebsiteuserpassword' ) );
      echo "</div><div class=\"input\"><label for=\"email\">Confirm password</label><br />";
      $f->password( array( 'name' => 'seecmswebsiteuserconfirmpassword' ) );
      echo "</div><div style=\"display: none;\" class=\"input\" id=\"seecmssc\">";
      /* spam catcher */ 
      $f->text( array( 'name' => 'seecmssc', 'value' => '') );
      echo "</div><div class=\"clear\"></div>";
      $f->hidden( array( 'name' => 'seecmswebsiteuserpasswordrecoverycode', 'value' => $_GET['passwordrecovery'] ) );
      $f->hidden( array( 'name' => 'seecmswebsiteuserpasswordrecoveryemail', 'value' => $_GET['email'] ) );
      $f->submit( array( 'name' => 'seecmswebsiteuserpasswordrecovery', 'class' => 'submitbutton', 'value' => 'Set password') );
      $f->close();
    
    }
    
    return( ob_get_clean() );
  }
  
  public function passwordRecovery( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $data['seecmswebsiteuseremail'] ) );
      if( $u ) {
        
        $u->passwordrecovery = md5( microtime().rand(0,10000000) );
        SeeDB::store( $u );
        
        $emailTemplate = SeeDB::findOne('setting', " name = 'email' ");
        
        $link = 'http'.((empty( $_SERVER['HTTPS'])) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?email={$u->email}&amp;passwordrecovery={$u->passwordrecovery}";
        
        $emailContent = "<h2>{$settings['settings']['emailSubject']}</h2><hr /><p>Please visit the link below to reset your password.<br /><a href=\"{$link}\">{$link}</a></p>";
        
        $email = str_replace( '<EMAILCONTENT>', $emailContent, $emailTemplate->value );
        
        $seeemail = new SeeEmailController();
        $seeemail->sendHTMLEmail( $settings['settings']['emailFrom'], $u->email, $email, $settings['settings']['emailSubject'] );
        
        $ret['complete'] = true;
        
      } else {
        $ret['errors']['email'] = 'User not found.';
      }
    }
    
    return( $ret );
  }
  
  public function changePassword( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      $u = SeeDB::findOne( 'websiteuser', ' email = ? && passwordrecovery = ? ', array( $data['seecmswebsiteuserpasswordrecoveryemail'], $data['seecmswebsiteuserpasswordrecoverycode'] ) );
      
      if( !$u->id ) {
        $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] );
      }
      
      if( $u ) {
        
        if( $data['seecmswebsiteuserpassword'] != $data['seecmswebsiteuserconfirmpassword'] ) {
        
          $ret['errors']['seecmswebsiteuserconfirmpassword'] = 'Please ensure the password and confirm password fields match.';
        } else {
        
          $u->password = password_hash( $data['seecmswebsiteuserpassword'], PASSWORD_BCRYPT );
          $u->passwordformat = 'hash';
          $u->passwordrecovery = '';
          
          SeeDB::store( $u );
          
          $ret['complete'] = true;
        }
      }
    }
    
    return( $ret );
  }
  
  public function loadAllGroups() {
  
    $ag = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    
    return( $ag );
  }
  
  public function loadGroupForEdit( $id = 0 ) {
  
    if( !$id ) {
      $id = $_GET['id'];
    }
  
    $d['group'] = SeeDB::load( 'websiteusergroup', $id );
    $d['users'] = SeeDB::findAll( 'websiteuser', ' ORDER BY surname, forename ' );
    
    return( $d );
  }
  
  public function updateGroup( $data, $errors, $settings ) {
  
    if( !$errors ) {
    
      $g = SeeDB::load( 'websiteusergroup', $data['id'] );
      $g->name = $data['name'];
      
      SeeDB::store( $g );
      
      foreach( $data as $dK => $dV ) {
      
        if( substr( $dK, 0, 18 ) == 'seecmswebsiteuser-' ) {
        
          $wu = SeeDB::load( 'websiteuser', substr( $dK, 18 ) );
          $g->sharedWebsiteuser[] = $wu;
        }
      }
      
      SeeDB::store( $g );
      $this->see->redirect( "./?id={$g->id}" );
    }
    
    return( $errors );
  }
  
  public function deleteGroup() {
  
    $wug = SeeDB::load( 'websiteusergroup', $_POST['id'] );
    SeeDB::trash( $wug );
    echo 'Done';
  }
  
  public static function setPermission( $objectID, $objectType, $groupIDs ) {
  
    // Delete old permissions
    SeeDB::exec( " DELETE FROM websiteusergrouppermission WHERE objectid = {$objectID} && objecttype = '{$objectType}' " );
  
    if( is_array( $groupIDs ) ) {
      foreach( $groupIDs as $g ) {
        // Insert permission
        $gp = SeeDB::dispense( 'websiteusergrouppermission' );
        $gp->objectid = $objectID;
        $gp->objecttype = strtolower( $objectType );
        $gp->websiteusergroup_id = $g;
        SeeDB::store( $gp );
      }
    }
  }
  
  public static function cascadePermission( $objectID, $objectType, $groupIDs ) {
  
    $obs = SeeDB::find( $objectType, ' parentid = ? && deleted = ? ', array( $objectID, '0000-00-00 00:00:00' ) );
    
    if( is_array( $obs ) ) {
      foreach( $obs as $o ) {
        // Insert permission
        SeeCMSWebsiteUserController::setPermission( $o->id, $objectType, $groupIDs );
        // Cascade again
        SeeCMSWebsiteUserController::cascadePermission( $o->id, $objectType, $groupIDs );
      }
    }
  }
}