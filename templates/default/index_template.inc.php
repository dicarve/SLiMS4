<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="<?php echo $language_code; ?>" dir="<?php echo $text_direction; ?>"><head><title><?php echo $page_title; ?></title><meta http-equiv="Content-Type" content="text/html; charset=<?php echo $page_charset; ?>" />
<link rel="icon" href="<?php echo $webicon; ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo $webicon; ?>" type="image/x-icon" />
<?php echo $metadata; ?>
<?php echo $css; ?>
<?php echo $javascripts; ?>
</head>
<body>
<div id="wrapper-all" class="container_12">
    <!--header-->
    <div class="grid_12" id="header">
    <?php echo $app_logo; ?>
    <div id="app-title"><?php echo $app_title; ?>
	<div id="app-subtitle"><?php echo $app_subtitle; ?></div>
    </div>
    </div>
    <div class="clear">&nbsp;</div>
    <!--header end-->

    <!--application main menu-->
    <div class="grid_12 tabs" id="main-menu">
    <?php echo $primary_links; ?>
    </div>
    <div class="clear">&nbsp;</div>
    <div class="spacer">&nbsp;</div>
    <!--application main menu end-->

    <!--application navigation menu/side menu-->
    <div class="grid_2" id="side-menu">
        <!-- language selection -->
        <?php echo $this->getBlock('language select'); ?>
        <!-- language selection end -->

        <!-- simple search -->
        <?php echo Biblio::getBlock($this, 'simple search'); ?>
        <!-- simple search end -->

        <!-- advanced search -->
        <?php echo Biblio::getBlock($this, 'advanced search'); ?>
        <!-- advanced search end -->

        <!-- license -->
	<?php echo Utility::createBlock('This Software is Released Under
	    <a href="http://www.gnu.org/copyleft/gpl.html" title="GNU GPL License" target="_blank">GNU GPL License</a> Version 3.', 'License'); ?>
        <!-- license end -->

        <!-- Awards -->
	<?php echo Utility::createBlock('The Winner in the Category of OSS</br><img src="files/media/logo-inaicta.png" />', 'Awards'); ?>
        <!-- Awards end -->
    </div>
    <!--application navigation menu/side menu-->

    <!--application main content -->
    <div class="grid_9" id="main-content">
    <div id="info"><?php echo $main_info; ?></div>
    <?php echo $main_content; ?>
    </div>
    <!--application main content end -->

    <!--footer-->
    <div class="grid_12" id="footer">
    <?php echo $this->getBlock('footer'); ?>
    </div>
    <!--footer end-->

    <div class="clear">&nbsp;</div>
    <div class="spacer">&nbsp;</div>
</div>
<?php echo $closure; ?>
</body>
</html>
