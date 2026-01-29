<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Controller;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 */
final class UserWithoutAutologinControllerTest extends FunctionalTestCase
{
    private const FIXTURES_PATH = __DIR__ . '/Fixtures/UserWithoutAutologinController';
    private const ASSERTIONS_PATH = __DIR__ . '/Assertions/UserWithoutAutologinController';

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    protected array $coreExtensionsToLoad = [
        'typo3/cms-fluid-styled-content',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/onetimeaccount/Tests/Functional/Controller/Fixtures/Sites/' => 'typo3conf/sites',
    ];

    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => false,
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }
}
