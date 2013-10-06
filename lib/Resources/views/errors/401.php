<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
$words = include 'words.php';
$word = $words [array_rand ( $words, 1 )];
?>
<!DOCTYPE html>
<html>
<head>
<title>Error : Authentication required</title>
<meta charset="utf-8">
<link href="<?=Config::cdn()?>/vendor/css/bootstrap.min.css" rel="stylesheet" media="screen">
<?php if(Config::$a['useMinifiedFiles'] && is_file(_STATICDIR .'/errors/css/style.min.css')):?>
<link href="<?=Config::cdn()?>/errors/css/style.min.css" rel="stylesheet" media="screen">
<?php else: ?>
<link href="<?=Config::cdn()?>/errors/css/style.css" rel="stylesheet" media="screen">
<?php endif; ?>
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="error forbidden">

	<?php include'top.php'?>

	<section id="header-band">
		<div class="container">
			<header class="hero-unit" id="overview">
				<div class="clearfix">
					<h1><strong><?=$word?>!</strong> Authentication required</h1>
					<p>This request requires you to login. <br />Would you like to <a href="/">return to the start</a>?</p>
				</div>
				<div id="destiny-illustration"></div>
			</header>
		</div>
	</section>

	<?php include'foot.php'?>

	<script src="<?=Config::cdn()?>/vendor/js/jquery-1.10.2.min.js"></script>
	<script src="<?=Config::cdn()?>/vendor/js/bootstrap.js"></script>

</body>
</html>