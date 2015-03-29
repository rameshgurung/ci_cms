<form action="<?php echo current_url();?>" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">Set Permissions for Group :: <?php echo ucfirst($group['name'])?></div>
		<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<tr>
						<th colspan="2">name</th>
						<th></th>
						<th>description</th>
					</tr>
				</thead>
				<tbody>
					<?
					if ($rows && count($rows) > 0) {
						foreach ($rows as $row) {
							?>
							<tr class="tr-head">
								<td>
									<input name="permission[<?php echo $row['id']?>][name]" 
									type="checkbox"
									id="<?=$row['id']?>"
									value="<?echo set_value('',$row['name']); ?>"
									<?php echo in_array($row['id'], $group_permsissions)?'checked':'';?>>									
								</td>
								<td class="col-lg-3" colspan="2"><?=$row['name'] ?></td>
								<td class="col-lg-9"><?=$row['desc'] ?></td>
								<?php 
								$child_permissions=$permission_m->get_child_permissions($row['id']);
								?>
							</tr>
							<?php
							foreach ($child_permissions as $permission) {
								?>
								<tr class="tr-hide">
									<td>&nbsp</td>
									<td class="center">
										<input name="permission[<?php echo $permission['id']?>][name]" 
										type="checkbox" 
										class="<?=$row['id']?>"
										value="<?echo set_value('',$permission['name']); ?>"
										<?php echo in_array($permission['id'], $group_permsissions)?'checked':'';?>>									
									</td>
									<td><?=$permission['name'] ?></td>
									<td><?=$permission['desc'] ?></td>
								</tr>
								<?
						}//end of child permissons
						?>
						<?
					}
				}?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer">
		<div class="table-footer">
			<span class="btn btn-primary btn-select-all">Select All</span>
			<input type="submit" value="Update" class="btn btn-primary">
			<a href="<?= $link?>" class="btn btn-primary"/>Cancel  </a>
			<ul class="pagination">
				<? if (!empty($pages)) echo $pages; ?>
			</ul>
		</div>
	</div>
</div>
</form>

<script>
	$(function(){
		$('span.btn-select-all').on('click',function(){
			if($(this).text()=="Select All"){
				$(this).text('Unselect All');
				$('input:checkbox').prop('checked',true);
			}
			else if($(this).text()=="Unselect All"){
				$(this).text('Select All');
				$('input:checkbox').prop('checked',false);
			}
		});

		$('input:checkbox').on('click',function(){
			var parent_id=parseInt($(this).attr("id"));
			if(parent_id){
				//parent case
				$("."+parent_id).prop('checked',this.checked);
			}
			else{
				//child case
				var parent_id=parseInt($(this).attr("class"));
				var child_checked=false;
				$("."+parent_id).each(function(){
					if($(this).prop('checked')==true){
						$('#'+parent_id).prop('checked',true);
						child_checked=true;
					}				
				});
				if(!child_checked){
					$("#"+parent_id).prop('checked',false);
				}
			}
		});

	})
</script>