
<ol id="steps">

	<?php foreach($menu as $item): ?>

		<li>
			<a data-code="<?php echo $item['code'] ;?>" href="get_<?php echo$item['code'] ;?>" class="<?php echo $item['active'] ;?>">
				<span><?php echo $item['title'] ;?></span>
			</a>
		</li>

	<?php endforeach ;?>

</ol>
<script type="text/javascript">

    BYZANTIN_MODULE.buildMenu();

</script>

