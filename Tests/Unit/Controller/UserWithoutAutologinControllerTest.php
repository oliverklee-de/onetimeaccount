<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;
use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures\TestingQueryResult;
use OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures\XclassFrontendUser;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument as ExtbaseArgument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 */
final class UserWithoutAutologinControllerTest extends UnitTestCase
{
    private const SITE_URL = 'https://www.example.com';
    private const NOW = '2004-02-12T15:19:21+00:00';

    protected bool $resetSingletonInstances = true;

    /**
     * @var UserWithoutAutologinController&MockObject&AccessibleObjectInterface
     */
    private UserWithoutAutologinController $subject;

    /**
     * @var TemplateView&MockObject
     */
    private TemplateView $viewMock;

    /**
     * @var FrontendUserRepository&MockObject
     */
    private FrontendUserRepository $userRepositoryMock;

    /**
     * @var FrontendUserGroupRepository&MockObject
     */
    private FrontendUserGroupRepository $userGroupRepositoryMock;

    /**
     * @var CredentialsGenerator&MockObject
     */
    private CredentialsGenerator $credentialsGeneratorMock;

    /**
     * @var UserValidator&MockObject
     */
    private UserValidator $userValidatorMock;

    /**
     * @var CaptchaFactory&MockObject
     */
    private CaptchaFactory $captchaFactoryMock;

    /**
     * @var CaptchaValidator&MockObject
     */
    private CaptchaValidator $captchaValidatorMock;

    private Arguments $controllerArguments;

    /**
     * @var FrontendUserAuthentication&MockObject
     */
    private FrontendUserAuthentication $userMock;

    private Request $requestMock;

    /**
     * @var array<string, string|null>
     */
    private array $getParameters = [];

    /**
     * @var array<string, string|null>
     */
    private array $postParameters = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        $this->userRepositoryMock = $this->createMock(FrontendUserRepository::class);
        $this->userGroupRepositoryMock = $this->createMock(FrontendUserGroupRepository::class);
        $this->credentialsGeneratorMock = $this->createMock(CredentialsGenerator::class);
        $this->userValidatorMock = $this->createMock(UserValidator::class);
        $this->captchaValidatorMock = $this->createMock(CaptchaValidator::class);
        $this->captchaFactoryMock = $this->createMock(CaptchaFactory::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getPropertyFromAspect')->with('date', 'iso')->willReturn(self::NOW);

        // We need to create an accessible mock in order to be able to set the protected `view`.
        $this->subject = $this->getAccessibleMock(
            UserWithoutAutologinController::class,
            ['redirect', 'redirectToUri', 'htmlResponse'],
            [
                $this->userRepositoryMock,
                $this->userGroupRepositoryMock,
                $this->credentialsGeneratorMock,
                $this->userValidatorMock,
                $this->captchaValidatorMock,
                $this->captchaFactoryMock,
                $contextMock,
            ],
        );

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->controllerArguments = new Arguments();
        $this->subject->_set('arguments', $this->controllerArguments);

        $this->userMock = $this->createMock(FrontendUserAuthentication::class);
        $this->requestMock = $this->createMock(Request::class);
        $this->requestMock->method('getAttribute')->with('frontend.user')->willReturn($this->userMock);
        $this->requestMock->method('getParsedBody')->willReturnCallback(fn(): array => $this->postParameters);
        $this->requestMock->method('getQueryParams')->willReturnCallback(fn(): array => $this->getParameters);
        $this->subject->_set('request', $this->requestMock);

        $responseStub = $this->createStub(HtmlResponse::class);
        $this->subject->method('htmlResponse')->willReturn($responseStub);
    }

