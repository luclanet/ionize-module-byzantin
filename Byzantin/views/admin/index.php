<div id="maincolumn">

    <h2 class="main byzantin"><?php echo lang('module_demo_title'); ?></h2>

    <div class="subtitle">

        <!-- About this module -->
        <p class="lite">
			<?php echo lang('module_demo_about'); ?>
        </p>
    </div>

	<?php
	/*
	<?php if ($post_max_vars) :?>

		<p class="notice"><?php echo lang('module_byzantin_post_max_vars', $post_max_vars); ?></p>

	<?php endif;?>
	*/
	?>

	<!-- Steps -->
	<?php echo $this->load->view('admin/steps'); ?>


    <div id="byzantinContainer" class="clear mt20"></div>


</div>

<script type="text/javascript">

    // Init the panel toolbox is mandatory
    ION.initToolbox('empty_toolbox');

    // Get the step content
    ION.HTML(
            'module/byzantin/byzantin/get_step',	// URL to the controller
            {}, 									// Data send by POST. Nothing
            {'update':'byzantinContainer'}			// JS request options
    );

</script>