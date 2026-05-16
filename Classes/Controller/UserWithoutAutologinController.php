<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Plugin for creating a front-end user and storing its UID in a FE session. This plugin has no autologin, though.
 */
class UserWithoutAutologinController extends ActionController
{
    public function __construct(
        private readonly FrontendUserRepository $userRepository,
        private readonly FrontendUserGroupRepository $userGroupRepository,
        private readonly CredentialsGenerator $credentialsGenerator,
        private readonly UserValidator $userValidator,
        private readonly CaptchaValidator $captchaValidator,
        private readonly CaptchaFactory $captchaFactory,
        private readonly Context $context,
    ) {}

    /**
     * Creates the user creation form (which initially is empty).
     */
    #[IgnoreValidation(['argumentName' => 'user'])]
    public function newAction(?FrontendUser $user = null, ?int $userGroup = null): ResponseInterface
    {
        $newUser = ($user instanceof FrontendUser) ? $user : GeneralUtility::makeInstance(FrontendUser::class);
        $dataForView = [
            'user' => $newUser,
            'selectedUserGroup' => $userGroup,
        ];
        $userGroupSetting = $this->settings['groupsForNewUsers'] ?? null;
        $userGroupUids = \is_string($userGroupSetting) ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
        if ($userGroupUids !== []) {
            $dataForView['userGroups'] = $this->userGroupRepository->findByUids($userGroupUids);
        }

        $captchaSetting = $this->settings['captcha'] ?? 0;
        $isCaptchaEnabled = (\is_string($captchaSetting) || \is_int($captchaSetting)) ? (bool)$captchaSetting : false;
        if ($isCaptchaEnabled) {
            $dataForView['captcha'] = $this->captchaFactory->generateChallenge();
        }

        $redirectUrl = $this->getRedirectUrl();
        if (\is_string($redirectUrl)) {
            $dataForView['redirectUrl'] = $redirectUrl;
        }

        $this->view->assignMultiple($dataForView);

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        if ($this->arguments->hasArgument('user')) {
            $conjunctionUserValidator = new ConjunctionValidator();
            $conjunctionUserValidator->addValidator($this->arguments->getArgument('user')->getValidator());
            $userValidator = $this->userValidator;
            $userValidator->setSettings($this->settings);
            $conjunctionUserValidator->addValidator($userValidator);
            $this->arguments->getArgument('user')->setValidator($conjunctionUserValidator);
        }
        if ($this->arguments->hasArgument('captcha')) {
            $conjunctionCaptchaValidator = new ConjunctionValidator();
            $conjunctionCaptchaValidator->addValidator($this->arguments->getArgument('captcha')->getValidator());
            $captchaValidator = $this->captchaValidator;
            $captchaValidator->setSettings($this->settings);
            $conjunctionCaptchaValidator->addValidator($captchaValidator);
            $this->arguments->getArgument('captcha')->setValidator($conjunctionCaptchaValidator);
        }
    }

    /**
     * Creates and persists a new user.
     *
     * The arguments are optional in order to avoid a crash when someone is using a FE login form on the sane page
     * after creating a user with this action. (This will use the current URL as form target, causing the user to be
     * null as it had been sent via a POST request.)
     *
     * Note: The `$captcha` argument is not directly used, but it needs to be present for the validator to be
     * applicable.
     *
     * @throws \RuntimeException
     */
    public function createAction(
        ?FrontendUser $user = null,
        ?int $userGroup = null,
        ?Captcha $captcha = null,
    ): ResponseInterface {
        if (!$user instanceof FrontendUser) {
            return $this->htmlResponse();
        }

        $this->enrichUser($user, $userGroup);
        $this->userRepository->add($user);
        $this->userRepository->persistAll();

        $this->afterCreate($user);
        $redirectResponse = $this->handleRedirect();

        return $redirectResponse instanceof ResponseInterface
            ? $redirectResponse : $this->htmlResponse();
    }

