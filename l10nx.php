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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function l10nx_civicrm_install() {
  _l10nx_civix_civicrm_install();
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
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function l10nx_civicrm_navigationMenu(&$menu) {
  _l10nx_civix_insert_navigation_menu($menu, 'Administer/Localization', [
      'label'      => E::ts('Extended Configuration (l10nx)'),
      'name'       => 'l10nx_config',
      'url'        => 'civicrm/admin/l10nx',
      'permission' => 'administer CiviCRM',
      'operator'   => 'OR',
      'separator'  => 0,
  ]);
  _l10nx_civix_navigationMenu($menu);
}

/**
 * Issue a warning, if you are on admin pages, but you're not using the data locale
 *
 * Implements hook_civicrm_buildForm().
 * @todo do we need this?
 */
function _todo_l10nx_civicrm_buildForm($formName, &$form) {
  if (strstr($_SERVER['REQUEST_URI'], 'civicrm/admin')) {
    $data_locale = CRM_L10nx_Configuration::get()->getDataLocale();
    $user_locale = CRM_Core_I18n::getLocale();
    if ($data_locale != $user_locale) {
      CRM_Core_Session::setStatus(E::ts("You have set the data language to {$data_locale}, but you're currently using the system in {$user_locale}. Make sure you know what you're doing."), E::ts("Be careful!"), 'info');
    }
  }
}
