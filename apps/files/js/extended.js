$(document).ready(function(){
	$('#fileTypeRestriction').change(function() {
		OC.AppConfig.setValue('core', $(this).attr('name'), this.checked ? 'yes' : 'no');
		$("#restriction").toggleClass('hidden', !this.checked);
	});
	$('.uploadGroups').each(function (index, element) {
		OC.Settings.setupGroupsSelect($(element));
		$(element).change(function(ev) {
			var groups = ev.val || [];
			groups = (groups.length > 0) ? ev.val.join(',') : '';
			OC.AppConfig.setValue('core', $(this).attr('name'), groups);
		});
	});
	$('#filetypes').change(function(){
		OC.AppConfig.setValue('core', $(this).attr('name'), $(this).attr('value'));
	});
});
