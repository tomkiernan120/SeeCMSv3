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
    $random_hash1 = "------_=_NextPart_001_{$random_hash}";
    
    $replyto = (( $replyto ) ? $replyto : $from );

    $headers = "From: {$from}\r\nReply-To: {$replyto}";
    $headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed;
    boundary=\"----_=_NextPart_001_{$random_hash}\"";

    $emailTxt .= wordwrap( strip_tags( $email ), 70, "\r\n" );
    
    ob_start(); //Turn on output buffering


    echo "\r\n\r\nThis is a multi-part message in MIME format.\r\n\r\n";

    echo $random_hash1; 

    echo "\r\nContent-Type: multipart/alternative;\r\n	boundary=\"----_=_NextPart_002_{$random_hash}\"\r\n------_=_NextPart_002_{$random_hash}\r\nContent-Type: text/plain; charset=\"utf-8\"\r\nContent-Transfer-Encoding: 7bit\r\n\r\n".trim($emailTxt)."\r\n\r\n------_=_NextPart_002_{$random_hash}\r\nContent-Type: text/html; charset=\"utf-8\"\r\nContent-Transfer-Encoding: 7bit\r\n\r\n".trim($email)."\r\n\r\n------_=_NextPart_002_{$random_hash}--\r\n";

    if( is_array( $files ) ) {
      foreach( $files as $v ) {
        if( $v[0] ) {
          echo "------_=_NextPart_001_{$random_hash}\r\nContent-Type: application/octet-stream; name=\"{$v[1]}\"\r\nContent-Transfer-Encoding: base64\r\n";
          echo "Content-Description: {$v[1]}\r\nContent-Disposition: attachment; filename=\"{$v[1]}\"\r\n\r\n";
          echo chunk_split( base64_encode( file_get_contents( $v[0] ) ) );
        }
      }
    }

    echo "\r\n\r\n------_=_NextPart_001_{$random_hash}--";

    //copy current buffer contents into $message variable and delete current output buffer
    $message = ob_get_clean();
    
    if( $useUnixLineBreaksInEmail ) {
      $message = str_replace( "\r\n", "\n", $message );
    }
        
    return( mail( $to, $subject, $message, $headers ) );
  }
}