<form action="<?php echo $link.'updateAll/';?>" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">Settings</div>
		<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="center">s.no</th>
						<th>name</th>
						<th width="70%">description</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?
					if ($rows && count($rows) > 0) {
						$c = 0;
						foreach ($rows as $row) {
							$c++;
							?>
							<tr class="tr-head">
								<td class="center"><?php echo $c;?></td>
								<td><?=$row['name'] ?></td>
								<td>
									<textarea cols="50" rows="2"
									name="setting[<?php echo $row['id']?>]"
									class="form-control" 
									placeholder="Description"><?=$row['value'] ?></textarea> 
								</td>
								<td>
									<?php //echo anchor($link."delete/".$row['slug'],"Delete",'class="a-delete"');?>
								</td>
							</tr>
							<?
						}
					}?>
				</tbody>
			</table>
		</div>
		<div class="panel-footer">
			<div class="table-footer">
			<!-- <a href="<?= $link ?>add" class="btn btn-primary"/>Add New  </a> -->
			<input type="submit" value="Update" class="btn btn-primary">
			<ul class="pagination">
				<? if (!empty($pages)) echo $pages; ?>
			</ul>
		</div>
	</div>
</div>
</form>

