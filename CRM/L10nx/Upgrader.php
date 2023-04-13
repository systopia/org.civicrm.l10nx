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

use CRM_L10nx_ExtensionUtil as E;

define('NEW_L10NX_CIVICRM_VERSION', '5.99'); // TODO: adjust once released

/**
 * Collection of upgrade steps.
 */
class CRM_L10nx_Upgrader extends CRM_Extension_Upgrader_Base {

  /**
   * Extension enabled:
   *  if old CiviCRM version,
   */
  public function enable() {
    // set legacy ts function as entry point
    $version = CRM_Utils_System::version();
    if (version_compare($version, NEW_L10NX_CIVICRM_VERSION, '<')) {
      // in the old versions, there was only the custom ts function as an entry point to the l10n system
      $config = CRM_Core_Config::singleton();
      if (empty($config->customTranslateFunction)) {
        // set our translation function
        CRM_Core_BAO_Setting::setItem('l10nx_legacy_ts', 'Localization Preferences', 'customTranslateFunction');
      } else {
        CRM_Core_Session::setStatus(E::ts("There is already a custom ts() function installed, please remove and re-enable this extension."));
      }
    }
  }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  public function disable() {
    // set remove legacy ts function entry point
    $version = CRM_Utils_System::version();
    if (version_compare($version, NEW_L10NX_CIVICRM_VERSION, '<')) {
      // in the old versions, there was only the custom ts function as an entry point to the l10n system
      $config = CRM_Core_Config::singleton();
      if (!empty($config->customTranslateFunction)) {
        if ($config->customTranslateFunction == 'l10nx_legacy_ts') {
          CRM_Core_BAO_Setting::setItem(NULL, 'Localization Preferences', 'customTranslateFunction');
        } else {
          CRM_Core_Session::setStatus(E::ts("Unknown custom ts() found and left active."));
        }
      }
    }
  }
}
