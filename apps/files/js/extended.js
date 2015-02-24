$(document).ready(function(){
  $('.uploadGroups').each(function (index, element) {
    OC.Settings.setupGroupsSelect($(element));
    $(element).change(function(ev) {
      var groups = ev.val || [];
			groups = (groups.length > 0) ? ev.val.join(',') : '';
			OC.AppConfig.setValue('core', $(this).attr('name'), groups);
		});
	});
});
