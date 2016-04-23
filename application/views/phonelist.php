<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h2 class="content-subhead">Wichtige Telefonnummern<?php if ($this->ion_auth->is_admin()): ?>&nbsp;<a href="#"><i title="Hinzufügen" class="fa fa-plus linked-icon fa-lg" aria-hidden="true"></i></a><?php endif ?>
</h2>
<table class="pure-table phonelist tight-table">
	<thead>
		<tr>
			<th>Name</th>
			<th>Telefon</th>
<?php if ($this->ion_auth->is_admin()): ?>
			<th></th>
<?php endif ?>
		</tr>
	</thead>
	<tbody>
		
<?php $odd = false;
foreach ($numbers as $item):
	$odd = ! $odd ?>
		
		<tr <?php if ($odd) echo 'class="pure-table-odd"'; ?>>
			<td><?php echo $item['name']; ?></td>
			<td><a href="tel:<?php echo $item['phone']; ?>"><?php echo $item['phone']; ?></a></td>
<?php if ($this->ion_auth->is_admin()): ?>
			<td>
				<a href="#"><i title="Bearbeiten" class="fa fa-pencil-square-o linked-icon fa-lg" aria-hidden="true"></i></a>&nbsp;<a href="#"><i title="Löschen" class="fa fa-trash-o linked-icon fa-lg" aria-hidden="true"></i></a>
			</td>
<?php endif ?>
		</tr>
		
<?php endforeach ?>
		
	</tbody>
</table>

<h2 class="content-subhead">Alle Mitglieder</h2>
<table class="pure-table phonelist">
	<thead>
		<tr>
				<th>Name</th>
				<th>Vorname</th>
				<th>Telefon</th>
		</tr>
	</thead>
	<tbody>
		
<?php $odd = false;
foreach ($users as $item):
	$odd = ! $odd ?>
		
		<tr <?php if ($odd) echo 'class="pure-table-odd"'; ?>>
			<td><?php echo $item['last_name']; ?></td>
			<td><?php echo $item['first_name']; ?></td>
			<td><a href="tel:<?php echo $item['phone']; ?>"><?php echo $item['phone']; ?></a></td>
		</tr>
		
<?php endforeach ?>
		
	</tbody>
</table>
