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

class CRM_L10nx_Hook
{
  /**
   * This hook allows another extension to provide it's own .mo file
   *   for the given domain
   *
   * If no domain is given, this will be passed on to CiviCRM's internal ts system
   *
   * @param array $mo_file_paths
   *   List of paths to the mo files to be used
   * @param string $locale
   *   locale for translating.
   * @param string $context
   *   The translation context, either 'ts' or 'dts'
   * @param string $domain
   *   The translation domain
   */
  public static function custom_mo(&$mo_file_paths, $locale, $context, $domain) {
    \Civi::dispatcher()->dispatch('civi.l10n.custom_mo', \Civi\Core\Event\GenericHookEvent::create([
        'mo_file_path' => $mo_file_paths,
        'locale'       => $locale,
        'context'      => &$context,
        'domain'       => $domain
    ]));
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
   *   The parameters passed to dts
   */
  public static function dts_post($locale, $original_text, &$translated_text, $params) {
    \Civi::dispatcher()->dispatch('civi.l10n.dts_post', \Civi\Core\Event\GenericHookEvent::create([
        'locale'          => $locale,
        'original_text'   => $original_text,
        'translated_text' => &$translated_text,
        'params'          => $params
    ]));
  }

  /**
   * This hook allows other extension to plug into the ts function directly
   *
   * @param string $locale
   *   locale for translating.
   * @param string $original_text
   *   The text to be translated
   * @param string $translated_text
   *   The text with the current translation
   * @param array $params
   *   The parameters passed to ts
   */
  public static function ts_post($locale, $original_text, &$translated_text, $params) {
    \Civi::dispatcher()->dispatch('civi.l10n.ts_post', \Civi\Core\Event\GenericHookEvent::create([
        'locale'          => $locale,
        'original_text'   => $original_text,
        'translated_text' => &$translated_text,
        'params'          => $params
    ]));
    //CRM_Core_Error::debug_log_message("'{$original_text}' translated as '{$translated_text}' ({$locale})");
  }

}