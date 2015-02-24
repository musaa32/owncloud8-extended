<?php if($_['uploadChangable']):?>

	<?php OCP\Util::addscript('files', 'admin'); ?>

	<form name="filesForm" class="section" action="#" method="post">
		<h2><?php p($l->t('File handling')); ?></h2>
		<label for="maxUploadSize"><?php p($l->t( 'Maximum upload size' )); ?> </label>
		<input type="text" name='maxUploadSize' id="maxUploadSize" value='<?php p($_['uploadMaxFilesize']) ?>'/>
		<?php if($_['displayMaxPossibleUploadSize']):?>
			(<?php p($l->t('max. possible: ')); p($_['maxPossibleUploadSize']) ?>)
		<?php endif;?>
		<br/>
		<input type="hidden" value="<?php p($_['requesttoken']); ?>" name="requesttoken" />
		<input type="submit" name="submitFilesAdminSettings" id="submitFilesAdminSettings"
			   value="<?php p($l->t( 'Save' )); ?>"/>
	</form>

<?php endif;?>
	<!-- Extension -->
	<?php OCP\Util::addscript('files', 'extended'); ?>
	<form name="extendedSettings" class="section" action="#" method="post">
		<h2><?php p($l->t('File handling')); ?></h2>
		<label for="delete">Users in this group <b>can delete</b>  files.</label><br />
		<input name="delete" type="hidden" class="uploadGroups" value="<?php p($_['deleteGroupsList']) ?>" style="width: 400px"/><br />
		<em>These groups will be able to delete files.</em>
	</form>
