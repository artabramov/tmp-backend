<?php 
$a = __DIR__; 
$b = 1;
?>

<script>
const ECHIDNA_URI = 'http://project.local';
const ECHIDNA_DEBUG = true;
</script>

<!-- header -->
<?php require_once(__DIR__ . '/header.php'); ?>

<!-- navbar -->
<?php require_once(__DIR__ . '/navbar.php'); ?>

<!-- modals -->
<?php require_once(__DIR__ . '/modals/register.php'); ?>
<?php require_once(__DIR__ . '/modals/registered.php'); ?>
<?php require_once(__DIR__ . '/modals/restore.php'); ?>
<?php require_once(__DIR__ . '/modals/restored.php'); ?>
<?php require_once(__DIR__ . '/modals/signin.php'); ?>
<?php require_once(__DIR__ . '/modals/signined.php'); ?>
<?php require_once(__DIR__ . '/modals/signouted.php'); ?>

<!-- events -->
<?php require_once(__DIR__ . '/events/register.php'); ?>
<?php require_once(__DIR__ . '/events/restore.php'); ?>
<?php require_once(__DIR__ . '/events/signin.php'); ?>
<?php require_once(__DIR__ . '/events/user_auth.php'); ?>
<?php require_once(__DIR__ . '/events/user_signout.php'); ?>



<!-- body -->
<a href="http://project.local/register">register</a>
<br>
<a href="http://project.local/restore">restore</a>
<br>
<a href="http://project.local/signin">signin</a>

        

</body>
</html>