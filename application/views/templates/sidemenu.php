<!-- Menu toggle -->
<a href="#menu" id="menuLink" class="menu-link">
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
				<a href="<?php echo site_url('/')?>" class="pure-menu-link">Home</a>
			</li>
			<li class="pure-menu-item <?php if ($menu_id == 'plan') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('plan/show')?>" class="pure-menu-link">Dienstplan</a>
			</li>
			<li class="pure-menu-item <?php if ($menu_id == 'phone') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('user/phonelist')?>" class="pure-menu-link">Telefonliste</a>
			</li>
			<li class="pure-menu-item <?php if ($menu_id == 'settings') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('user/settings')?>" class="pure-menu-link">Einstellungen</a>
			</li>
<?php if ($this->ion_auth->is_admin()): ?>
			<li class="pure-menu-item <?php if ($menu_id == 'admin') echo 'pure-menu-selected' ?>">
				<a href="<?php echo site_url('admin')?>" class="pure-menu-link">Administration</a>
			</li>
<?php endif ?>
		</ul>
	</div>
	<a href="<?php echo site_url('auth/logout')?>"><button class="primary-button pure-button">Abmelden</button></a>
	
<?php } else { ?>
	
		<!-- Menu hidden -->
	</div>
	
<?php } ?>

</div>

