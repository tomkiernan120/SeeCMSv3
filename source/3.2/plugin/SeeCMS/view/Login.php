<seecmsheader>
<div class="main loginpage">

<div class="loginbox">
  <img src="/seecms/images/login-logo.png" alt="" />
  <div class="loginfields">
<?php 

$formSettings['controller']['name'] = 'SeeCMSAdminAuthentication';
$formSettings['controller']['method'] = 'login';

$formSettings['validate']['email']['validate'] = 'required,email';
$formSettings['validate']['email']['error'] = 'Please enter a valid email address.';
$formSettings['validate']['password']['validate'] = 'required';
$formSettings['validate']['password']['error'] = 'Please enter your password.';

$f = $see->html->form( $formSettings );

?>
  <p>Email</p>
  <p><?php $f->text( array( 'name' => 'email' ) ); ?></p>
  <p>Password</p>
  <p><?php $f->password( array( 'name' => 'password' ) ); ?></p>
  <p><?php $f->submit( array( 'value' => 'Log in', 'id' => 'submit' ) ); ?></p>
  <div class="clear"></div>
  <?php $f->close(); ?>
  </div>
</div>

<seecmsfooternosearch>