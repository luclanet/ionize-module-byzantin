<?php
	$existing_lang_codes = array($source_lang);
?>
<div>
	<p>
		<span class="w90 left">
			<?php echo lang('module_byzantin_source') ?> :
        </span>
		<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$source_lang.'.gif')) :?>
	        <img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $source_lang; ?>.gif" />
		<?php endif ?>
		<strong><?php echo($iso639[$source_lang]); ?></strong>
	</p>
</div>

<div id="byzantinDestination">

	<h3><?php echo lang('module_byzantin_existing_destination_folders'); ?></h3>

	<?php foreach ( $languages as $language ) :?>

		<?php if ($language['code'] != $source_lang) :?>

			<?php
				$existing_lang_codes[] = $language['code'];
				$label = $language['code'] . ' / ' .$iso639[$language['code']] . ' : '. $language['existing_files'].' translation files';
			?>
			<div>
				<a class="button light lang_dest" data-lang-code="<?php echo $language['code']; ?>">
                    <i class="mr10">
						<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$language['code'].'.gif')) :?>
            				<img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $language['code']; ?>.gif" />
						<?php endif ;?>
                    </i>
					<?php echo $label ?>
				</a>
			</div>

		<?php endif; ?>

	<?php endforeach ;?>


    <h3><?php echo lang('module_byzantin_not_existing_destination_folders'); ?></h3>

	<?php foreach ( $iso639 as $code => $label ) :?>
		<?php if ( ! in_array($code, $existing_lang_codes)):?>
			<div>
				<a class="button light lang_dest" data-lang-code="<?php echo $code; ?>">
					<i class="mr10">
						<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$code.'.gif')) :?>
							<img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $code; ?>.gif" />
						<?php endif ;?>
                    </i>
					<?php echo $code ?> / <?php echo $label ?>
				</a>
			</div>
		<?php endif; ?>
	<?php endforeach ;?>


</div>

<script type="text/javascript">

	$$('#byzantinDestination .lang_dest').each(function(item)
	{
		item.addEvent('click', function(e)
		{
			e.stop();
            ION.HTML(
                    'module/byzantin/byzantin/get_file',
                    {
						'dest_lang':item.getProperty('data-lang-code')
					},
                    {'update':'byzantinContainer'}
            );
        });
	});

    // Set the current menu item
    BYZANTIN_MODULE.setActiveMenu('destination');

</script>