# CiviCRM Extended Localisation Extension

This extension adds extra hooks to CiviCRM's translation (l10n/i18n) system, 
so other extensions can influence how, when, and where 
string translation takes place - for each individual translation or a whole set.

Namely there are two new "hooks" (implemented exclusively as Symfony events for performance reasons):
* ``custom_mo(&$mo_file_paths, $locale, $context, $domain)`` allows you to inject custom .mo 
files into the translation, depending on the translation context.
* ``ts_post($locale, $original_text, &$translated_text, $params)`` allows you to detect, profile and 
change a prepared translation just before delivery 

This extension simply provides new infrastructure to other extensions, and doesn't have any UI or features itself. 
If you want to know how these new hooks can be used, have a look at the following extensions:
* SYSTOPIA's [Custom MO extension](https://github.com/systopia/de.systopia.l10nmo) allows you to define
custom .MO files that should be evaluated *before* the regular translation kicks in. 
* SYSTOPIA's [Profiler extension](https://github.com/systopia/de.systopia.l10nprofiler) allows you to
live-capture ongoing translations and export those as .PO and .POT files, so you can easily create or amend
the existing translation.

## Development Stages

The development of these new features will proceed in three stages:

### Stage 1: Patched custom ts function

In the first stage, we will only use CiviCRM's existing feature to provide a custom ``ts()`` function,
to feed into the new hooks/events. For that, the extension adjusts the configuration during installation.
Unfortunately, the system currently doesn't work with function provided by extension, so you would also 
have to apply the patch shipped with the extension.

This is the current stage.

### Stage 2: Refactored Core I18n

Tim Otten already started working on a complete refactoring of the I18n core component, which is responsible, among other things,
for the string translation. This refactoring will not only make this subsystem faster and more flexible,
but also allows a seamless integration of this extension.

As soon as this refactoring is part of the core, I will adjust this extension to use the new integration.

### Stage 3: User Data Translation

The last stage (for the foreseeable future) is going to be a real game changer. We will extend the
l10n system to *user data*, i.e. labels, option values, etc. So far, multi-language user data was only
possible with the "Multiple Languages Support", which extends the DB scheme for each additional language. 
The implementation concept is, no doubt, genius - but it doesn't scale well. You probably don't need your
backend to cater for 50 different languages, but why shouldn't your public forms offer that? This third stage 
can get you there, and with minimal effort.

To achieve the translation of user data, we will introduce the ``dts()`` function, that is an exact
replica of the ``ts()`` function, but used for user data. This new function will then, of course, also use the 
exciting new infrastructure above.  

## Installation

This extension can be installed as any other CiviCRM extension. 
In addition, you currently have to apply the patch shipped with this extension to you CiviCRM 
module. This step will be obsolete with development stage 2, see above.  

Thanks to Tim Otten and Aidan Saunders for the fun discussions as the 2018 Bamford sprint. 

The extension is licensed under [AGPL-3.0](LICENSE.txt).