    /**
     * Enriches the user with a username, a password, a full name, a PID and a group.
     *
     * Also sets the last login date to now, and saves the date the terms or privacy checkboxes were checked.
     */
    private function enrichUser(FrontendUser $user, ?int $userGroupUid): void
    {
        $this->generateAndSetFullNameForUser($user);
        $this->credentialsGenerator->generateAndSetUsernameForUser($user);
        $this->credentialsGenerator->generateAndSetPasswordForUser($user);

        $this->enrichWithPid($user);
        $this->enrichWithGroup($user, $userGroupUid);
        $nowAsString = $this->context->getPropertyFromAspect('date', 'iso');
        \assert(\is_string($nowAsString));
        $now = new \DateTime($nowAsString);
        $user->setLastLogin($now);

        if ($user->hasTermsAcknowledged()) {
            $user->setTermsDateOfAcceptance($now);
        }
        if ($user->getPrivacy()) {
            $user->setPrivacyDateOfAcceptance($now);
        }
    }

    private function generateAndSetFullNameForUser(FrontendUser $user): void
    {
        if ($user->getName() !== '') {
            return;
        }

        $fullName = \trim($user->getFirstName() . ' ' . $user->getLastName());
        $user->setName($fullName);
    }

    private function enrichWithPid(FrontendUser $user): void
    {
        $pageUid = $this->settings['systemFolderForNewUsers'] ?? null;
        if (\is_numeric($pageUid)) {
            $user->setPid((int)$pageUid);
        }
    }

    private function enrichWithGroup(FrontendUser $user, ?int $userGroupUid): void
    {
        $userGroupSetting = $this->settings['groupsForNewUsers'] ?? null;
        $userGroupUids = \is_string($userGroupSetting) ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
        if (\is_int($userGroupUid) && $userGroupUid >= 1 && \in_array($userGroupUid, $userGroupUids, true)) {
            $group = $this->userGroupRepository->findByUid($userGroupUid);
            if ($group instanceof FrontendUserGroup) {
                $user->addUserGroup($group);
            }
        }

        if ($userGroupUids !== [] && $user->getUserGroup()->count() === 0) {
            $userGroups = $this->userGroupRepository->findByUids($userGroupUids);
            foreach ($userGroups as $userGroup) {
                $user->addUserGroup($userGroup);
            }
        }
    }

    /**
     * Stores the UID of the created user in the FE session.
     */
    private function afterCreate(FrontendUser $user): void
    {
        $frontEndAuthentication = $this->getFrontendUserAuthentication();
        $frontEndAuthentication->setAndSaveSessionData('onetimeaccountUserUid', $user->getUid());
    }

    private function getFrontendUserAuthentication(): FrontendUserAuthentication
    {
        $userAuthentication = $this->request->getAttribute('frontend.user');
        \assert($userAuthentication instanceof FrontendUserAuthentication);

        return $userAuthentication;
    }

    private function handleRedirect(): ?ResponseInterface
    {
        $redirectUrl = $this->getRedirectUrl();
        if (!\is_string($redirectUrl)) {
            return null;
        }

        $sanitizedUrl = GeneralUtility::sanitizeLocalUrl($redirectUrl);

        return $sanitizedUrl !== '' ? $this->redirectToUri($redirectUrl) : null;
    }

    /**
     * @return non-empty-string|null
     */
    private function getRedirectUrl(): ?string
    {
        $parsedBody = $this->request->getParsedBody();
        $redirectUrlFromParsedBody = \is_array($parsedBody) ? ($parsedBody['redirect_url'] ?? null) : null;
        $queryParams = $this->request->getQueryParams();
        $redirectUrlFromQueryParams = $queryParams['redirect_url'] ?? null;
        $redirectUrl = $redirectUrlFromParsedBody ?? $redirectUrlFromQueryParams ?? null;

        return (\is_string($redirectUrl) && $redirectUrl !== '') ? $redirectUrl : null;
    }
}
