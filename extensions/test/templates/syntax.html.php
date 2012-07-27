<h3>Syntax</h3>

<?php foreach($data as $class => $violations): ?>
<table class="metrics">
	<thead>
		<tr>
			<th colspan="3"><?php echo $class; ?></th>
		</tr>
		<?php if($violations): ?>
		<tr>
			<th>Line</th>
			<th>Position</th>
			<th>Violation</th>
		</tr>
		<?php endif; ?>
	</thead>
	<tbody>
		<?php if($violations): ?>
			<?php foreach($violations as $violation): ?>
			<tr>
				<td><?php echo $violation['line']; ?></td>
				<td><?php echo $violation['position']; ?></td>
				<td><?php echo $violation['message']; ?></td>
			</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr><td colspan="3">No violations found!</td></tr>
		<?php endif; ?>
	</tbody>
</table>
<?php endforeach; ?>