    protected function tearDown(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class]);
        $this->resetRequestData();
        parent::tearDown();
    }

    private function setDummyRequestData(): void
    {
        $this->resetRequestData();
        GeneralUtility::setIndpEnv('TYPO3_SITE_URL', self::SITE_URL);
        GeneralUtility::setIndpEnv('TYPO3_REQUEST_HOST', 'https://www.example.com');
    }

    private function resetRequestData(): void
    {
        GeneralUtility::flushInternalRuntimeCaches();
    }

    /**
     * @param non-empty-string $uri
     */
    private function mockRedirectToUri(string $uri): void
    {
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $this->subject
                ->expects(self::once())->method('redirectToUri')
                ->with($uri)
                ->willThrowException(new StopActionException('redirectToUri', 1476045828));
            $this->expectException(StopActionException::class);
        } else {
            $redirectResponse = $this->createStub(RedirectResponse::class);
            $this->subject
                ->expects(self::once())->method('redirectToUri')->with($uri)
                ->willReturn($redirectResponse);
        }
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }

    /**
     * @test
     */
    public function newActionReturnsHtmlResponse(): void
    {
        $result = $this->subject->newAction();

        self::assertInstanceOf(HtmlResponse::class, $result);
    }

    /**
     * @test
     */
    public function newActionWithUserPassesUserToView(): void
    {
        $user = new FrontendUser();

        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', $user],
            ['selectedUserGroup', self::anything()],
        );

        $this->subject->newAction($user);
    }

    /**
     * @test
     */
    public function newActionWithoutUserPassesVirginUserToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::isInstanceOf(FrontendUser::class)],
            ['selectedUserGroup', self::anything()],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithNullUserPassesVirginUserToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::isInstanceOf(FrontendUser::class)],
            ['selectedUserGroup', self::anything()],
        );

        $this->subject->newAction(null);
    }

    /**
     * @test
     */
    public function newActionWithUserGroupPassesProvidedUserGroupUidToView(): void
    {
        $userGroupUid = 5;
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', $userGroupUid],
        );

        $this->subject->newAction(null, $userGroupUid);
    }

    /**
     * @test
     */
    public function newActionWithNullUserGroupPassesNullUserGroupToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', null],
        );

        $this->subject->newAction(null, null);
    }

    /**
     * @test
     */
    public function newActionWithMissingUserGroupPassesNullUserGroupToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', null],
        );

        $this->subject->newAction(null, null);
    }

    /**
     * @test
     */
    public function newActionWithoutUserCanPassVirginSubclassedUserToView(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class] = ['className' => XclassFrontendUser::class];

        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::isInstanceOf(XclassFrontendUser::class)],
            ['selectedUserGroup', self::anything()],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionPassesConfiguredUserGroupsToView(): void
    {
        $groupUid1 = 1;
        $groupUid2 = 2;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $userGroups = $this->createStub(QueryResultInterface::class);
        $this->userGroupRepositoryMock->method('findByUids')->with([$groupUid1, $groupUid2])->willReturn($userGroups);

        $this->viewMock->expects(self::atLeast(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['userGroups', $userGroups],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithPassesNewCaptchaToView(): void
    {
        $this->subject->_set('settings', ['captcha' => '1']);
        $captcha = new Captcha();
        $this->captchaFactoryMock->method('generateChallenge')->willReturn($captcha);

        $this->viewMock->expects(self::atLeast(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['captcha', $captcha],
        );

        $this->subject->newAction(null);
    }

    /**
     * @return array<string, array{0: ''|null}>
     */
    public function emptyParameterDataProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
        ];
    }

    /**
     * @test
     *
     * @dataProvider emptyParameterDataProvider
     */
    public function newActionWithEmptyRedirectUrlInGetNotPassesRedirectUrlToView(?string $redirectUrl): void
    {
        $this->getParameters['redirect_url'] = $redirectUrl;

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     *
     * @dataProvider emptyParameterDataProvider
     */
    public function newActionWithEmptyRedirectUrlInPostNotPassesRedirectUrlToView(?string $redirectUrl): void
    {
        $this->postParameters['redirect_url'] = $redirectUrl;

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithRedirectUrlInSiteInGetPassesRedirectUrlToView(): void
    {
        $redirectUrl = 'https://example.com/';
        $this->getParameters['redirect_url'] = $redirectUrl;

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['redirectUrl', $redirectUrl],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithRedirectUrlInSiteInPostPassesRedirectUrlToView(): void
    {
        $redirectUrl = 'https://example.com/';
        $this->postParameters['redirect_url'] = $redirectUrl;

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['redirectUrl', $redirectUrl],
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function initializeCreateActionWithUserArgumentSetsUserValidatorWithSettings(): void
    {
        $user = new FrontendUser();
        $userArgument = new ExtbaseArgument('user', FrontendUser::class);
        $userArgument->setValue($user);
        $this->controllerArguments->addArgument($userArgument);
        $propertyValidator = new GenericObjectValidator();
        $userArgument->setValidator($propertyValidator);

        $settings = ['fieldsToShow' => 'name,email', 'requiredFields' => 'email'];
        $this->subject->_set('settings', $settings);
        $this->userValidatorMock->expects(self::once())->method('setSettings')->with($settings);

        $this->subject->initializeCreateAction();

        $actualValidator = $userArgument->getValidator();
        self::assertInstanceOf(ConjunctionValidator::class, $actualValidator);
        $validators = $actualValidator->getValidators();
        self::assertCount(2, $validators);
        self::assertContains($propertyValidator, $validators);
        self::assertContains($this->userValidatorMock, $validators);
    }

    /**
     * @test
     */
    public function initializeCreateActionWithoutUserArgumentNotTouchesUserValidator(): void
    {
        $this->subject->_set('settings', []);

        $this->userValidatorMock->expects(self::never())->method('setSettings');

        $this->subject->initializeCreateAction();
    }

    /**
     * @test
     */
    public function initializeCreateActionWithCaptchaArgumentSetsCaptchaValidatorWithSettings(): void
    {
        $captcha = new Captcha();
        $captchaArgument = new ExtbaseArgument('captcha', Captcha::class);
        $captchaArgument->setValue($captcha);
        $this->controllerArguments->addArgument($captchaArgument);
        $propertyValidator = new GenericObjectValidator();
        $captchaArgument->setValidator($propertyValidator);

        $settings = ['captcha' => '1'];
        $this->subject->_set('settings', $settings);
        $this->captchaValidatorMock->expects(self::once())->method('setSettings')->with($settings);

        $this->subject->initializeCreateAction();

        $actualValidator = $captchaArgument->getValidator();
        self::assertInstanceOf(ConjunctionValidator::class, $actualValidator);
        $validators = $actualValidator->getValidators();
        self::assertCount(2, $validators);
        self::assertContains($propertyValidator, $validators);
        self::assertContains($this->captchaValidatorMock, $validators);
    }

    /**
     * @test
     */
    public function initializeCreateActionWithoutCaptchaArgumentNotTouchesCaptchaValidator(): void
    {
        $this->subject->_set('settings', []);

        $this->captchaValidatorMock->expects(self::never())->method('setSettings');

        $this->subject->initializeCreateAction();
    }

    /**
     * @test
     */
    public function createActionWithUserReturnsHtmlResponse(): void
    {
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $result = $this->subject->createAction(new FrontendUser());

        self::assertInstanceOf(HtmlResponse::class, $result);
    }

    /**
     * @test
     */
    public function createActionWithoutUserReturnsHtmlResponse(): void
    {
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $result = $this->subject->createAction();

        self::assertInstanceOf(HtmlResponse::class, $result);
    }

    /**
     * @test
     */
    public function createActionSetsUserPidFromSettings(): void
    {
        $systemFolderUid = 42;
        $this->subject->_set('settings', ['systemFolderForNewUsers' => (string)$systemFolderUid]);
        $user = new FrontendUser();
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($systemFolderUid, $user->getPid());
    }

    /**
     * @test
     */
    public function createActionWithAllowedExistentGroupUidSetsGivenGroup(): void
    {
        $groupUid = 4;
        $group = new FrontendUserGroup();
        $this->subject->_set('settings', ['groupsForNewUsers' => (string)$groupUid]);
        $this->userGroupRepositoryMock->method('findByUid')->with($groupUid)->willReturn($group);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, $groupUid);

        self::assertCount(1, $user->getUserGroup());
        self::assertContains($group, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithAllowedInexistentGroupUidSetsOtherGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->userGroupRepositoryMock->method('findByUid')->with($groupUid1)->willReturn(null);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock
            ->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, $groupUid1);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(1, $user->getUserGroup());
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNotAllowedGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock
            ->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, 123);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNullGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock
            ->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, null);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNullGroupUidAndNoConfiguredGroupsSetsNoGroups(): void
    {
        $this->subject->_set('settings', ['groupsForNewUsers' => '']);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, null);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(0, $userGroupsFromUser);
    }

    /**
     * @test
     */
    public function createActionWithZeroGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock
            ->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, 0);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNegativeGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock
            ->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, -1);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionGeneratesUsername(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock->expects(self::once())->method('generateAndSetUsernameForUser')->with($user);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionGeneratesPassword(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock
            ->expects(self::once())->method('generateAndSetPasswordForUser')
            ->with($user)->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionSetsLastLoginToNow(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $this->subject->createAction($user);

        $lastLoginDate = $user->getLastLogin();
        self::assertInstanceOf(\DateTime::class, $lastLoginDate);
        self::assertEquals(new \DateTime(self::NOW), $lastLoginDate);
    }

    /**
     * @test
     */
    public function createActionForTermsNotAcknowledgedKeepsTermsDateOfAcceptanceUnchanged(): void
    {
        $user = new FrontendUser();
        $user->setTermsAcknowledged(false);
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $this->subject->createAction($user);

        self::assertNull($user->getTermsDateOfAcceptance());
    }

    /**
     * @test
     */
    public function createActionForTermsAcknowledgedTermsDateOfAcceptanceToNow(): void
    {
        $user = new FrontendUser();
        $user->setTermsAcknowledged(true);
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $this->subject->createAction($user);

        $termsAcceptedDate = $user->getTermsDateOfAcceptance();
        self::assertInstanceOf(\DateTime::class, $termsAcceptedDate);
        self::assertEquals(new \DateTime(self::NOW), $termsAcceptedDate);
    }

    /**
     * @test
     */
    public function createActionForPrivacyNotSetKeepsPrivacyDateOfAcceptanceUnchanged(): void
    {
        $user = new FrontendUser();
        $user->setPrivacy(false);
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $this->subject->createAction($user);

        self::assertNull($user->getPrivacyDateOfAcceptance());
    }

    /**
     * @test
     */
    public function createActionForPrivacySetPrivacyDateOfAcceptanceToNow(): void
    {
        $user = new FrontendUser();
        $user->setPrivacy(true);
        $this->credentialsGeneratorMock->method('generateAndSetPasswordForUser')->willReturn('');

        $this->subject->createAction($user);

        $privacyAcceptedDate = $user->getPrivacyDateOfAcceptance();
        self::assertInstanceOf(\DateTime::class, $privacyAcceptedDate);
        self::assertEquals(new \DateTime(self::NOW), $privacyAcceptedDate);
    }

    /**
     * @test
     */
    public function createActionForUserWithFullNameAndFirstNameAndLastNameKeepsFullNameUnchanged(): void
    {
        $fullName = 'Max Performance';
        $user = new FrontendUser();
        $user->setName($fullName);
        $user->setFirstName('Mini');
        $user->setLastName('Slowness');
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($fullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithFullNameAndFirstNameAndNoLastNameKeepsFullNameUnchanged(): void
    {
        $fullName = 'Max Performance';
        $user = new FrontendUser();
        $user->setName($fullName);
        $user->setFirstName('Mini');
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($fullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithFullNameAndLastNameAndNoFirstNameKeepsFullNameUnchanged(): void
    {
        $fullName = 'Max Performance';
        $user = new FrontendUser();
        $user->setName($fullName);
        $user->setLastName('Slowness');
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($fullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithoutFullNameAndFirstAndLastNameBuildsFullNameFromFirstAndLastName(): void
    {
        $user = new FrontendUser();
        $firstName = 'Mini';
        $user->setFirstName($firstName);
        $lastName = 'Slowness';
        $user->setLastName($lastName);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        $expectedFullName = $firstName . ' ' . $lastName;
        self::assertSame($expectedFullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithFirstNameOnlySetsFullNameToFirstName(): void
    {
        $user = new FrontendUser();
        $firstName = 'Mini';
        $user->setFirstName($firstName);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($firstName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithLastNameOnlySetsFullNameToLastName(): void
    {
        $user = new FrontendUser();
        $lastName = 'Slowness';
        $user->setLastName($lastName);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($lastName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithNoNameAtAllKeepsEmptyFullyName(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);

        self::assertSame('', $user->getName());
    }

    /**
     * @test
     */
    public function createActionWithUserAddsProvidedUserToRepository(): void
    {
        $user = new FrontendUser();
        $this->userRepositoryMock->expects(self::once())->method('add')->with($user);
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithUserPersistsEverything(): void
    {
        $user = new FrontendUser();
        $this->userRepositoryMock->expects(self::once())->method('persistAll');
        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithNullUserNotAddsAnythingToRepository(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('add')->with(self::anything());

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotAddsAnythingToRepository(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('add')->with(self::anything());

        $this->subject->createAction();
    }

    /**
     * @test
     */
    public function createActionWithNullUserNotPersistsAnything(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('persistAll');

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotPersistsAnything(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('persistAll');

        $this->subject->createAction();
    }

    /**
     * @test
     *
     * @dataProvider emptyParameterDataProvider
     */
    public function createActionWithUserWithEmptyRedirectUrlInPostNotRedirects(?string $redirectUrl): void
    {
        $this->postParameters['redirect_url'] = $redirectUrl;

        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        $this->subject->expects(self::never())->method('redirectToUri');

        $this->subject->createAction(new FrontendUser());
    }

    /**
     * @test
     */
    public function createActionWithUserWithLocalRedirectUrlInPostRedirectsToRedirectUrl(): void
    {
        $redirectUrl = self::SITE_URL;
        $this->postParameters['redirect_url'] = $redirectUrl;

        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        $this->mockRedirectToUri($redirectUrl);

        if ((new Typo3Version())->getMajorVersion() < 12) {
            $this->subject->createAction(new FrontendUser());
        } else {
            $result = $this->subject->createAction(new FrontendUser());
            self::assertInstanceOf(RedirectResponse::class, $result);
        }
    }

    /**
     * @test
     */
    public function createActionWithUserWithExternalRedirectUrlNotRedirects(): void
    {
        $this->postParameters['redirect_url'] = 'https://www.oliverklee.de/';

        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');
        $this->subject->expects(self::never())->method('redirectToUri');

        $this->subject->createAction(new FrontendUser());
    }

    /**
     * @test
     */
    public function createActionWithoutUserWithRedirectUrlInSiteNotRedirects(): void
    {
        $this->postParameters['redirect_url'] = self::SITE_URL;

        $this->subject->expects(self::never())->method('redirectToUri');

        $this->subject->createAction();
    }

    /**
     * @test
     */
    public function createActionStoresUidOfNewUserInSession(): void
    {
        $userUid = 42;
        $user = new FrontendUser();
        $user->_setProperty('uid', $userUid);

        $this->credentialsGeneratorMock
            ->method('generateAndSetPasswordForUser')
            ->with(self::anything())
            ->willReturn('');

        $this->userMock
            ->expects(self::once())
            ->method('setAndSaveSessionData')
            ->with('onetimeaccountUserUid', $userUid);

        $this->subject->createAction($user);
    }
}
