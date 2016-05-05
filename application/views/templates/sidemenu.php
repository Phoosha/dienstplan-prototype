<!-- Menu toggle -->
<a href="<?php echo current_url() ?>#menu" id="menuLink" class="menu-link">
	<!-- Hamburger icon -->
	<span></span>
</a>

<!-- Menu heading + content -->
<div id="menu">
	<div class="pure-menu">
		<a class="pure-menu-heading" href="https://github.com/Phoosha/dienstplan">
			<div id="logo"></div>
		</a>

<?php // Only display menu content if explicitly requested
	if ($menu) { ?>

		<ul class="pure-menu-list">
			<li class="pure-menu-item <?php if ($menu_id == 'welcome') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('/')?>" class="pure-menu-link"><i class="fa fa-home fa-fw" aria-hidden="true"></i> Start</a>
			</li>
			<li class="pure-menu-item <?php if ($menu_id == 'plan') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('plan/show')?>" class="pure-menu-link"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i> Dienstplan</a>
			</li>
			<li class="pure-menu-item <?php if ($menu_id == 'phone') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('user/phonelist')?>" class="pure-menu-link"><i class="fa fa-phone fa-fw" aria-hidden="true"></i> Telefonliste</a>
			</li>
			<li class="pure-menu-item <?php if ($menu_id == 'settings') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('user/settings')?>" class="pure-menu-link"><i class="fa fa-user fa-fw" aria-hidden="true"></i> Mein Konto</a>
			</li>
<?php if ($this->ion_auth->is_admin()): ?>
			<li class="pure-menu-item <?php if ($menu_id == 'admin') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('admin')?>" class="pure-menu-link"><i class="fa fa-users fa-fw" aria-hidden="true"></i> Verwaltung</a>
			</li>
<?php endif ?>
		</ul>
	</div>
	<a href="<?php echo site_url('auth/logout')?>" class="primary-button pure-button danger-button"><i class="fa fa-sign-out" aria-hidden="true"></i> Abmelden</a>
	
<?php } else { ?>
	
		<!-- Menu hidden -->
	</div>
	
<?php } ?>

</div>

