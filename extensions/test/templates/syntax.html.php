<h3>Syntax</h3>

<table class="metrics">
<?php foreach($data as $class => $messages): ?>
	<thead>
		<tr>
			<th colspan="4"><?php echo $class; ?></th>
		</tr>
		<?php if($messages['violations'] || $messages['warnings']): ?>
		<tr>
			<th>&nbsp;</th>
			<th>Line</th>
			<th>Position</th>
			<th>Message</th>
		</tr>
		<?php endif; ?>
	</thead>
	<tbody>
		<?php if($messages['violations'] || $messages['warnings']): ?>
			<?php foreach($messages['violations'] as $violation): ?>
			<tr>
				<td><div class="test-result-failure">&nbsp;</div></td>
				<td><?php echo $violation['line']; ?></td>
				<td><?php echo $violation['position']; ?></td>
				<td><?php echo $violation['message']; ?></td>
			</tr>
			<?php endforeach; ?>
			<?php foreach($messages['warnings'] as $warning): ?>
			<tr>
				<td><div class="test-result-exception">&nbsp;</div></td>
				<td><?php echo $warning['line']; ?></td>
				<td><?php echo $warning['position']; ?></td>
				<td><?php echo $warning['message']; ?></td>
			</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr><td colspan="3">No violations found!</td></tr>
		<?php endif; ?>
	</tbody>
<?php endforeach; ?>
</table>