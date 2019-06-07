<?php
/*-------------------------------------------------------+
| L10n Extension - extended functionality for l10n       |
| Copyright (C) 2019 SYSTOPIA                            |
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
 * Provides the basic configuration for the l10nx behaviour
 */
class CRM_L10nx_Configuration {

  protected $settings = NULL;

  /**
   * Get the current configuration
   * @return CRM_L10nx_Configuration configuration
   */
  public static function get() {
    static $singleton = NULL;
    if ($singleton === NULL) {
      $singleton = new CRM_L10nx_Configuration();
    }
    return $singleton;
  }

  protected function __construct() {
    $this->settings = CRM_Core_BAO_Setting::getItem('l10nx', 'l10nx_settings');
    if (empty($this->settings)) {
        $this->settings = [];
    }
  }

  /**
   * Get all settings
   * @return array all settings
   */
  public function getAllSettings() {
    return $this->settings;
  }

  /**
   * Set/override all settings
   *
   * @param $settings array the new settings
   *
   */
  public function setAllSettings($settings) {
    $this->settings = $settings;
    CRM_Core_BAO_Setting::setItem($settings, 'l10nx', 'l10nx_settings');
  }

  /**
   * Should data from the DB (except option groups) be translated?
   *  These are strings in the ts domain 'civicrm-data'.
   *
   * @return boolean feature enabled?
   */
  public function translateData() {
    return (boolean) CRM_Utils_Array::value('translate_data', $this->settings,FALSE);
  }

  /**
   * Should option group values be translated?
   *  These are strings in the ts domain 'civicrm-option'.
   *
   * @return boolean feature enabled?
   */
  public function translateOptions() {
    return (boolean) CRM_Utils_Array::value('translate_options', $this->settings,FALSE);
  }

  /**
   * Should the option group translation use gettext context?
   * Disadvantage: you have to specify translations for each option value
   * Advantage: you can have different translations for the same label, depending on the option group
   *
   * @return bool
   */
  public function useTranslationContextForOptions() {
    // TODO: implement?
    return FALSE;
  }

  /**
   * Get the list of translated option groups.
   * Warning: does not check if option group translation is active..
   *
   * @return array list of option group names
   */
  public function getTranslatedOptionGroups() {
    return CRM_Utils_Array::value('option_groups', $this->settings, []);
  }

  /**
   * Should the given option group, identified by name, be translated?
   *
   * @param $option_group_name string option group name
   * @return boolean TRUE, if it should be translated
   */
  public function translateOptionGroup($option_group_name) {
    $translated_groups = $this->getTranslatedOptionGroups();
    return empty($translated_groups) || in_array($option_group_name, $translated_groups);
  }

  /**
   * Return the locale (language) in which the data in the database is encoded. This is relevant, if
   *  translateData or translateOptions (see above) is active
   *
   * @return string locale
   */
  public function getDataLocale() {
    return CRM_Utils_Array::value('data_language', $this->settings, 'en_US');
  }
}