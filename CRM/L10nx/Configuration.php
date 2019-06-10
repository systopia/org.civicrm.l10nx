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
class CRM_L10nx_Configuration
{

  protected $settings = NULL;

  /**
   * Get the current configuration
   * @return CRM_L10nx_Configuration configuration
   */
  public static function get()
  {
    static $singleton = NULL;
    if ($singleton === NULL) {
      $singleton = new CRM_L10nx_Configuration();
    }
    return $singleton;
  }

  protected function __construct()
  {
    $this->settings = CRM_Core_BAO_Setting::getItem('l10nx', 'l10nx_settings');
    if (empty($this->settings)) {
      $this->settings = [];
    }
  }

  /**
   * Get all settings
   * @return array all settings
   */
  public function getAllSettings()
  {
    return $this->settings;
  }

  /**
   * Set/override all settings
   *
   * @param $settings array the new settings
   *
   */
  public function setAllSettings($settings)
  {
    $this->settings = $settings;
    CRM_Core_BAO_Setting::setItem($settings, 'l10nx', 'l10nx_settings');
  }

  /**
   * Should data from the DB (except option groups) be translated?
   *  These are strings in the ts domain 'civicrm-data'.
   *
   * @return boolean feature enabled?
   */
  public function translateData()
  {
    return (boolean)CRM_Utils_Array::value('translate_data', $this->settings, FALSE);
  }

  /**
   * Should option group values be translated?
   *  These are strings in the ts domain 'civicrm-option'.
   *
   * @return boolean feature enabled?
   */
  public function translateOptions()
  {
    return (boolean)CRM_Utils_Array::value('translate_options', $this->settings, FALSE);
  }

  /**
   * Should the option group translation use gettext context?
   * Disadvantage: you have to specify translations for each option value, and you can't use the CiviCRM default translations
   * Advantage: you can have different translations for the same label, depending on the option group
   *
   * @return bool
   */
  public function useTranslationContextForOptions()
  {
    // TODO: implement?
    return FALSE;
  }

  /**
   * Should the data translation use gettext context?
   * Disadvantage: you have to specify translations for each option value, and you can't use the CiviCRM default translations
   * Advantage: you can have different translations for the same label, depending on the option group
   *
   * @return bool
   */
  public function useTranslationContextForData()
  {
    // TODO: implement?
    return FALSE;
  }

  /**
   * Get the list of translated option groups.
   * Warning: does not check if option group translation is active..
   *
   * @return array list of option group names
   */
  public function getTranslatedOptionGroups()
  {
    return CRM_Utils_Array::value('option_groups', $this->settings, []);
  }

  /**
   * Should the given option group, identified by name, be translated?
   *
   * @param $option_group_name string option group name
   * @return boolean TRUE, if it should be translated
   */
  public function translateOptionGroup($option_group_name)
  {
    $translated_groups = $this->getTranslatedOptionGroups();
    return empty($translated_groups) || in_array($option_group_name, $translated_groups);
  }

  /**
   * Return the locale (language) in which the data in the database is encoded. This is relevant, if
   *  translateData or translateOptions (see above) is active
   *
   * @return string locale
   */
  public function getDataLocale()
  {
    return CRM_Utils_Array::value('data_language', $this->settings, 'en_US');
  }

  /**
   * Get the list of translatable columns for the given civicrm table
   *
   * @param $table_name string civicrm table name
   * @return array list if columns to be translated
   */
  public static function getTranslatableColumns($table_name)
  {
    static $translatable_columns = NULL;
    if ($translatable_columns === NULL) {
      // init with the translatable columns from the multi-language schema
      $translatable_columns = CRM_Core_I18n_SchemaStructure::columns();
      // add our own
      $translatable_columns['civicrm_financial_type'] = ['name'];
      unset($translatable_columns['civicrm_option_group']);
    }
    return CRM_Utils_Array::value($table_name, $translatable_columns, []);
  }

  /**
   * Get a list of known option groups by entity/field
   *
   * Remark: for performance reasons, this is hardcoded rather then dynamically built
   */
  public static function getOptionGroupMapping()
  {
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
              'gender_id'                      => 'gender',
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
          'Activity'               => [
              'activity_type_id' => 'activity_type',
              'status_id'        => 'activity_status',
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

  /**
   * Get a list option groups (plus fields) available for translation
   */
  public static function getGroupList()
  {
    $option_groups = [];
    $mapping       = self::getOptionGroupMapping();
    foreach ($mapping as $entity => $field2groups) {
      foreach ($field2groups as $field => $group) {
        if ($group != 'IGNORE') {
          $option_groups[$group] = 1;
        }
      }
    }
    return array_keys($option_groups);
  }
}