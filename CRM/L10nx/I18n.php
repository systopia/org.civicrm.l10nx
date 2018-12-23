<?php
/*-------------------------------------------------------+
| L10n Extension - extended functionality for l10n       |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

require_once 'Hook.php';

/**
 * Basic I18n implementation using phpgettext
 */
class CRM_L10nx_I18n {

  /**
   * @var array stores existing instances by locale
   */
  protected static $instances = [];

  /**
   * @var array mo_file_name => gettext reader
   */
  protected static $gettext_resource = [];


  /**
   * @var array domain -> default mo
   */
  protected $default_mos = [];

  /**
   * @var string the locale this instance is representing
   */
  protected $locale;


  /**
   * Get the localisation instance for the given locale
   * @param string $locale the locale
   * @return CRM_L10nx_I18n instance
   */
  public static function getInstance($locale) {
    if (!isset(self::$instances[$locale])) {
      self::$instances[$locale] = new CRM_L10nx_I18n($locale);
    }
    return self::$instances[$locale];
  }

  /**
   * CRM_L10nx_I18n constructor.
   * @param string $locale
   */
  protected function __construct($locale) {
    $this->locale = $locale;

    // set civicrm default mo
    $this->default_mos['civicrm'] = CRM_Core_I18n::getResourceDir() . $this->locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . 'civicrm.mo';
  }

  /**
   * Replacement for static string translation
   *
   * @param string $text
   *   String string for translating.
   * @param array $params
   *   Array an array of additional parameters.
   *
   * @return string
   *   the translated string
   */
  public function ts($text, $params = array()) {
    // see if there is a custom translation file
    $mo_files = [$this->getDefaultMO(CRM_Utils_Array::value('domain', $params, 'civicrm'))];
    CRM_L10nx_Hook::custom_mo($mo_files, $this->locale, 'ts', CRM_Utils_Array::value('context', $params));

    // run the CiviCRM translation
    $translated = FALSE;
    foreach ($mo_files as $mo_file) {
      $translated_text = $this->crm_translate($text, $params, $mo_file, $translated);

      if ($translated) {
        // translation successful
        break;
      }
    }

    // send the post event
    CRM_L10nx_Hook::ts_post($this->locale, $text, $translated_text, $params);

    // return the result
    return $translated_text;
  }

  /**
   * Translate the given string with the given parameters and the given mo_file
   *
   * Mostly copied from CRM_Core_I18n:crm_translate
   *
   * @param string $text            the text to be translated
   * @param array  $params          translation parameters
   * @param string $mo_file         the mo_file to be used
   * @param bool   $translated      did the string get translated?
   * @return string translation
   */
  public function crm_translate($text, $params, $mo_file, &$translated) {
    $original_text = $text;

    if (isset($params['escape'])) {
      $escape = $params['escape'];
      unset($params['escape']);
    }

    // sometimes we need to {ts}-tag a string, but don’t want to
    // translate it in the template (like civicrm_navigation.tpl),
    // because we handle the translation in a different way (CRM-6998)
    // in such cases we return early, only doing SQL/JS escaping
    if (isset($params['skip']) and $params['skip']) {
      if (isset($escape) and ($escape == 'sql')) {
        $text = self::escapeSql($text);
      }
      if (isset($escape) and ($escape == 'js')) {
        $text = addcslashes($text, "'");
      }

      $translated = TRUE;
      return $text;
    }

    $plural = $count = NULL;
    if (isset($params['plural'])) {
      $plural = $params['plural'];
      unset($params['plural']);
      if (isset($params['count'])) {
        $count = $params['count'];
      }
    }

    if (isset($params['context'])) {
      $context = $params['context'];
      unset($params['context']);
    } else {
      $context = NULL;
    }

    if (isset($params['domain'])) {
      $domain = $params['domain'];
      unset($params['domain']);
    } else {
      $domain = NULL;
    }

    $raw = !empty($params['raw']);
    unset($params['raw']);

    if (!empty($domain)) {
      // It might be prettier to cast to an array, but this is high-traffic stuff.
      if (is_array($domain)) {
        foreach ($domain as $d) {
          $candidate = $this->crm_translate_raw($text, $d, $count, $plural, $context, $mo_file, $translated);
          if ($candidate != $text) {
            $text = $candidate;
            break;
          }
        }
      } else {
        $text = $this->crm_translate_raw($text, $domain, $count, $plural, $context, $mo_file, $translated);
      }
    } else {
      $text = $this->crm_translate_raw($text, NULL, $count, $plural, $context, $mo_file, $translated);
    }

    // replace the numbered %1, %2, etc. params if present
    if (count($params) && !$raw) {
      $text = $this->strarg($text, $params);
    }

    // escape SQL if we were asked for it
    if (isset($escape) and ($escape == 'sql')) {
      $text = self::escapeSql($text);
    }

    // escape for JavaScript (if requested)
    if (isset($escape) and ($escape == 'js')) {
      $text = addcslashes($text, "'");
    }

    // FIXME: ask subfunction

    $translated = ($original_text != $text);
    return $text;
  }


