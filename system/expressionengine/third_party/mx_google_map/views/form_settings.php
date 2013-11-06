<?php if($message) : ?>
<div class="mor alert success">
<p><?php print($message); ?></p>
</div>
<?php endif; ?>



<table class="mainTable padTable" id="event_table" border="0" cellpadding="0" cellspacing="0">
<tr class="header" >
<th colspan="3"><?= lang('markers_path')?></th>
</tr>
<tbody>
<tr style="width: 33%;">
<td><?=lang('url2folder')?></td>
<td><input dir="ltr" style="width: 100%;" name="url_markers_icons" id="url_markers_icons" value="<?=((isset($settings['url_markers_icons'])) ? $settings['url_markers_icons'] : '' );?>" size="20" maxlength="256" class="input" type="text"></td>
</tr>
<tr style="width: 33%;">
<td style="width: 33%;"><?=lang('server_path')?></td>
<td><input dir="ltr" style="width: 100%;" name="path_markers_icons" id="" value="<?=((isset($settings['path_markers_icons'])) ? $settings['path_markers_icons'] : '' );?>" size="20" maxlength="256" class="input" type="text"></td>
</tr>
<tr class="header" >
<th colspan="3"><?=lang('default_map')?></th>
</tr>

<tr>
<td><?=lang('latitude')?></td>
<td><input dir="ltr" style="width: 100%;" name="latitude" id="" value="<?=((isset($settings['latitude'])) ? $settings['latitude'] : '' );?>" size="20" maxlength="256" class="input" type="text"></td>
</tr>
<tr>
<td><?=lang('longitude')?></td>
<td><input dir="ltr" style="width: 100%;" name="longitude" id="" value="<?=((isset($settings['longitude'])) ? $settings['longitude'] : '' );?>" size="20" maxlength="256" class="input" type="text"></td>
</tr>
<tr>
<td><?=lang('longitude')?></td>
<td><?=form_dropdown('zoom', range(1, 20), $settings['zoom'])?></td>
</tr>
<tr>
<td><?=lang('map')?></td>
<td><div style="height: 300px;"><div id="map_canvas" style="width: 100%; height: 100%"></div></div></td>
</tr>

</tbody>
</table>
