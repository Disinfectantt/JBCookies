<?php

/**
 * @package			Joomla.Site
 * @subpackage		Modules - mod_jbcookies_modified
 * 
 * @author			JoomBall! Project
 * @link			http://www.joomball.com
 * @copyright		Copyright © 2011-2018 JoomBall! Project. All Rights Reserved.
 * @license			GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();

if ($params->get('show_decline', 1) != 0 || $app->input->cookie->get('jbcookies', null) == null) {

	$domain = str_replace(array('https://www.', 'http://www.', 'https://', 'http://'), '', Uri::base());

	if ((strpos($domain, '/') !== false) || (strstr($domain, 'localhost', true) !== false)) :
		$domain = '';
	elseif ($params->get('subdomain_alias', 0) && count(explode('.', $domain)) > 1) :
		$parts = explode('.', $domain);
		$domain = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
	else :
		$domain = '';
	endif;

	// --> Afegeixo un arxiu d'estil
	HTMLHelper::_('stylesheet', 'media/jbmedia/css/cookies.css', array('version' => 'auto'));

	if ($params->get('color_option', 'selectable') == 'selectable') :
		$color_background = $params->get('color_background', 'black');
		$color_links = $params->get('color_links', 'blue');
	else :
		$params->set('layout', 'default_custom'); // Modify layout

		$color_background = $params->get('color_background_custom', '#000000');
		$color_links = $params->get('color_links_custom', '#37a4fc');
		$color_text = $params->get('color_text_custom', '#ffffff');
		$btn_border_color = $params->get('btn_border_color_custom', '#024175');
		$btn_text_color = $params->get('btn_text_color_custom', '#ffffff');
		$btn_start_color = $params->get('btn_start_bgcolor_custom', '#37a4fc');
		$btn_end_color = $params->get('btn_end_bgcolor_custom', '#025fab');
		$btn_width = (int) $params->get('btn_width_custom', '100');
		$btn_height = (int) $params->get('btn_height_custom', '30');
	endif;

	$position = $params->get('position', 'bottom');
	$show_info = $params->get('show_info', 1);
	$modal_framework = $params->get('modal', 'bootstrap');
	$framework_version = $params->get('bootstrap_version', 5);
	$show_article_modal = $params->get('show_article_modal', 1);
	$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');
	#$class_sfx			= htmlspecialchars($params->get('class_sfx', ''), ENT_COMPAT, 'UTF-8');
	$color_border = (strpos($color_links, 'btn-') !== false) ? ' border-' . ($position == 'top' ? 'bottom' : 'top') . ' border-' . str_replace('btn-', '', $color_links) : '';

	$lang = Factory::getLanguage();
	$currentLang = $lang->getTag();
	$langs = $params->get('lang');

	$title				= !empty($langs->$currentLang->title) ? $langs->$currentLang->title : Text::_('MOD_JBCOOKIES_MODIFIED_LANG_TITLE_DEFAULT');
	$text				= !empty($langs->$currentLang->text) ? $langs->$currentLang->text : Text::_('MOD_JBCOOKIES_MODIFIED_LANG_TEXT_DEFAULT');
	$header				= !empty($langs->$currentLang->header) ? $langs->$currentLang->header : Text::_('MOD_JBCOOKIES_MODIFIED_LANG_HEADER_DEFAULT');
	$body				= !empty($langs->$currentLang->body) ? $langs->$currentLang->body : Text::_('MOD_JBCOOKIES_MODIFIED_LANG_BODY_DEFAULT');
	$aliasButton		= !empty($langs->$currentLang->alias_button) ? $langs->$currentLang->alias_button : Text::_('MOD_JBCOOKIES_MODIFIED_GLOBAL_ACCEPT');
	$aliasLink			= !empty($langs->$currentLang->alias_link) ? $langs->$currentLang->alias_link : Text::_('MOD_JBCOOKIES_MODIFIED_GLOBAL_MORE_INFO');
	$aLink				= !empty($langs->$currentLang->alink) ? $langs->$currentLang->alink : '';
	$text_decline		= !empty($langs->$currentLang->text_decline) ? $langs->$currentLang->text_decline : Text::_('MOD_JBCOOKIES_MODIFIED_LANG_TITLE_DEFAULT');
	$aliasButton_decline = !empty($langs->$currentLang->alias_button_decline) ? $langs->$currentLang->alias_button_decline : Text::_('MOD_JBCOOKIES_MODIFIED_GLOBAL_DECLINE');
	$color_links_decline = $params->get('decline_btn_link_color', '#37a4fc');

	if ($modal_framework != 'bootstrap' || $framework_version < 5) {
		// Include jQuery
		HTMLHelper::_('jquery.framework');
	}

	if ($params->get('decline_icon', '')) {
		$aliasButton_decline = '<i class="hasTooltip ' . $params->get('decline_icon', '') . '" data-bs-toggle="tooltip" title="' . $aliasButton_decline . '"></i>';
	}

	if ($show_info && $aLink) {
		if ($show_article_modal) {
			if ($modal_framework == 'bootstrap' && $framework_version >= 5) {
				JHtml::_('bootstrap.modal');
			}

			$model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);
			$model->setState('filter.published', 1);

			// Load the parameters.
			$paramsApp = $app->getParams();
			$model->setState('params', $paramsApp);

			// Filter by id
			$model->setState('article.id', (int) $aLink);

			// Retrieve Content
			$item = $model->getItem();

			if (!empty($item->params) && is_object($item->params)) {
				$showInfo = ($item->params->get('show_intro', '1') == '1') ? 1 : 0;
			} else {
				$paramsContent = ComponentHelper::getParams('com_content');
				$showInfo = ($paramsContent->get('show_intro', '1') == '1') ? 1 : 0;
			}

			if ($showInfo) {
				$item->text = $item->introtext . ' ' . $item->fulltext;
			} elseif ($item->fulltext) {
				$item->text = $item->fulltext;
			} else {
				$item->text = $item->introtext;
			}

			$aLink = 0;
			$show_info = 1;
			$header = $item->title;
			$body = $item->text;
		} else {
			$db		= Factory::getDbo();
			$query	= $db->getQuery(true);

			$query->select('a.id, a.alias, a.catid');
			$query->from('#__content AS a');

			// Join on category table.
			$query->select('c.alias AS category_alias')
				->join('LEFT', '#__categories AS c on c.id = a.catid');

			$query->where('a.id = ' . (int) $aLink);

			$db->setQuery((string)$query);
			$item = $db->loadObject();

			// Add router helpers.
			$item->slug			= $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$item->catslug		= $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;

			$item->readmore_link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catslug));
		}
	}

	require ModuleHelper::getLayoutPath('mod_jbcookies_modified', $params->get('layout', 'default'));
?>

	<script type="text/javascript">
		function setCookie(c_name, value, exdays, domain) {
			if (domain != '') {
				domain = '; domain=' + domain;
			}

			let exdate = new Date();
			exdate.setDate(exdate.getDate() + exdays);
			let c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString()) + "; path=/" + domain;

			document.cookie = c_name + "=" + c_value;
		}

		<?php if ($modal_framework == 'bootstrap' && $framework_version >= 5) : ?>
			document.addEventListener("DOMContentLoaded", () => {

				function getItem(item) {
					return document.querySelector(item);
				}

				function animEnd(item) {
					if (item.classList.contains('fadeOutDown')) {
						item.style.display = 'none';
					}
					item.classList.remove('fadeInUp');
					item.classList.remove('fadeOutDown');
				}

				var $jb_cookie = getItem('.jb-cookie'),
					cookieValue = document.cookie.replace(/(?:(?:^|.*;\s*)jbcookies\s*\=\s*([^;]*).*$)|^.*$/, "$1");
				<?php if ($params->get('show_decline', 1)) : ?>
					var jbCookieDecline = getItem('.jb-cookie-decline');
				<?php endif; ?>

				$jb_cookie.addEventListener('animationend', () => animEnd($jb_cookie));

				if (cookieValue === '') { // NO EXIST
					$jb_cookie.style.display = 'block';
					$jb_cookie.classList.add('fadeInUp');
					<?php if ($params->get('show_decline', 1)) : ?>
				} else { // YES EXIST
					jbCookieDecline.style.display = 'block';
					jbCookieDecline.classList.add('fadeInUp');
				<?php endif; ?>
				}

				getItem('.jb-accept').addEventListener('click', () => {
					setCookie("jbcookies", "yes", <?php echo $params->get('duration_cookie_days', 90); ?>, "<?php echo trim($domain); ?>");
					$jb_cookie.classList.add('fadeOutDown');
					<?php if ($params->get('show_decline', 1)) : ?>
						jbCookieDecline.style.display = 'block';
						jbCookieDecline.classList.add('fadeInUp');
					<?php endif; ?>
				});

				<?php if ($params->get('show_decline', 1)) : ?>
					getItem('.jb-decline').addEventListener('click', () => {
						setCookie("jbcookies", "", 0, "<?php echo trim($domain); ?>");
						$jb_cookie.style.display = 'block';
						$jb_cookie.classList.add('fadeInUp');
						jbCookieDecline.classList.add('fadeOutDown');
					});
					jbCookieDecline.addEventListener('animationend', () => animEnd(jbCookieDecline));
				<?php endif; ?>
			});

		<?php else : ?>

			jQuery(document).ready(function() {

				var $jb_cookie = jQuery('.jb-cookie'),
					cookieValue = document.cookie.replace(/(?:(?:^|.*;\s*)jbcookies\s*\=\s*([^;]*).*$)|^.*$/, "$1");

				if (cookieValue === '') { // NO EXIST
					$jb_cookie.delay(1000).slideDown('fast');
					<?php if ($params->get('show_decline', 1)) : ?>
				} else { // YES EXIST
					jQuery('.jb-cookie-decline').fadeIn('slow', function() {});
				<?php endif; ?>
				}

				jQuery('.jb-accept').click(function() {
					setCookie("jbcookies", "yes", <?php echo $params->get('duration_cookie_days', 90); ?>, "<?php echo trim($domain); ?>");
					$jb_cookie.slideUp('slow');
					jQuery('.jb-cookie-decline').fadeIn('slow', function() {});
				});

				jQuery('.jb-decline').click(function() {
					jQuery('.jb-cookie-decline').fadeOut('slow', function() {
						jQuery('.jb-cookie-decline').find('.hasTooltip').tooltip('hide');
					});
					setCookie("jbcookies", "", 0, "<?php echo trim($domain); ?>");
					$jb_cookie.delay(1000).slideDown('fast');
				});
			});
		<?php endif; ?>
	</script>
<?php
}
?>