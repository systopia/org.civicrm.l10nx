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

use Civi\API\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Civi\Core\Event\GenericHookEvent;


/**
 * The Feeder class implements the various events raised by the Core system,
 *  and translates them into the right ts() calls
 */
class CRM_L10nx_Feeder implements EventSubscriberInterface {

  /**
   * Define which events we subscribe to
   * @return array subscriptions
   */
  public static function getSubscribedEvents() {
    $config = CRM_L10nx_Configuration::get();
    $events = [];

    if ($config->translateOptions()) {
      $events['hook_civicrm_fieldOptions'] = [
          ['translateFieldOptions', Events::W_MIDDLE],
      ];
    }

    if ($config->translateData()) {
      $events['civi.l10n.dts_post'] =[
          ['convertDataTS', Events::W_MIDDLE],
      ];
    }

    return $events;
  }

  /**
   * Pass on a dts hook event to the ts hook
   *
   * @param GenericHookEvent $dts_event mo event
   */
  public function convertDataTS(GenericHookEvent $dts_event) {
    // TODO: evaluate params, take care of locale, etc.

    // add context?
    $params = $dts_event->params;
    $config = CRM_L10nx_Configuration::get();
    if ($config->useTranslationContextForData()) {
      $params['context'] = $dts_event->params['table_name'] . '.' . $dts_event->params['column_name'];
    }
    $params['domain'] = 'civicrm-data';
    $dts_event->translated_text = ts($dts_event->original_text, $params);
  }


  /**
   * Translate the hook_civicrm_fieldOptions hook into individual ts() calls
   *
   * @param GenericHookEvent $fo_event fieldOptions hook, contains: 'entity', 'field', 'options', 'params'
   */
  public function translateFieldOptions(GenericHookEvent $fo_event) {
    $context = isset($fo_event->params['context']) ? $fo_event->params['context'] : '';
    if ($context == 'validate' || $context == 'abbreviate') {
      // these modes don't expect translated labels
      // see https://docs.civicrm.org/dev/en/latest/framework/pseudoconstant#context
      return;
    }

    // turn off for admin pages
    // TODO: is this necessary?
    if (strstr($_SERVER['REQUEST_URI'], 'civicrm/admin')) {
      return;
    }

    // look up option group
    $config = CRM_L10nx_Configuration::get();
    $option_group = $this->getOptionGroupName($fo_event);
    if ($option_group == 'IGNORE') {
      return; // dont' do anything

    } else {
      // send each value through translation
      if ($config->translateOptionGroup($option_group)) {
        if ($config->useTranslationContextForOptions()) {
          foreach ($fo_event->options as $key => &$label) {
            $label = ts($label, ['domain' => 'civicrm-option', 'context' => $option_group]);
          }
        } else {
          foreach ($fo_event->options as $key => &$label) {
            $label = ts($label, ['domain' => 'civicrm-option']);
          }
        }
      }
    }
  }


  /**
   * Get the option group behind this civicrm_fieldOptions hook event
   *
   * @param $fo_event
   */
  protected function getOptionGroupName($fo_event) {
    $entity = $fo_event->entity;
    $field  = $fo_event->field;

    $hardcoded_mapping = CRM_L10nx_Configuration::getOptionGroupMapping();
    if (isset($hardcoded_mapping[$entity][$field])) {
      return $hardcoded_mapping[$entity][$field];
    }

    // TODO: custom field groups

    // NOT FOUND:
    CRM_Core_Error::debug_log_message("Missing [$entity][$field] /  " .json_encode($fo_event->params));


//    static $cached_mapping = [];
//    $cache_key = "{$entity}.{$field}";
//    if (!isset($cached_mapping[$cache_key])) {
//      // first find the entity
//      $entities = CRM_Core_DAO_AllCoreTables::get();
//      foreach ($entities as $entity) {
//        if ($entity['name'] == $entity) {
//          CRM_Core_Error::debug_log_message($entity['class_name']);
//          break;
//        }
//      }
//    }
  }
}
