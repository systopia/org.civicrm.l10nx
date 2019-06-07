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

/*
* Settings metadata file
*/

return [
  'allow_mandate_modification' => [
      'group_name'      => 'l10nx',
      'group'           => 'org.civicrm.l10nx',
      'name'            => 'l10nx_settings',
      'type'            => 'Array',
      'default'         => '',
      'add'             => '5.0',
      'title'           => 'l10nx settings',
      'is_domain'       => 1,
      'is_contact'      => 0,
      'description'     => 'settings for the behaviour of the l10nx translation module',
      'help_text'       => 'settings for the behaviour of the l10nx translation module',
  ]
];