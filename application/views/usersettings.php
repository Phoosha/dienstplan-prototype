<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1><?php echo $title ?></h1>
<?php if ($user['remote_code'] !== null): ?>
<p><?php echo site_url('remote/user/'.$user['remote_code']); ?></p>
<?php endif ?>
