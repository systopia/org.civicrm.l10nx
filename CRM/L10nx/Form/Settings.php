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

use CRM_L10nx_ExtensionUtil as E;

/**
 * Settings page for l10nx configuration
 */
class CRM_L10nx_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm() {

    // add form elements
    $this->add(
      'checkbox',
      'translate_data',
      E::ts('Translate DB Data (experimental)')
    );

    $this->add(
        'checkbox',
        'translate_options',
        E::ts('Translate Options (experimental)')
    );

    $this->add(
        'select',
        'option_groups',
        E::ts('Option Groups'),
        $this->getOptionGroups(),
        FALSE,
        ['class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => E::ts("all option groups")]
    );

    $this->add(
        'select',
        'data_language',
        E::ts('Data Language'),
        CRM_Core_I18n::languages(),
        TRUE
    );

    // set defaults
    $config = CRM_L10nx_Configuration::get();
    $this->setDefaults([
        'translate_data'    => $config->translateData(),
        'translate_options' => $config->translateOptions(),
        'option_groups'     => $config->getTranslatedOptionGroups(),
        'data_language'     => $config->getDataLocale(),
    ]);

    // add save button
    $this->addButtons([
        [
            'type'      => 'submit',
            'name'      => E::ts('Save'),
            'isDefault' => TRUE,
        ],
    ]);

    parent::buildQuickForm();
  }
  
  public function postProcess() {
    $values = $this->exportValues();

    $config = CRM_L10nx_Configuration::get();
    $config->setAllSettings([
        'translate_data'    => CRM_Utils_Array::value('translate_data',    $values, FALSE),
        'translate_options' => CRM_Utils_Array::value('translate_options', $values, FALSE),
        'option_groups'     => CRM_Utils_Array::value('option_groups',     $values, []),
        'data_language'     => CRM_Utils_Array::value('data_language',     $values, 'en_US'),
    ]);


    CRM_Core_Session::setStatus(E::ts('Settings updated'));
    parent::postProcess();
  }

  /**
   * Get a list of OptionGroups
   *
   * @return array option group list
   */
  protected function getOptionGroups() {
    $option_groups = [];
    $query = civicrm_api3('OptionGroup', 'get', [
        'option.limit' => 0,
        'return'       => 'id,name,title'
    ]);
    foreach ($query['values'] as $option_group) {
      $option_groups[$option_group['name']] = "{$option_group['title']} [{$option_group['id']}]";
    }
    return $option_groups;
  }
}
