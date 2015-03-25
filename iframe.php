<?php

/* 
 * Copyright (C) 2015 Nathan Crause - All rights reserved
 *
 * This file is part of Galleryviewer
 *
 * Copying, modification, duplication in whole or in part without
 * the express written consent of the copyright holder is
 * expressly prohibited under the Berne Convention and the
 * Buenos Aires Convention.
 */

require_once '../common/core.php';
require_once '../common/connect.php';
require_once '../common/sessioncheck.php';
require_once '../common/templater.php';

$component = new GalleryviewerComponent();

?>
<!html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" name="htmlheader:meta_ctype" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="author" content="Claromentis Ltd (www.claromentis.com)" />
		<meta name="viewport" content="width=device-width,initial-scale=1">

		<link type="text/css" href="/interface_default/css/bootstrap.min.css" media="screen" rel="stylesheet" />
		<?php if (file_exists("../interface_{$_SESSION['skin']}/css/style.css")): ?>
		<link type="text/css" href="/interface_<?= $_SESSION['skin'] ?>/css/style.css" media="screen" rel="stylesheet" />
		<?php endif ?>
		
		<script type="text/javascript" src="/intranet/js/jquery.min.js"></script>
	</head>
	
	<body id="galleryviewer-iframe">
		<?= $component->Show(array_merge(GalleryviewerComponent::$DEFAULTS, $_GET)) ?>
		
		<script type="text/javascript" src="/intranet/js/bootstrap/bootstrap.min.js"></script>
	</body>
</html>
