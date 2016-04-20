<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h2 class="content-subhead">Wichtige Telefonnummern</h2>
<table class="pure-table phonelist">
	<thead>
		<tr>
				<th>Name</th>
				<th>Telefon</th>
		</tr>
	</thead>
	<tbody>
		
<?php $odd = false;
foreach ($numbers as $item):
	$odd = ! $odd ?>
		
		<tr <?php if ($odd) echo 'class="pure-table-odd"'; ?>>
			<td><?php echo $item['name']; ?></td>
			<td><a href="tel:<?php echo $item['phone']; ?>"><?php echo $item['phone']; ?></a></td>
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
