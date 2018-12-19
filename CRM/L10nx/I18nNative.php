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
 * I18n implementation for native gettext
 *
 * @todo IMPLEMENT FOR NATIVE GETTEXT
 */
class CRM_L10nx_I18nNative extends CRM_L10nx_I18n {

  /**
   * Get the localisation instance for the given locale
   * @param string $locale the locale
   * @return CRM_L10nx_I18n instance
   */
  public static function getInstance($locale) {
    if (!isset(self::$instances[$locale])) {
      self::$instances[$locale] = new CRM_L10nx_I18nNative($locale);
    }
    return self::$instances[$locale];
  }

  /**
   * CRM_L10nx_I18n constructor.
   * @param string $locale
   */
  protected function __construct($locale) {
    parent::__construct($locale);
  }


  /**
   * Get the phpgettext reader
   * @param string $mo_file
   * @return gettext_reader reader
   */
  public function getGettextReader($mo_file) {
    // TODO: adjust to native gettext
    if (!isset(self::$gettext_resource[$mo_file])) {
      require_once 'PHPgettext/streams.php';
      require_once 'PHPgettext/gettext.php';
      $streamer                         = new FileReader($mo_file);
      self::$gettext_resource[$mo_file] = new gettext_reader($streamer);
    }

    return self::$gettext_resource[$mo_file];
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
   *
   * @return string
   */
  protected function crm_translate_gettext($text, $mo_file, $count, $plural, $context) {
    // TODO: adjust to native gettext
    $php_gettext = $this->getGettextReader($mo_file);
    // use plural if required parameters are set
    if (isset($count) && isset($plural)) {
      $text = $php_gettext->ngettext($text, $plural, $count);

      // expand %count in translated string to $count
      return strtr($text, array('%count' => $count));

      // if not plural, but the locale's set, translate
    } else {
      if ($context) {
        return $php_gettext->pgettext($context, $text);
      } else {
        return $php_gettext->translate($text);
      }
    }
  }
}