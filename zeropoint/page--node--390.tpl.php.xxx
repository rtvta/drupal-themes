<div id="top_bg">
<div class="sizer0 clearfix"<?php print wrapper_width() ?>>
<div id="top_left">
<div id="top_right">
<div id="headimg">

<div id="header" role="banner">
<div class="clearfix">
<?php if (theme_get_setting('loginlinks') || $page['topreg']): ?>
  <div id="top-elements">
    <?php if (theme_get_setting('loginlinks')): ?><div id="user_links"><?php print login_links() ?></div><?php endif; ?>
    <?php if ($page['topreg']): ?><div id="topreg"><?php print render ($page['topreg']); ?></div><?php endif; ?>
  </div>
<?php endif; ?>
  <?php if ($logo): ?><a href="<?php print check_url($front_page); ?>" title="<?php print t('Home'); ?>"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" class="logoimg" /></a><?php endif; ?>
  <div id="name-and-slogan">
  <?php if ($site_name): ?>
    <?php if ($title && theme_get_setting('page_h1') == '0'): ?>
      <p id="site-name"><a href="<?php print check_url($front_page); ?>" title="<?php print t('Home'); ?>"><?php print $site_name; ?></a></p>
    <?php else: ?>
      <h1 id="site-name"><a href="<?php print check_url($front_page); ?>" title="<?php print t('Home'); ?>"><?php print $site_name; ?></a></h1>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($site_slogan): ?><div id="site-slogan"><?php print $site_slogan; ?></div><?php endif; ?>
  </div>
</div>


<?php if ($page['header']): ?><?php print render ($page['header']); ?><?php endif; ?>

<!-- BB Dev -->
 <?php if ($messages): ?>
    <div id="messages"><div class="section clearfix">
      <?php print $messages; ?>
    </div></div> <!-- /.section, /#messages -->
  <?php endif; ?>

  <div class="menuband clearfix">
  <div id="menu" class="menu-wrapper">
  <?php if ($logo || $site_name): ?>
    <a href="<?php print check_url($front_page); ?>" class="pure-menu-heading" title="<?php if ($site_slogan) print $site_slogan; ?>">
      <?php if ($logo): ?><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" class="logomob" /><?php endif; ?>
      <?php if ($site_name) print $site_name; ?>
    </a>
  <?php endif; ?>
  <?php if ($main_menu): ?>
    <a href="#" id="toggles" class="menu-toggle"><s class="bars"></s><s class="bars"></s><div class="element-invisible">toggle</div></a>
    <div class="pure-menu pure-menu-horizontal menu-transform" role="navigation" aria-label="Menu">
      <div class="element-invisible"><?php print t('Main menu'); ?></div>
      <?php print theme('links__system_main_menu', array('links' => menu_tree(variable_get('menu_main_links_source', 'main-menu')))); ?>
    </div>
  <?php endif; ?>
  </div>
</div>
</div>

</div></div></div></div></div>

<div id="bottom_bg">
<div class="sizer0 clearfix"<?php print wrapper_width() ?>>
<div id="bottom_left">
<div id="bottom_right">

<div id="footer" class="pure-g" role="contentinfo">
<div class="<?php print resp_class(); ?>1-5"><?php if (theme_get_setting('social_links_display')): ?><div id="soclinks"><?php print social_links(); ?></div><?php endif; ?></div>
<div class="<?php print resp_class(); ?>3-5"><?php if ($page['footer']): ?><?php print render ($page['footer']); ?><?php endif; ?></div>
<div class="<?php print resp_class(); ?>1-5"></div>
</div>
<div id="brand"></div>

</div></div></div></div>
