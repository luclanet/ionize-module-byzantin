
<div id="byzantinSource">
	<?php foreach ( $languages as $language ) :?>

		<?php if ($language['existing_files'] != 0) :?>

			<?php
				$label = $iso639[$language['code']] . ' : '. $language['existing_files'].' translation files';
			?>
			<div>
				<a class="button light lang_source" data-lang-code="<?php echo $language['code']; ?>">
					<i class="mr10"><img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $language['code']; ?>.gif" /></i>
					<?php echo $label ?>
				</a>
			</div>

		<?php endif; ?>

	<?php endforeach ;?>
</div>

<script type="text/javascript">

	// Add link to each language link
	$$('#byzantinSource .lang_source').each(function(item)
	{
		item.addEvent('click', function(e)
		{
			e.stop();
            ION.HTML(
                    'module/byzantin/byzantin/get_destination',
                    {'source_lang':item.getProperty('data-lang-code')},
                    {'update':'byzantinContainer'}
            );
        });
	});

    // Set the current menu item
    BYZANTIN_MODULE.setActiveMenu('source');

</script>