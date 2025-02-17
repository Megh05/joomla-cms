<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var JDocumentHtml $this */

$app   = Factory::getApplication();
$input = $app->input;
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option = $input->get('option', '');
$view   = $input->get('view', '');
$layout = $input->get('layout', 'default');
$task   = $input->get('task', 'display');

require_once __DIR__ . '/Service/HTML/Atum.php';

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Template params
$logoBrandLarge  = $this->params->get('logoBrandLarge')
	? Uri::root() . htmlspecialchars($this->params->get('logoBrandLarge'), ENT_QUOTES)
	: $this->baseurl . '/templates/' . $this->template . '/images/logos/brand-large.svg';
$loginLogo = $this->params->get('loginLogo')
	? Uri::root() . $this->params->get('loginLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logos/login.svg';
$logoBrandSmall = $this->params->get('logoBrandSmall')
	? Uri::root() . htmlspecialchars($this->params->get('logoBrandSmall'), ENT_QUOTES)
	: $this->baseurl . '/templates/' . $this->template . '/images/logos/brand-small.svg';

$logoBrandLargeAlt = empty($this->params->get('logoBrandLargeAlt')) && empty($this->params->get('emptyLogoBrandLargeAlt'))
	? ''
	: 'alt="' . htmlspecialchars($this->params->get('logoBrandLargeAlt'), ENT_COMPAT, 'UTF-8') . '"';
$logoBrandSmallAlt = empty($this->params->get('logoBrandSmallAlt')) && empty($this->params->get('emptyLogoBrandSmallAlt'))
	? ''
	: 'alt="' . htmlspecialchars($this->params->get('logoBrandSmallAlt'), ENT_COMPAT, 'UTF-8') . '"';
$loginLogoAlt = empty($this->params->get('loginLogoAlt')) && empty($this->params->get('emptyLoginLogoAlt'))
	? ''
	: 'alt="' . htmlspecialchars($this->params->get('loginLogoAlt'), ENT_COMPAT, 'UTF-8') . '"';

// Enable assets
$wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
	->useStyle('template.active.language')
	->useStyle('template.user');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');

$monochrome = (bool) $this->params->get('monochrome');

HTMLHelper::getServiceRegistry()->register('atum', 'JHtmlAtum');
HTMLHelper::_('atum.rootcolors', $this->params);

// Add cookie alert message
Text::script('JGLOBAL_WARNCOOKIES');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ($task ? ' task-' . $task : '') . ($monochrome ? ' monochrome' : ''); ?>">

<noscript>
	<div class="alert alert-danger" role="alert">
		<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</div>
</noscript>
<div class="ie11 alert alert-warning" role="alert">
	<?php echo Text::_('JGLOBAL_WARNIE'); ?>
</div>

<header id="header" class="header">
	<div class="d-flex">
		<div class="header-title d-flex">
			<div class="d-flex align-items-center">
				<div class="logo">
					<img src="<?php echo $logoBrandLarge; ?>" <?php echo $logoBrandLargeAlt; ?>>
					<img class="logo-collapsed" src="<?php echo $logoBrandSmall; ?>"  <?php echo $logoBrandSmallAlt; ?>>
				</div>
			</div>
			<jdoc:include type="modules" name="title" />
		</div>
		<div class="header-items d-flex">
			<jdoc:include type="modules" name="status" style="header-item" />
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<div class="container-fluid container-main order-1">
		<section id="content" class="content h-100">
			<div class="login_message">
				<jdoc:include type="message" />
			</div>
			<main class="d-flex justify-content-center align-items-center h-100">
				<div class="login">
					<div class="main-brand logo text-center">
						<img src="<?php echo $loginLogo; ?>" <?php echo $loginLogoAlt; ?>>
					</div>
					<jdoc:include type="component" />
				</div>
			</main>
		</section>
	</div>

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper px-3">
		<div id="main-brand" class="main-brand">
			<h1><?php echo $app->get('sitename'); ?></h1>
			<h2><?php echo Text::_('TPL_ATUM_BACKEND_LOGIN'); ?></h2>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body" />
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none" />
<jdoc:include type="scripts" />
</body>
</html>
