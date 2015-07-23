<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeFormController extends SeeHTMLController {

  var $errors;
  var $data;
  var $submittedData;
  var $see;
  
  public function __construct( $see ) {
    $this->see = $see;
  }

  public function open( $settings = array() ) {
  
    if( !$settings['attributes']['action'] ) {
      $settings['attributes']['action'] = './';
    }
    
    if( !$settings['attributes']['method'] ) {
      $settings['attributes']['method'] = 'post';
    }
    
    if( $settings['attributes']['method'] == 'post' ) {
      $this->submittedData = $_POST;
      $this->submittedData['files'] = $_FILES;
    } else {
      $this->submittedData = $_GET;
    }
  
    if( isset( $settings['controller']['name'] ) ) {
      $formname = "seeform-{$settings['controller']['name']}-{$settings['controller']['method']}-".$this->see->html->forms;
      if( $this->submittedData[$formname] ) {

        if( count( $this->submittedData ) ) {
          $this->errors = $this->validateAll( $settings['validate'] );
        }
    
        $sControllerName = $settings['controller']['name']."Controller";
        $sController = new $sControllerName( $this->see );
        if( $settings['controller']['method'] ) {
          $this->returndata = $sController->$settings['controller']['method']( $this->submittedData, $this->errors, $settings['controller'] );
          if( is_array( $this->returndata ) ) {
            if( is_array($this->returndata['errors']) ) {
              $this->errors = array_merge($this->errors,$this->returndata['errors']);
            }
          } else {
            $this->data = $this->returndata['data'];
          }
        }
      }
    }
    
    $this->makeTag( 'form', true, $settings['attributes'] );
    
    if( $formname ) {
      $this->hidden( array( 'name' => $formname, 'value' => 1 ) );
    }
    
    if( !$settings['disableStandardErrors'] ) {
      $this->displayErrors();
    }
  }
  
  public function close() {
    
    $this->output( '</form>');
  }
  
  public function text( $attributes = array(), $settings = array() ) {
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes, '', $settings['format'] );
    }
    
    $attributes['type'] = 'text';
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function textarea( $attributes = array(), $settings = array() ) {
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes );
    }
    
    $value = $attributes['value'];
    unset($attributes['value']);
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'textarea', true, $attributes );
    $this->output( $value, 1 );
    $this->makeTag( 'textarea', false, $attributes, false );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function password( $attributes = array(), $settings = array() ) {
    
    if( $settings['reload'] === true ) {
      $attributes = $this->reloadData( $attributes );
    }
    
    $attributes['type'] = 'password';
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function submit( $attributes = array(), $settings = array() ) {
    
    $attributes['type'] = 'submit';
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function button( $attributes = array(), $settings = array() ) {
    
    $attributes['type'] = 'button';
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function reset( $attributes = array(), $settings = array() ) {
    
    $attributes['type'] = 'reset';
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function select( $attributes = array(), $settings = array() ) {
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes );
      $value = $attributes['value'];
      $attributes['value'] = '';
    }
    
    $this->makeTag( $settings['tag'] );
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
    $this->makeTag( 'select', true, $attributes );
    
    if( is_array( $settings['options'] ) ) {
      foreach( $settings['options'] as $k => $v ) {
        if( $settings['optionValueOnly'] ) {
          $k = $v;
        }
        $this->output( "<option value=\"{$k}\"".(($k==$value)?' selected="selected"':'').">{$v}</option>" );
      }
    }
    
    
    $this->makeTag( 'select', false, $attributes );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function hidden( $attributes = array(), $settings = array() ) {
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes );
    }
    
    $attributes['type'] = 'hidden';
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function file( $attributes = array(), $settings = array() ) {
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes );
    }
    
    $attributes['type'] = 'file';
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function checkbox( $attributes = array(), $settings = array() ) {
    
    $attributes['type'] = 'checkbox';
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes, 'checked' );
    }
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  public function radio( $attributes = array(), $settings = array() ) {
    
    $attributes['type'] = 'radio';
    $attributes['class'] .= (($this->errors[$attributes['name']])?' seeformvalidationerror':'');
  
    if( $settings['reload'] !== false ) {
      $attributes = $this->reloadData( $attributes, 'checked' );
    }
    
    $this->makeTag( $settings['tag'] );
    $this->makeTag( 'input', true, $attributes, true );
    $this->makeTag( $settings['tag'], 0 );
  }
  
  private function reloadData( $attributes = array(), $type = '', $format = '' ) {
  
    if( $attributes['type'] == 'checkbox' ) {
      if( $attributes['value'] ) {
        $attributes[$type] = $type;
      }
    } else if( $_GET[$attributes['name']] && (( $attributes['type'] != 'radio' ) || ( $_GET[$attributes['name']] == $attributes['value'] )) ) {
      if( $type ) {
        $attributes[$type] = $type;
      } else {
        $attributes['value'] = $_GET[$attributes['name']];
      }
    } else if( $_POST[$attributes['name']] && (( $attributes['type'] != 'radio' ) || ( $_POST[$attributes['name']] == $attributes['value'] )) ) {
      if( $type ) {
        $attributes[$type] = $type;
      } else {
        $attributes['value'] = $_POST[$attributes['name']];
      }
    } else if( $this->data[$attributes['name']] && (( $attributes['type'] != 'radio' ) || ( $this->data[$attributes['name']] == $attributes['value'] )) ) {
      if( $type ) {
        $attributes[$type] = $type;
      } else {
        $attributes['value'] = $this->data[$attributes['name']];
      }
    }
    
    if( $format ) {
      $formatController = new SeeFormatController();
      $attributes['value'] = $formatController->$format[0]( $attributes['value'], $format[1] );
    }
    
    return( $attributes );
  }
  
  private function validateAll( $fields ) {
  
    $errors = array();
    
    if( is_array( $fields ) ) {
      foreach( $fields as $k => $v ) {
        $validate = explode( ',', $v['validate'] );
        foreach( $validate as $dv ) {
          $error = $this->validate( $k, $dv );
          if( $error ) {
            $errors[$k] = $v['error'];
          }
        }
      }
    }
    
    return( $errors );
  }
  
  private function validate( $name, $type ) {
    
    // returns true if error
    $rdn = $this->reloadData( array( 'name' => $name ) );
    $value = $rdn['value'];
    
    $typeData = explode( '=', $type );
    $typeName = $typeData[0];
    
    if( method_exists('SeeValidationController',$typeName) ) {
      $valid = SeeValidationController::$typeName( $value, $type );
      if( !$valid ) {
        return( true );
      }
    } else {
      $this->see->error( "Validation method {$typeName} doesn't exist." );
    }
    
  }
  
  public function displayErrors() {
    
    if( count( $this->errors ) ) {
      $this->output( "<p class=\"seeformerrors\">" );
      foreach( $this->errors as $e ) {
        $this->output( $e."<br />" );
      }
      $this->output( "</p>" );
    }
  }

}