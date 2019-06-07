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
   * @param GenericHookEvent $fo_event fieldOptions hook, contains: 'entity', 'field', 'options', 'params'
   */
  public function translateFieldOptions(GenericHookEvent $fo_event) {
    $context = isset($fo_event->params['context']) ? $fo_event->params['context'] : '';
    if ($context == 'validate' || $context == 'abbreviate') {
      // these modes don't expect translated labels
      // see https://docs.civicrm.org/dev/en/latest/framework/pseudoconstant#context
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
    static $cached_mapping = [];
    $entity = $fo_event->entity;
    $field  = $fo_event->field;

    $hardcoded_mapping = self::getOptionGroupMapping();
    if (isset($hardcoded_mapping[$entity][$field])) {
      return $hardcoded_mapping[$entity][$field];
    }

    // TODO: custom field groups

    // NOT FOUND:
    CRM_Core_Error::debug_log_message("Missing [$entity][$field] /  " .json_encode($fo_event->params));
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


  /**
   * Get a list of known option groups by entity/field
   *
   * Remark: for performance reasons, this is hardcoded rather then dynamically built
   */
  public static function getOptionGroupMapping() {
    static $option_group_mapping = NULL;
    if ($option_group_mapping === NULL) {
      $option_group_mapping = [
          'Contact'                => [
              'prefix_id'                      => 'individual_prefix',
              'suffix_id'                      => 'individual_suffix',
              'contact_sub_type'               => 'civicrm_contact_type__label',
              'preferred_communication_method' => 'preferred_communication_method',
              'preferred_language'             => 'languages',
              'preferred_mail_format'          => 'IGNORE',
              'communication_style_id'         => 'communication_style',
              'gender_id'                      => 'gender_id',
          ],
          'Email'                  => [
              'location_type_id' => 'civicrm_location_type__display_name',
              'on_hold'          => 'IGNORE',
          ],
          'Phone'                  => [
              'location_type_id' => 'civicrm_location_type__display_name',
              'phone_type_id'    => 'phone_type',
          ],
          'IM'                     => [
              'location_type_id' => 'civicrm_location_type__display_name',
              'provider_id'      => 'instant_messenger_service',
          ],
          'Website'                => [
              'location_type_id' => 'civicrm_location_type__display_name',
              'website_type_id'  => 'website_type',
          ],
          'Address'                => [
              'location_type_id'  => 'civicrm_location_type__display_name',
              'state_province_id' => 'IGNORE', // should be translated already / 'civicrm_state_province__name',
              'country_id'        => 'IGNORE', // should be translated already / 'civicrm_country__name',
              'county_id'         => 'IGNORE', // should be translated already / 'civicrm_county__name',
          ],
          'EntityFinancialAccount' => [
              'financial_account_id' => 'IGNORE',
          ],
          'PaymentProcessor'       => [
              'domain_id' => 'IGNORE',
          ],
          'Contribution'           => [
              'payment_instrument_id'  => 'payment_instrument',
              'contribution_status_id' => 'contribution_status',
              'currency'               => 'IGNORE',
          ],
          'Event'                  => [
              'event_type_id'          => 'event_type',
              'default_role_id'        => 'participant_role',
              'participant_listing_id' => 'participant_listing',
          ],
          'Membership'             => [
              'membership_type_id' => 'civicrm_membership_type__name',
              'status_id'          => 'civicrm_membership_status__label',
          ],
          'ContributionSoft'       => [
              'soft_credit_type_id' => 'soft_credit_type',
          ],
          'OptionValue'            => [
              'option_group_id' => 'IGNORE',
          ],
          'UFField'                => [
              'uf_group_id' => 'IGNORE',
          ],
      ];
    }
    return $option_group_mapping;
  }
}
