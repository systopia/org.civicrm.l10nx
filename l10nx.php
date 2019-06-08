<?php

require_once 'l10nx.civix.php';
use CRM_L10nx_ExtensionUtil as E;

/**
 * Old-style custom ts function to hook into the system
 *
 * @param $text
 * @param array $params
 *
 * @return string translated string
 */
function l10nx_legacy_ts($text, $params = array()) {
  $activeLocale = CRM_Core_I18n::getLocale();

  require_once 'CRM/L10nx/I18n.php';
  if (defined('CIVICRM_GETTEXT_NATIVE') && CIVICRM_GETTEXT_NATIVE && function_exists('gettext')) {
    require_once 'CRM/L10nx/I18nNative.php';
    $l10n = CRM_L10nx_I18nNative::getInstance($activeLocale);
  } else {
    $l10n = CRM_L10nx_I18n::getInstance($activeLocale);
  }
  return $l10n->ts($text, $params);
}

/**
 * Inject API translation hook
 */
function l10nx_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if (CRM_L10nx_Configuration::get()->translateData()) {
    $wrappers[] = new CRM_L10nx_ApiTranslator();
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function l10nx_civicrm_config(&$config) {
  _l10nx_civix_civicrm_config($config);

  // install the link
  CRM_L10nx_I18n::config();

  // enable injection
  require_once 'CRM/L10nx/Feeder.php';
  \Civi::dispatcher()->addSubscriber(new CRM_L10nx_Feeder());
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function l10nx_civicrm_xmlMenu(&$files) {
  _l10nx_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function l10nx_civicrm_install() {
  _l10nx_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function l10nx_civicrm_postInstall() {
  _l10nx_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function l10nx_civicrm_uninstall() {
  _l10nx_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function l10nx_civicrm_enable() {
  _l10nx_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function l10nx_civicrm_disable() {
  _l10nx_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function l10nx_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _l10nx_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function l10nx_civicrm_managed(&$entities) {
  _l10nx_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function l10nx_civicrm_caseTypes(&$caseTypes) {
  _l10nx_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function l10nx_civicrm_angularModules(&$angularModules) {
  _l10nx_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function l10nx_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _l10nx_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function l10nx_civicrm_entityTypes(&$entityTypes) {
  _l10nx_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function l10nx_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function l10nx_civicrm_navigationMenu(&$menu) {
  _l10nx_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _l10nx_civix_navigationMenu($menu);
} // */
