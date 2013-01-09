<?php

	$nb_posts = count($translations) * 2 + 20;

?>

<div>
    <h3>
		<?php echo lang('module_byzantin_file') ?> : <strong><?php echo $file; ?></strong>
    </h3>
</div>


<?php if( $post_max_vars && $nb_posts > $post_max_vars) :?>

	<p class="error"><?php echo lang('module_byzantin_post_max_vars', array($nb_posts, $post_max_vars)); ?></p>

<?php else: ?>

	<div id="byzantinTranslate">

		<?php echo form_open('translator', array('id'=>'formByzantinTranslator'), $hidden ); ?>

			<div class="h30">
				<div class="left">
					<a id="byzantinFilterAll" class="button light"><?php echo lang('module_byzantin_filter_all'); ?></a>
					<a id="byzantinFilterEmpty" class="button light"><?php echo lang('module_byzantin_filter_empty'); ?></a>
					<a id="byzantinFilterSame" class="button light"><?php echo lang('module_byzantin_filter_same'); ?></a>
				</div>
				<?php
					echo form_submit(
						array(
							'name' => 'savelang',
							'value' => lang('module_byzantin_save_translation'),
							'class' => 'input submit right saveByzantinTranslation'
						)
					);
				?>
			</div>

			<table id="byzantinTable" class="byzantin">
				<thead>
					<tr>
						<th></th>
						<th><?php echo lang('module_byzantin_key'); ?></th>
						<th>
							<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$source_lang.'.gif')) :?>
						        <img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $source_lang; ?>.gif" />&nbsp;
							<?php endif;?>
							<?php echo($iso639[$source_lang]); ?>
						</th>
						<th>
							<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$source_lang.'.gif')) :?>
						        <img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $dest_lang; ?>.gif" />&nbsp;
							<?php endif;?>
							<?php echo($iso639[$dest_lang]); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$i = 1;
					?>
					<?php foreach($translations as $key => $line) :?>
						<?php
							$class = array();
							if(empty($line['destination'])) $class[] = 'empty';
							if($line['same']) $class[] = 'same';
							$class = implode(' ', $class);
						?>
						<tr class="<?php echo $class; ?>">
							<td><?php echo $i++ ;?></td>
							<td><strong><?php echo $key ; ?></strong></td>
							<td><?php echo htmlspecialchars($line['source']); ?></td>
							<td style="width:50%;">
								<?php
								/*
								 * Form input
								 */
								$md5 = md5($key);
								?>
								<?php echo form_hidden($key_uniq_prefix . $input_uniq_prefix . $md5, $key ); ?>

								<?php if ( mb_strlen( $line['source'] ) > $textarea_line_break ): ?>

									<?php
									echo form_textarea(
										array(
											'name' => $input_uniq_prefix . $md5,
											'value' => $line['destination'],
											'rows' => $textarea_rows,
											'class' => 'textarea'
										)
									);
									?>

								<?php else:?>

									<?php
										echo form_input(
											array(
												'name' => $input_uniq_prefix . $md5,
												'value' => $line['destination'],
												'class' => 'inputtext',
											)
										);
									?>

								<?php endif;?>

								<?php
								/*
								 * Notice / Warning
								 */
								?>

								<?php if ( ! is_null( $line['error'])) :?>
									<br /><span class="error"><?php echo $line['error'] ;?></span>
								<?php endif;?>

								<?php if ( ! is_null( $line['note'])) :?>
									<br /><span class="note"><?php echo $line['note'] ;?></span>
								<?php endif;?>

								<?php if ( $line['same']) :?>
									<br /><span class="same"><?php echo lang('module_byzantin_translation_same') ;?></span>
								<?php endif;?>

							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>

			</table>

			<div class="h30 mt10">
			<?php
			echo form_submit(
				array(
					'name' => 'savelang',
					'value' => lang('module_byzantin_save_translation'),
					'class' => 'input submit right saveByzantinTranslation'
				)
			);
			?>
			</div>

		<?php echo form_close(); ?>
	</div>
<?php endif ;?>

<script type="text/javascript">

    // Set the current menu item
    BYZANTIN_MODULE.setActiveMenu('translate');

    $$('.saveByzantinTranslation').each(function(item)
	{
		item.addEvent('click', function(e)
        {
			e.stop();
			ION.sendData(
            	admin_url + 'module/byzantin/byzantin/save',
				$('formByzantinTranslator')
			);
        });
    });

	$('byzantinFilterEmpty').addEvent('click', function()
	{
		$$('#byzantinTable tr').hide();
		$$('#byzantinTable tr.empty').removeProperty('style');
	});

	$('byzantinFilterSame').addEvent('click', function()
	{
		$$('#byzantinTable tr').hide();
		$$('#byzantinTable tr.same').removeProperty('style');
	});

	$('byzantinFilterAll').addEvent('click', function()
	{
		$$('#byzantinTable tr').removeProperty('style');
	});

</script>
