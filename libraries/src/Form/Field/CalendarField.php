<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 *
 * @since  1.7.0
 */
class CalendarField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Calendar';

	/**
	 * The allowable maxlength of calendar field.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $maxlength;

	/**
	 * The format of date and time.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $format;

	/**
	 * The filter.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $filter;

	/**
	 * The minimum year number to subtract/add from the current year
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	protected $minyear;

	/**
	 * The maximum year number to subtract/add from the current year
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	protected $maxyear;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $layout = 'joomla.form.field.calendar';

	/**
	 * The parent class of the field
	 *
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $parentclass;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'maxlength':
			case 'format':
			case 'filter':
			case 'timeformat':
			case 'todaybutton':
			case 'singleheader':
			case 'weeknumbers':
			case 'showtime':
			case 'filltable':
			case 'minyear':
			case 'maxyear':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'maxlength':
			case 'timeformat':
				$this->$name = (int) $value;
				break;
			case 'todaybutton':
			case 'singleheader':
			case 'weeknumbers':
			case 'showtime':
			case 'filltable':
			case 'format':
			case 'filter':
			case 'minyear':
			case 'maxyear':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->maxlength    = (int) $this->element['maxlength'] ? (int) $this->element['maxlength'] : 45;
			$this->format       = (string) $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
			$this->filter       = (string) $this->element['filter'] ? (string) $this->element['filter'] : 'USER_UTC';
			$this->todaybutton  = (string) $this->element['todaybutton'] ? (string) $this->element['todaybutton'] : 'true';
			$this->weeknumbers  = (string) $this->element['weeknumbers'] ? (string) $this->element['weeknumbers'] : 'true';
			$this->showtime     = (string) $this->element['showtime'] ? (string) $this->element['showtime'] : 'false';
			$this->filltable    = (string) $this->element['filltable'] ? (string) $this->element['filltable'] : 'true';
			$this->timeformat   = (int) $this->element['timeformat'] ? (int) $this->element['timeformat'] : 24;
			$this->singleheader = (string) $this->element['singleheader'] ? (string) $this->element['singleheader'] : 'false';
			$this->minyear      = \strlen((string) $this->element['minyear']) ? (string) $this->element['minyear'] : null;
			$this->maxyear      = \strlen((string) $this->element['maxyear']) ? (string) $this->element['maxyear'] : null;

			if ($this->maxyear < 0 || $this->minyear > 0)
			{
				$this->todaybutton = 'false';
			}
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		$user = Factory::getUser();

		// Translate the format if requested
		$translateFormat = (string) $this->element['translateformat'];

		if ($translateFormat && $translateFormat !== 'false')
		{
			$showTime = (string) $this->element['showtime'];

			$lang  = Factory::getLanguage();
			$debug = $lang->setDebug(false);

			if ($showTime && $showTime !== 'false')
			{
				$this->format = Text::_('DATE_FORMAT_CALENDAR_DATETIME');
			}
			else
			{
				$this->format = Text::_('DATE_FORMAT_CALENDAR_DATE');
			}

			$lang->setDebug($debug);
		}

		// If a known filter is given use it.
		switch (strtoupper($this->filter))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ($this->value && $this->value != Factory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$date = Factory::getDate($this->value, 'UTC');
					$date->setTimezone(new \DateTimeZone(Factory::getApplication()->get('offset')));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ($this->value && $this->value != Factory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$date = Factory::getDate($this->value, 'UTC');
					$date->setTimezone($user->getTimezone());

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
		}

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($this->value && $this->value != Factory::getDbo()->getNullDate() && strtotime($this->value) !== false)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$this->value = strftime($this->format, strtotime($this->value));
			date_default_timezone_set($tz);
		}
		else
		{
			$this->value = '';
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  3.7.0
	 */
	protected function getLayoutData()
	{
		$data      = parent::getLayoutData();
		$tag       = Factory::getLanguage()->getTag();
		$calendar  = Factory::getLanguage()->getCalendar();
		$direction = strtolower(Factory::getDocument()->getDirection());

		// Get the appropriate file for the current language date helper
		$helperPath = 'system/fields/calendar-locales/date/gregorian/date-helper.min.js';

		if (!empty($calendar) && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar)))
		{
			$helperPath = 'system/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
		}

		// Get the appropriate locale file for the current language
		$localesPath = 'system/fields/calendar-locales/en.js';

		if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . strtolower($tag) . '.js';
		}
		elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . $tag . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . $tag . '.js';
		}
		elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
		}

		$extraData = array(
			'value'        => $this->value,
			'maxLength'    => $this->maxlength,
			'format'       => $this->format,
			'filter'       => $this->filter,
			'todaybutton'  => ($this->todaybutton === 'true') ? 1 : 0,
			'weeknumbers'  => ($this->weeknumbers === 'true') ? 1 : 0,
			'showtime'     => ($this->showtime === 'true') ? 1 : 0,
			'filltable'    => ($this->filltable === 'true') ? 1 : 0,
			'timeformat'   => $this->timeformat,
			'singleheader' => ($this->singleheader === 'true') ? 1 : 0,
			'helperPath'   => $helperPath,
			'localesPath'  => $localesPath,
			'minYear'      => $this->minyear,
			'maxYear'      => $this->maxyear,
			'direction'    => $direction,
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Method to filter a field value.
	 *
	 * @param   mixed     $value  The optional value to use as the default for the field.
	 * @param   string    $group  The optional dot-separated form group path on which to find the field.
	 * @param   Registry  $input  An optional Registry object with the entire data set to filter
	 *                            against the entire form.
	 *
	 * @return  mixed   The filtered value.
	 *
	 * @since   4.0.0
	 */
	public function filter($value, $group = null, Registry $input = null)
	{
		$app = Factory::getApplication();

		// Make sure there is a valid SimpleXMLElement.
		if (!($this->element instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::filter `element` is not an instance of SimpleXMLElement', \get_class($this)));
		}

		// Get the field filter type.
		$filter = (string) $this->element['filter'];

		$return = $value;

		switch (strtoupper($filter))
		{
			// Convert a date to UTC based on the server timezone offset.
			case 'SERVER_UTC':
				if ((int) $value > 0)
				{
					// Get the server timezone setting.
					$offset = $app->get('offset');

					// Return an SQL formatted datetime string in UTC.
					$return = Factory::getDate($value, $offset)->toSql();
				}
				else
				{
					$return = '';
				}
				break;

			// Convert a date to UTC based on the user timezone offset.
			case 'USER_UTC':
				if ((int) $value > 0)
				{
					// Get the user timezone setting defaulting to the server timezone setting.
					$offset = Factory::getUser()->getParam('timezone', $app->get('offset'));

					// Return an SQL formatted datetime string in UTC.
					$return = Factory::getDate($value, $offset)->toSql();
				}
				else
				{
					$return = '';
				}
				break;
		}

		return $return;
	}
}
