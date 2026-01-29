<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Controller;

use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 */
final class UserWithoutAutologinControllerTest extends FunctionalTestCase
{
    private const FIXTURES_PATH = __DIR__ . '/Fixtures/UserWithoutAutologinController';
    private const PAGE_UID = 1;

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

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Sites/SiteStructure.csv');
        $this->importCSVDataSet(self::FIXTURES_PATH . '/OnetimeAccountContentElement.csv');

        $this->setUpFrontendRootPage(1, [
            'constants' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript',
                'EXT:onetimeaccount/Configuration/TypoScript/constants.typoscript',
            ],
            'setup' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                'EXT:onetimeaccount/Configuration/TypoScript/setup.typoscript',
                'EXT:onetimeaccount/Tests/Functional/Controller/Fixtures/TypoScript/Setup/Rendering.typoscript',
            ],
        ]);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function nonDateFieldKeysDataProvider(): array
    {
        return [
            'company' => ['company'],
            'department' => ['department'],
            'gender' => ['gender'],
            'name' => ['name'],
            'firstName' => ['firstName'],
            'lastName' => ['lastName'],
            'title' => ['title'],
            'address' => ['address'],
            'zip' => ['zip'],
            'city' => ['city'],
            'zone' => ['zone'],
            'country' => ['country'],
            'email' => ['email'],
            'telephone' => ['telephone'],
            'www' => ['www'],
            'status' => ['status'],
            'vatIn' => ['vatIn'],
            'comments' => ['comments'],
            'privacy' => ['privacy'],
            'termsAcknowledged' => ['termsAcknowledged'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     *
     * @dataProvider nonDateFieldKeysDataProvider
     */
    public function newActionHasAllNonDateFields(string $key): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_onetimeaccount_withoutautologin[user][' . $key . ']"', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function dateFieldKeysDataProvider(): array
    {
        return [
            'dateOfBirth' => ['dateOfBirth'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     *
     * @dataProvider dateFieldKeysDataProvider
     */
    public function newActionHasAllDateFields(string $key): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_onetimeaccount_withoutautologin[user][' . $key . '][date]"', $html);
        self::assertStringContainsString(
            'name="tx_onetimeaccount_withoutautologin[user][' . $key . '][dateFormat]"',
            $html
        );
    }

    /**
     * @test
     */
    public function newActionHasCaptcha(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_onetimeaccount_withoutautologin[captcha][givenAnswer]"', $html);
    }
}
