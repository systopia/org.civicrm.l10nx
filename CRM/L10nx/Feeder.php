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
    return [
        'hook_civicrm_fieldOptions' => [
            ['translateFieldOptions', Events::W_MIDDLE],
        ],
        'civi.l10n.dts_post' => [
            ['convertDataTS', Events::W_MIDDLE],
        ],
    ];
  }

  /**
   * Inject custom MO files according to the configuration
   *
   * @param GenericHookEvent $ts_event mo event
   */
  public function convertDataTS(GenericHookEvent $ts_event) {
//    $locale  = $ts_event->locale;
//    $context = empty($ts_event->context) ? 'None' : $ts_event->context;
//    $domain  = $ts_event->domain;
//
//    // postprocess domain
//    if (is_array($domain)) {
//      $domain = reset($domain);
//    }
//    if (empty($domain)) {
//      $domain = 'civicrm';
//    }
//
//    // fill cache
//    if (!isset($this->cache[$locale][$domain][$context])) {
//      $this->cache[$locale][$domain][$context] = $this->amend_mo_files($locale, $domain, $context, $ts_event->mo_file_paths);
//    }
//
//    $ts_event->mo_file_paths = $this->cache[$locale][$domain][$context];
  }


  /**
   * Translate the hook_civicrm_fieldOptions hook into individual ts() calls
   *
   * @param GenericHookEvent $fo_event fieldOptions hook
   */
  public function translateFieldOptions(GenericHookEvent $fo_event) {
    //'entity', 'field', 'options', 'params']
    //CRM_Core_Error::debug_log_message("translateFieldOptions {$fo_event->entity}.{$fo_event->field}." . json_encode($fo_event->params));
    if ($fo_event->field == 'phone_type_id') {
      CRM_Core_Error::debug_log_message("OPTIONS: " . json_encode($fo_event->options));
    }
    foreach ($fo_event->options as $key => &$label) {
      // TODO: use context
      // $label = ts($label, ['domain' => 'civicrm-option', 'context' => "{$fo_event->entity}.{$fo_event->field}"]);
      $label = ts($label, ['domain' => 'civicrm-option']);
    }
  }


}