  /**
   * Get the default mo file for the given domain
   *
   * @param string $domain the domain
   * @return string default mo path
   */
  public function getDefaultMO($domain) {
    if (empty($domain)) {
      $domain = 'civicrm';
    }

    if (is_array($domain)) {
      $domain = reset($domain);
    }

    if (!isset($this->default_mos[$domain])) {
      try {
        // could be an extension
        $mapper = CRM_Extension_System::singleton()->getMapper();
        $path = $mapper->keyToBasePath($domain);
        $info = $mapper->keyToInfo($domain);
        $domain_file = $info->file;

        // phpgettext
        $mo_file = $path . DIRECTORY_SEPARATOR . 'l10n' . DIRECTORY_SEPARATOR . $this->locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $domain_file . '.mo';
        if (file_exists($mo_file)) {
          $this->default_mos[$domain] = $mo_file;
        } else {
          // no localisation present
          // CRM_Core_Session::setStatus("No mo files found for {$domain} / {$this->locale}:", 'error');
        }
      }
      catch (Exception $e) {
        // Intentionally not translating this string to avoid possible infinite loops
        // Only developers should see this string, if they made a mistake in their ts() usage.
        CRM_Core_Session::setStatus('Unknown extension key in a translation string: ' . $domain, '', 'error');
      }

      if (!isset($this->default_mos[$domain])) {
        // if this didn't work, we fall back to civicrm
        $this->default_mos[$domain] = $this->default_mos['civicrm'];
      }
    }

    return $this->default_mos[$domain];
  }


  /**
   * Get the phpgettext reader
   * @param string $mo_file
   * @return gettext_reader reader
   */
  public function getGettextReader($mo_file) {
    if (!isset(self::$gettext_resource[$mo_file])) {
      require_once 'PHPgettext/streams.php';
      require_once 'PHPgettext/gettext.php';
      $streamer                         = new FileReader($mo_file);
      self::$gettext_resource[$mo_file] = new gettext_reader($streamer);
    }

    return self::$gettext_resource[$mo_file];
  }




  /**
   * Lookup the raw translation of a string (without any extra escaping or interpolation).
   *
   * Mostly copied from CRM_Core_I18n:crm_translate
   *
   * @param string $text
   * @param string|NULL $domain
   * @param int|NULL $count
   * @param string $plural
   * @param string $context
   * @param string $mo_file
   * @param bool $translated
   *
   * @return string
   */
  protected function crm_translate_raw($text, $domain, $count, $plural, $context, $mo_file, &$translated) {
    // do all wildcard translations first

    // FIXME: Is there a constant we can reference instead of hardcoding en_US?
    $replacementsLocale = $this->locale ? $this->locale : 'en_US';
    if (!isset(Civi::$statics[__CLASS__]) || !array_key_exists($replacementsLocale, Civi::$statics[__CLASS__])) {
      if (defined('CIVICRM_DSN') && !CRM_Core_Config::isUpgradeMode()) {
        Civi::$statics[__CLASS__][$replacementsLocale] = CRM_Core_BAO_WordReplacement::getLocaleCustomStrings($replacementsLocale);
      } else {
        Civi::$statics[__CLASS__][$replacementsLocale] = array();
      }
    }
    $stringTable = Civi::$statics[__CLASS__][$replacementsLocale];

    $exactMatch = FALSE;
    if (isset($stringTable['enabled']['exactMatch'])) {
      foreach ($stringTable['enabled']['exactMatch'] as $search => $replace) {
        if ($search === $text) {
          $exactMatch = TRUE;
          $translated = TRUE;
          $text       = $replace;
          break;
        }
      }
    }

    if (
        !$exactMatch &&
        isset($stringTable['enabled']['wildcardMatch'])
    ) {
      $search  = array_keys($stringTable['enabled']['wildcardMatch']);
      $replace = array_values($stringTable['enabled']['wildcardMatch']);
      $text    = str_replace($search, $replace, $text);
      $translated = TRUE;
    }

    // dont translate if we've done exactMatch already
    if (!$exactMatch) {
      $text = $this->crm_translate_gettext($text, $mo_file, $count, $plural, $context, $translated);
    }

    return $text;
  }

  /**
   * relay the translation to the actual gettext function
   *
   * Mostly copied from CRM_Core_I18n:crm_translate_raw
   *
   * @param string $text
   * @param int|NULL $count
   * @param string $plural
   * @param string $context
   * @param string $mo_file
   * @param bool $translated
   *
   * @return string
   */
  protected function crm_translate_gettext($text, $mo_file, $count, $plural, $context, &$translated) {
    $php_gettext = $this->getGettextReader($mo_file);
    // use plural if required parameters are set
    if (isset($count) && isset($plural)) {
      $translated_text = $php_gettext->ngettext($text, $plural, $count);

      // expand %count in translated string to $count
      $translated_text = strtr($translated_text, array('%count' => $count));

      // if not plural, but the locale's set, translate
    } else {
      if ($context) {
        $translated_text = $php_gettext->pgettext($context, $text);
      } else {
        $translated_text = $php_gettext->translate($text);
      }
    }
    $translated = ($text != $translated_text);
    return $translated_text;
  }




  /**
   * Encode a string for use in SQL.
   *
   * Copied from CRM_Core_I18n:escapeSql
   *
   * @param string $text
   * @return string
   */
  protected static function escapeSql($text) {
    if (CRM_Core_I18n::$SQL_ESCAPER == NULL) {
      return CRM_Core_DAO::escapeString($text);
    }
    else {
      return call_user_func(CRM_Core_I18n::$SQL_ESCAPER, $text);
    }
  }

  /**
   * Replace arguments in a string with their values. Arguments are represented by % followed by their number.
   *
   * Copied from CRM_Core_I18n:strarg
   *
   * @param string $str
   *   source string.
   *
   * @return string
   *   modified string
   */
  public function strarg($str) {
    $tr = array();
    $p = 0;
    for ($i = 1; $i < func_num_args(); $i++) {
      $arg = func_get_arg($i);
      if (is_array($arg)) {
        foreach ($arg as $aarg) {
          $tr['%' . ++$p] = $aarg;
        }
      }
      else {
        $tr['%' . ++$p] = $arg;
      }
    }
    return strtr($str, $tr);
  }




  /**
   * Function to connect this extension to CiviCRM's translation core
   * by providing custom ts() and dts() implementations
   */
  public static function config() {
    // TODO: inject our ts/dts functions
    // TODO: set
  }
}