var BYZANTIN_MODULE = (BYZANTIN_MODULE || {});

BYZANTIN_MODULE.append = function(hash){
	Object.append(BYZANTIN_MODULE, hash);
}.bind(BYZANTIN_MODULE);

BYZANTIN_MODULE.append(
{
	baseUrl: base_url,
	adminUrl: admin_url,
	moduleUrl: admin_url + 'module/byzantin/',

	menu: 'steps',

	buildMenu:function()
	{
		var self = this;
		$$('#' + this.menu + ' li a').each(function(item)
		{
			var url = self.moduleUrl + 'byzantin/' + item.getProperty('href');

			item.addEvent('click', function(e)
			{
				e.stop();
				ION.HTML(
					url,
					{},
					{'update':'byzantinContainer'}
				);
			});

		});
	},

	setActiveMenu:function(key)
	{

		$$('#' + this.menu + ' li a').each(function(item){item.removeClass('active')});

		$(this.menu).getElement('a[data-code='+key+']').addClass('active');
	},

	initUploadForm:function(destLang)
	{
		var self = this;
		var input = $('byzantinUploadInput');
		var submit = $('byzantinUploadSubmit');
		var list = $('byzantinUploadList');
		var drop = $('byzantinUploadDrop');

		// Form.MultipleFileInput instance
		var inputFiles = new Form.MultipleFileInput(input, list, drop,
			{
				itemClass: 'upload-item',
				onDragenter: drop.addClass.pass('hover', drop),
				onDragleave: drop.removeClass.pass('hover', drop),
				onDrop: drop.removeClass.pass('hover', drop)
			});

		// Request instance;
		var request = new Request.File({
			url: self.moduleUrl + 'byzantin/upload',
			onProgress: function(item){},
			onSuccess: function()
			{
				ION.notification('success', Lang.get('module_byzantin_upload_success_message'));
				ION.HTML(
					self.moduleUrl + 'byzantin/get_file',
					{},
					{'update':'byzantinContainer'}
				);
			}
		});

		$('byzantinUploadForm').addEvent('submit', function(event)
		{
			event.preventDefault();
			submit.hide();
			request.append('dest_lang', destLang);
			inputFiles.getFiles().each(function(file){
				request.append('files[]' , file);
			});
			request.send();
		});
	}

});