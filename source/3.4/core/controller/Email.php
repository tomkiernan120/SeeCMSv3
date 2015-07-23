<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeEmailController {

  function sendHTMLEmail( $from, $to, $email, $subject, $files = '', $replyTo = '', $txtOnly = '', $useUnixLineBreaksInEmail = false )
  {
    ini_set( 'sendmail_from', $from );
        
    $random_hash = md5(time());
    $mixedB = "SEECMS-MIXED-{$random_hash}";
    $altB = "SEECMS-ALT-{$random_hash}";
    
    $replyto = (( $replyto ) ? $replyto : $from );

    $headers = "From: {$from}\r\nReply-To: {$replyto}";
    
    $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"$mixedB\"";

    $emailTxt .= wordwrap( strip_tags( $email ), 70, "\r\n" );
    
    ob_start(); //Turn on output buffering

    echo "--{$mixedB}\r\n";
    echo "Content-Type: multipart/alternative; boundary=\"{$altB}\"\r\n\r\n";

    echo "--{$altB}\r\n";
    echo "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
    echo "Content-Transfer-Encoding: 7bit\r\n\r\n";
    
    echo $emailTxt;

    echo "\r\n\r\n--{$altB}\r\n";
    echo "Content-Type: text/html; charset=\"iso-8859-1\"\r\n";
    echo "Content-Transfer-Encoding: 7bit\r\n\r\n";
    
    echo $email;
    
    echo "\r\n\r\n--{$altB}--\r\n\r\n";

    if( is_array( $files ) ) {
      foreach( $files as $v ) {
        if( $v[0] || $v[2] ) {
          echo "--{$mixedB}\r\nContent-Type: application/octet-stream; name=\"{$v[1]}\"\r\nContent-Transfer-Encoding: base64\r\n";
          echo "Content-Disposition: attachment\r\n\r\n";
          if( $v[2] ) {
            echo chunk_split( base64_encode( $v[2] ) );
          } else {
            echo chunk_split( base64_encode( file_get_contents( $v[0] ) ) );
          }
        }
      }
    }

    echo "--{$mixedB}--\r\n";

    //copy current buffer contents into $message variable and delete current output buffer
    $message = ob_get_clean();
    
    if( $useUnixLineBreaksInEmail ) {
      $message = str_replace( "\r\n", "\n", $message );
    }
        
    return( mail( $to, $subject, $message, $headers ) );
  }
}