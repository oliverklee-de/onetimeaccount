<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

// This makes the plugin selectable in the BE.
ExtensionUtility::registerPlugin(
    // extension name, matching the PHP namespaces (but without the vendor)
    'Onetimeaccount',
    // arbitrary, but unique plugin name (not visible in the BE)
    'WithoutAutologin',
    // plugin title, as visible in the drop-down in the BE
    'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang.xlf:plugin.withoutAutologin',
    // the icon visible in the drop-down in the BE
    'EXT:onetimeaccount/Resources/Public/Icons/Extension.svg',
);

// This removes the default controls from the plugin.
// @phpstan-ignore-next-line We know that this array key exists and is an array.
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['onetimeaccount_withoutautologin']
    = 'recursive,pages';
// These two commands add the flexform configuration for the plugin.
// @phpstan-ignore-next-line We know that this array key exists and is an array.
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['onetimeaccount_withoutautologin'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'onetimeaccount_withoutautologin',
    'FILE:EXT:onetimeaccount/Configuration/FlexForms/Plugin.xml',
);
