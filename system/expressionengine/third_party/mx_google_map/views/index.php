<?php if($message) : ?>
<div class="mor alert success">
<p><?php print  lang($message); ?></p>
</div>
<?php endif; ?>

<?php if($errors) : ?>
<div class="mor alert">
<?php
	foreach ($errors as  $error)
	{ 
		print  '<p>'.lang($error).'</p>';
	}
?>
</div>
<?php endif; ?>

<?= form_open(
'&C=addons_modules&M=show_module_cp&module=mx_google_map',
'',
array("file" => "mx_google_map")
)
?>

<table class="mainTable" border="0" cellpadding="0" cellspacing="0">
<thead>
<tr>
<tr class="header"><th colspan="6"><?= lang('custom_fields')?></th></tr>
</thead>
<tbody id="custom_fields">
<tr class="header" ><th>#</th><th><?= lang('name')?></th><th><?= lang('label')?></th><th><?= lang('field_type')?></th><th><?= lang('pattern')?></th><th><?= lang('delete')?></th></tr>
		 <?php
		$out  = "";
		$row_ids = 1;
		$last_id = 1;
		if (isset($settings['custom_fields'])) {
			foreach ($settings['custom_fields'] as  $field)
			{
	
				echo '
				<tr id="field_'. $field['field_id'].'" class="'.(($row_ids&1) ? 'odd' : 'even').'">
				<td>'.$field['field_id'].'</td>
				<td><input name="row_order[]"  value="'.$field['field_id'].'" type="hidden"/><input name="old_field_name_'.$field['field_id'].'"  value="'.$field['field_name'].'" type="hidden"/><input dir="ltr" style="width: 100%;" name="field_name_'.$field['field_id'].'" id="" value="'.$field['field_name'].'" size="20" maxlength="256" class="input" type="text"></td>
				<td><input dir="ltr" style="width: 100%;" name="field_label_'.$field['field_id'].'" id="" value="'.$field['field_label'].'" size="20" maxlength="256" class="input" type="text"></td>
				<td><select name="field_type" id="field_type"><option value="text">Text Input</option> </select></td>
				<td><input dir="ltr" style="width: 100%;" name="field_pattern_'.$field['field_id'].'" id="" value="'.$field['field_pattern'].'" size="20" maxlength="500" class="input" type="text"></td>
				
				<td style="width:40px;""><input type="checkbox" name="delete_'.$field['field_id'].'" value=""></td>
				</tr>';
				$row_ids	+=	1;
				$last_id	=	$field['field_id'];
			}
 }
 
?>

</tbody>		
</table>
<input name="edit_field_group_name" value="<?= lang('add_custom_field'); ?>" class="submit" type="button" id="add_rule"></p>

<script type="text/javascript">
	jQuery(function() {
	index_row	=	<?= $row_ids ?>;
	last_id	=	<?= $last_id ?>;
	template = '<tr  id="field_{row}"><td>{row}</td><td><input name="row_order[]" value="{row}" type="hidden"><input name="new_field_{row}" value="{row}" type="hidden"><input dir="ltr" style="width: 100%;" name="field_name_{row}" id="" value="" size="20" maxlength="256" class="input" type="text"></td><td><input dir="ltr" style="width: 100%;" name="field_label_{row}" id="" value="" size="20" maxlength="256" class="input" type="text"></td><td><select name="field_type" id="field_type"><option value="text">Text Input</option> </select></td><td><input dir="ltr" style="width: 100%;" name="field_pattern_{row}" id="" value="" size="20" maxlength="500" class="input" type="text"></td><td style="width: 40px;"><span onclick="delete_line({row});"><img src="<?php echo($img_path);?>third_party/mx_google_map/images/round_minus.png" alt="<?=lang('remove_line')?>"  title="<?=lang('remove_line')?>" /></span></td></tr>';	
 
 		if (index_row == 1) {
			jQuery("#custom_fields").append(template.replace(/{row}/g, last_id));
			index_row 		+=	1;
			last_id +=	1;
		}
		
		jQuery("#add_rule").click(function () {	
			jQuery("#custom_fields").append(template.replace(/{row}/g, index_row));
			index_row	+=	1;
		});
		

		

 });
 		function delete_line (row_id){

			jQuery("#field_" +row_id).remove();

				
		}
</script> 
<p class="centerSubmit"><input name="edit_field_group_name" value="<?= lang('update'); ?>" class="submit" type="submit"></p>

<?= form_close(); ?>


