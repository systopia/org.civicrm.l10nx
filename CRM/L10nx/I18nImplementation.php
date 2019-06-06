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

/**
 * CAUTION: WORK IN PROGRESS
 *
 * This class will once implement the new I18n interface
 */
class CRM_L10nx_I18nImplementation implements CRM_Core_I18n_Interface {

  /**
   * Function to connect this extension to CiviCRM's translation core
   * by providing custom ts() and dts() implementations
   */
  public static function config() {
    // TODO: inject our ts/dts functions
    // TODO: set
  }

  /**
   * Old-style custom ts function
   *
   * @param $text
   * @param array $params
   *
   * @return string translated string
   */
  public static function legacy_ts($text, $params = array()) {
    return $text;
  }

  /**
   * @param $locale
   * @return CRM_L10nx_I18n
   */
  public static function create($locale) {
    return new CRM_L10nx_I18n($locale);
  }





  public function setLocale($locale) {
     // TODO: implement
  }




  /**
   * Set getText locale.
   *
   * @param string $locale
   */
  protected function setPhpGettextLocale($locale) {

    // we support both the old file hierarchy format and the new:
    require_once 'PHPgettext/streams.php';
    require_once 'PHPgettext/gettext.php';

    // TODO
    $mo_file = CRM_Core_I18n::getResourceDir() . $locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . 'civicrm.mo';

    $streamer = new FileReader($mo_file);
    $this->_phpgettext = new gettext_reader($streamer);
    $this->_extensioncache['civicrm'] = $this->_phpgettext;
  }

  /**
   * Binds a gettext domain, wrapper over bindtextdomain().
   *
   * @param $key
   *   Key of the extension (can be 'civicrm', or 'org.example.foo').
   *
   * @return Bool
   *   True if the domain was changed for an extension.
   */
  public function setGettextDomain($key) {
    /* No domain changes for en_US */
    if (!$this->_phpgettext) {
      return FALSE;
    }

    // It's only necessary to find/bind once
    if (!isset($this->_extensioncache[$key])) {
      try {
        $mapper = CRM_Extension_System::singleton()->getMapper();
        $path = $mapper->keyToBasePath($key);
        $info = $mapper->keyToInfo($key);
        $domain = $info->file;

        if ($this->_nativegettext) {
          bindtextdomain($domain, $path . DIRECTORY_SEPARATOR . 'l10n');
          bind_textdomain_codeset($domain, 'UTF-8');
          $this->_extensioncache[$key] = $domain;
        }
        else {
          // phpgettext
          $mo_file = $path . DIRECTORY_SEPARATOR . 'l10n' . DIRECTORY_SEPARATOR . $this->locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $domain . '.mo';
          $streamer = new FileReader($mo_file);
          $this->_extensioncache[$key] = new gettext_reader($streamer);
        }
      }
      catch (CRM_Extension_Exception $e) {
        // Intentionally not translating this string to avoid possible infinite loops
        // Only developers should see this string, if they made a mistake in their ts() usage.
        CRM_Core_Session::setStatus('Unknown extension key in a translation string: ' . $key, '', 'error');
        $this->_extensioncache[$key] = FALSE;
      }
    }

    if (isset($this->_extensioncache[$key]) && $this->_extensioncache[$key]) {
      if ($this->_nativegettext) {
        textdomain($this->_extensioncache[$key]);
      }
      else {
        $this->_phpgettext = $this->_extensioncache[$key];
      }

      return TRUE;
    }

    return FALSE;
  }

}