<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'com_users/two-factor-switcher.min.js', ['version' => 'auto', 'relative' => true], ['type' => 'module']);
HTMLHelper::_('script', 'com_users/two-factor-switcher-es5.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true, 'nomodule' => true]);

$input = Factory::getApplication()->input;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->useCoreUI = true;
?>
<form
	action="<?php echo Route::_('index.php?option=com_admin&view=profile&layout=edit&id=' . $this->item->id); ?>"
	method="post"
	name="adminForm"
	id="profile-form"
	enctype="multipart/form-data"
	class="form-validate"
>
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'user_details']); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	<?php if (!empty($this->twofactorform) && $this->item->id) : ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'twofactorauth', Text::_('COM_ADMIN_PROFILE_TWO_FACTOR_AUTH')); ?>
	<div class="control-group">
		<div class="control-label">
			<label id="jform_twofactor_method-lbl" for="jform_twofactor_method">
				<?php echo Text::_('COM_ADMIN_PROFILE_FIELD_TWOFACTOR_LABEL'); ?>
			</label>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('select.genericlist', UsersHelper::getTwoFactorMethods(), 'jform[twofactor][method]', ['onchange' => 'Joomla.twoFactorMethodChange();', 'class' => 'form-select'], 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
		</div>
	</div>
	<div id="com_users_twofactor_forms_container">
		<?php foreach ($this->twofactorform as $form) : ?>
			<?php $class = $form['method'] == $this->otpConfig->method ? '' : ' class="hidden"'; ?>
			<div id="com_users_twofactor_<?php echo $form['method'] ?>"<?php echo $class; ?>>
				<?php echo $form['form'] ?>
			</div>
		<?php endforeach; ?>
	</div>

	<fieldset>
		<legend>
			<?php echo Text::_('COM_ADMIN_PROFILE_OTEPS'); ?>
		</legend>
		<div class="alert alert-info">
			<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_ADMIN_PROFILE_OTEPS_DESC'); ?>
		</div>
		<?php if (empty($this->otpConfig->otep)) : ?>
			<div class="alert alert-warning">
				<span class="icon-exclamation-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
				<?php echo Text::_('COM_ADMIN_PROFILE_OTEPS_WAIT_DESC'); ?>
			</div>
		<?php else : ?>
		<?php foreach ($this->otpConfig->otep as $otep) : ?>
		<span class="col-lg-3">
			<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
		</span>
		<?php endforeach; ?>
		<?php endif; ?>
	</fieldset>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->get('return', '', 'BASE64'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
