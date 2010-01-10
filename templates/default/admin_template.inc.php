<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language_code; ?>" lang="<?php echo $language_code; ?>" dir="<?php echo $text_direction; ?>"><head><title><?php echo $page_title; ?></title><meta http-equiv="Content-Type" content="text/html; charset=<?php echo $page_charset; ?>" />
<link rel="icon" href="<?php echo $webicon; ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo $webicon; ?>" type="image/x-icon" />
<?php echo $metadata; ?>
<?php echo $css; ?>
<?php echo $javascripts; ?>
</head>
<body>
<!--application main menu-->
<div id="main-menu">
<?php echo $primary_links; ?>
</div><div class="clear"></div>
<!--application main menu end-->

<!--header-->
<div id="header">
<?php echo $app_logo; ?>
    <div id="app-title"><?php echo $app_title; ?>
        <div id="app-subtitle"><?php echo $app_subtitle; ?></div>
    </div>
</div>
<!--header end-->

<!-- content -->
<table cellspacing="0" cellpadding="0" id="content-wrapper">
    <tr>
    <!--application navigation menu/side menu-->
    <td id="admin-side-menu">
        <!-- navigation -->
        <?php echo $navigation_links; ?>
        <!-- navigation end -->
    </td>
    <!--application navigation menu/side menu-->

    <!--application main content -->
    <td id="admin-main-content-wrapper">
    <div id="loading"></div>
    <?php echo $main_info; ?>
    <div id="admin-main-content"><?php echo $main_content; ?></div>
    </td>
    <!--application main content end -->
    </tr>
</table>
<!-- content end -->

<!--footer-->
<div id="footer">
<?php echo $this->getBlock('footer'); ?>
</div>
<!--footer end-->
<?php echo $closure; ?>
</body>
</html>
