<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;

defined('TYPO3') or die('Access denied.');

(static function (): void {
    // This makes the plugin available for front-end rendering.
    ExtensionUtility::configurePlugin(
    // extension name, matching the PHP namespaces (but without the vendor)
        'Onetimeaccount',
        // arbitrary, but unique plugin name (not visible in the BE)
        'WithoutAutologin',
        // all actions
        [
            UserWithoutAutologinController::class => 'new, create',
        ],
        // non-cacheable actions
        [
            UserWithoutAutologinController::class => 'new, create',
        ],
    );
})();
