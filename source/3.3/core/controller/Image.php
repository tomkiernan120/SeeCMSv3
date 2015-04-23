<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeImageController {

  public function prepare( $image, $newImage, $width, $height, $ext, $constrain = false, $stretch = false, $settings = '' ) {
  
    $img = $this->load( $image, $ext );
    
    $imgWidth = imagesx( $img );
    $imgHeight = imagesy( $img );
  
    $ow = $width/$imgWidth;
    $oh = $height/$imgHeight;
    
    if( $width && $height && !$constrain ) {
      $newImg = $this->imageResizeCrop( $img, $width, $height, $ext );
    } else if( $ow <= $oh ) {
      $newImg = $this->imageSetWidth( $img, (( $width > $imgWidth && !$stretch ) ? $imgWidth : $width ), $ext );
    } else {
      $newImg = $this->imageSetHeight( $img, (( $height > $imgHeight && !$stretch ) ? $imgHeight : $height ), $ext );
    }
    
    if( $newImg ) {
      $newImg['status'] = $this->save( $newImg['img'], $newImage, $settings );
      return( $newImg );
    } else {
      $newImg['status'] = false;
      return( $newImg );
    }
  }
  
  private function imageSetWidth( $img, $width, $ext ) {
  
    // Get current image dimensions
    $ow = imagesx( $img );
    $oh = imagesy( $img );
    
    // Work out new height
    $height = $oh * ( $width / $ow );
    
    // Create new image 
    $newImg = imageCreateTrueColor( $width, $height );
    
    // Resample old image onto new image
    if( $height != $oh && $width != $ow ) {
      if( $ext == "png" ) {
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);  
      } 
      imageCopyResampled( $newImg, $img, 0, 0, 0, 0, $width, $height, $ow, $oh );
    } else {
      imageCopy( $newImg, $img, 0, 0, 0, 0, $width, $height );
    }
    
    // Return new image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function imageSetHeight( $img, $height, $ext ) {
  
    // Get current image dimensions
    $ow = imagesx( $img );
    $oh = imagesy( $img );
    
    // Work out new width
    $width = $ow * ( $height / $oh );
    
    // Create new image 
    $newImg = imageCreateTrueColor( $width, $height );
    
    // Resample old image onto new image
    if( $height != $oh && $width != $ow ) {
      if ($ext == "png") {
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);  
      } 
      imageCopyResampled( $newImg, $img, 0, 0, 0, 0, $width, $height, $ow, $oh );
    } else {
      imageCopy( $newImg, $img, 0, 0, 0, 0, $width, $height );
    }
    
    // Return new image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function imageResizeCrop( $img, $width, $height, $ext ) {
 
    // Get size of source image
    $sw = imagesx( $img );
    $sh = imagesy( $img );

    // Use source dimensions if target width/height is 0  
    $width = ( $width ? $width : $sw );
    $height = ( $height ? $height : $sh );

    // Create destination image at target size
    $newImg = imageCreateTrueColor( $width, $height );
      
    // Work out size ratios
    $rw = $width / $sw;
    $rh = $height / $sh;
      
    // Use lowest ratio as 'full' side and work out displacement for other axis
    if ( $rw < $rh ) {
      $src_w = $width / $rh;
      $src_h = $height / $rh;
      $src_x = ( $sw - $src_w ) / 2;
      $src_y = 0;
    } else {
      $src_w = $width / $rw;
      $src_h = $height / $rw;
      $src_x = 0;
      $src_y = ( $sh - $src_h ) / 2;
    }
      
    if(  $ext == "png"  ) {
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);  
    }  
    
    // Resize the image
    imageCopyResampled( $newImg, $img, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h );

    // Return the resized image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function load( $filename, $ext = '' ) {
  
    $ext = strtolower( $ext );
    
    switch( $ext ) {
      case 'jpg':
      case 'jpeg':
        $img = imageCreateFromJPEG( $filename );
        break;
    
      case 'gif':
        $img = imageCreateFromGIF( $filename );
        break;
      
      case 'png':
        $img = imageCreateFromPNG( $filename );
        break;
      
      default:
        return false;
        break;
    }
    
    return $img;
  }
  
  private function save( $img, $filename, $settings ) {

    $ext = SeeHelperController::getFileExtension( $filename );

    $settings = json_decode( $settings );
    if( isset( $settings->filters ) ) {
      foreach( $settings->filters as $f ) {
        imagefilter( $img, constant($f[0]), (int)$f[1], (int)$f[2], (int)$f[3], (int)$f[4] );
      }
    }
    
    switch( $ext ) {
      case 'jpg':
      case 'jpeg':
        imageJPEG( $img, $filename, 100 );
        break;
    
      case 'gif':
        imageGIF( $img, $filename );
        break;
      
      case 'png':
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagePNG( $img, $filename );
        break;
      
      default:
        return false;
        break;
    }

    return true;
  }

}