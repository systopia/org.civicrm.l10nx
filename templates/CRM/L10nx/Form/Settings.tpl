{*-------------------------------------------------------+
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
+-------------------------------------------------------*}

<div class="l10nx-settings">
  <div class="crm-section">
    <div class="label">{$form.data_language.label}</div>
    <div class="content">{$form.data_language.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.translate_data.label}</div>
    <div class="content">{$form.translate_data.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.translate_options.label}</div>
    <div class="content">{$form.translate_options.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section l10nx-option-groups">
    <div class="label">{$form.option_groups.label}</div>
    <div class="content">{$form.option_groups.html}</div>
    <div class="clear"></div>
  </div>
</div>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="application/javascript">
function l10nxShowOptionGroups() {
  let active = cj("#translate_options").prop("checked");
  if (active) {
    cj("div.l10nx-option-groups").show(100);
  } else {
    cj("div.l10nx-option-groups").hide(100);
  }
}
l10nxShowOptionGroups();
cj("#translate_options").change(l10nxShowOptionGroups);

</script>
{/literal}