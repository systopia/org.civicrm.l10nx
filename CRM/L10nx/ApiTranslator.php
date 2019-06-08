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

class CRM_L10nx_ApiTranslator implements API_Wrapper {

  /**
   * This wrapper will translate the given fields
   *
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    // TODO: performance optimization
    if ($apiRequest['action'] == 'get') {
      // get translatable columns from the multi-lang schema
      $table_name = $this->getTableName($apiRequest['entity']);
      $translatable_columns = CRM_L10nx_Configuration::getTranslatableColumns($table_name);

      // check if this is relevant
      if ($translatable_columns) {
        $locale = CRM_Core_I18n::getLocale();
        $dts_params = [
            'table_name' => $table_name,
            'source'     => 'api3'];
        // find and translate all the values
        foreach ($result['values'] as &$data) {
          foreach ($translatable_columns as $column) {
            if (isset($data[$column])) {
              $dts_params['column_name'] = $column;
              CRM_L10nx_Hook::dts_post($locale, $data[$column], $data[$column], $dts_params);
            }
          }
        }
      }
    }

    return $result;
  }

  /**
   * Implementing API_Wrapper interface
   */
  public function fromApiInput($apiRequest) {
    // nothing to do here
    return $apiRequest;
  }



  /// HELPERS ///

  /**
   * Convert the entity name into the respective civicrm table name
   * @param $entity string entity name
   * @return string civicrm table name
   */
  protected function getTableName($entity) {
    $table_name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $entity);
    $table_name = strtolower("civicrm_{$table_name}");
    return $table_name;
  }


  /**
   * This hook allows other extension to plug into the dts function directly
   *
   * Remark: we intentionally created _two_ events to enable people to efficiently
   *   subscribe to only one of the two.
   *
   * @param string $locale
   *   locale for translating.
   * @param string $original_text
   *   The text to be translated
   * @param string $translated_text
   *   The text with the current translation
   * @param array $params
   *   Parameters passed to dts_post. Should include
   *    'table_name' and 'column_name' OR
   *    'entity' and 'attribute'
   *   to identify the context of the value
   */
  public static function dts_post($locale, $original_text, &$translated_text, $params) {
    \Civi::dispatcher()->dispatch('civi.l10n.dts_post', \Civi\Core\Event\GenericHookEvent::create([
        'locale'          => $locale,
        'original_text'   => $original_text,
        'translated_text' => &$translated_text,
        'params'          => $params
    ]));
  }


}