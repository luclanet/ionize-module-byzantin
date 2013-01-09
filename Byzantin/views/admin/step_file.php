<?php
	$source_lang_name = isset($iso639[$source_lang]) ? $iso639[$source_lang] : $source_lang;
	$dest_lang_name = isset($iso639[$dest_lang]) ? $iso639[$dest_lang] : $dest_lang;
?>
<div>
    <p>
        <span class="w90 left">
			<?php echo lang('module_byzantin_source') ?> :
		</span>
		<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$source_lang.'.gif')) :?>
        	<img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $source_lang; ?>.gif" />
		<?php endif ;?>
        <strong><?php echo $source_lang_name; ?></strong>
    </p>
    <p>
		<span class="w90 left">
			<?php echo lang('module_byzantin_destination') ?> :
		</span>
		<?php if (file_exists(Theme::get_theme_path().'images/world_flags/flag_'.$dest_lang.'.gif')) :?>
    		<img src="<?php echo theme_url(); ?>images/world_flags/flag_<?php echo $dest_lang; ?>.gif" />
		<?php endif ;?>
        <strong><?php echo $dest_lang_name; ?></strong>
    </p>
</div>

<h3><?php echo lang('module_byzantin_upload_files'); ?></h3>
<p>
	<?php echo lang('module_byzantin_upload_files_help'); ?>
</p>

<form id="byzantinUploadForm" method="post" enctype="multipart/form-data">
    <div id="byzantinUploadDrop" class="droppable w200 h40 mb10">
		<?php echo lang('module_byzantin_upload_drop_files_here'); ?>
    </div>
    <ul id="byzantinUploadList" class="list mb10"></ul>
    <fieldset>
        <input type="hidden" name="dest_lang" value="<?php echo $dest_lang; ?>" />
        <input type="file" id="byzantinUploadInput" name="files[]" multiple="multiple" style="display:none;" />
        <input type="submit" id="byzantinUploadSubmit" class="input submit" name="upload" value="Upload" />
    </fieldset>
</form>

<h3><?php echo lang('module_byzantin_select_file_to_translate'); ?></h3>

<div id="byzantinFile">

	<table class="list">
		<thead>
			<tr>
				<th><?php echo lang('module_byzantin_file'); ?></th>
				<th><?php echo lang('module_byzantin_translated'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $source_files as $name => $source_file ) :?>
				<?php
					$class= '';
					$no_dest_file = FALSE;
					$percent_translated = 0;
					$percent_class = 'error';

					if ( ! in_array( $name, array_keys($dest_files)))
					{
						$class = 'class="nofiles"';
						$no_dest_file = TRUE;
					}
					else
					{
						$dest_file = $dest_files[$name];
						$percent_translated = intval($dest_file['nb_items'] / $source_file['nb_items'] * 100);
						if ($percent_translated > 100) $percent_translated = 100;
						if ($percent_translated > 0) $percent_class = 'notice';
						if ($percent_translated == 100) $percent_class = 'success';

					}
				?>
				<tr>
					<td>
                        <a class="button light lang_file" data-file="<?php echo $name; ?>">
                            <i class="icon-article"></i>
							<?php echo $name ?>
							<?php if( $no_dest_file) :?>
                            ( <?php echo lang('module_byzantin_file_not_found_in'); ?> <?php echo $dest_lang_name; ?>)
							<?php endif;?>
                        </a>
					</td>
					<td>
						<span class="<?php echo $percent_class ;?>">
							<?php echo $percent_translated; ?> %
						</span>
					</td>
                </tr>
			<?php endforeach ;?>
        </tbody>
    </table>

</div>

<script type="text/javascript">

	// Upload
	BYZANTIN_MODULE.initUploadForm('<?php echo $dest_lang; ?>');

    // Add link to each language link
    $$('#byzantinFile .lang_file').each(function(item)
    {
        item.addEvent('click', function(e)
        {
            e.stop();
            ION.HTML(
				'module/byzantin/byzantin/get_translate',
				{'file':item.getProperty('data-file')},
				{'update':'byzantinContainer'}
            );
        });
    });

    // Set the current menu item
    BYZANTIN_MODULE.setActiveMenu('file');

</script>
