<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class can generate a username and password for a user.
 */
class CredentialsGenerator implements SingletonInterface
{
    /**
     * needs to be an even number
     */
    private const PASSWORD_LENGTH = 32;

    private readonly PasswordHashInterface $passwordHasher;

    public function __construct(
        private readonly FrontendUserRepository $userRepository,
        PasswordHashFactory $passwordHashFactory,
    ) {
        $this->passwordHasher = $passwordHashFactory->getDefaultHashInstance('FE');
    }

    /**
     * Generates a unique username for the given user (either from the email address possibly with a number attached,
     * or a 32-character random hex username if the email address is empty).
     */
    public function generateAndSetUsernameForUser(FrontendUser $user): void
    {
        if ($user->getUsername() !== '') {
            return;
        }

        if ($user->getEmail() !== '') {
            $this->generateAndSetUsernameFromEmail($user);
        } else {
            $this->generateAndSetRandomHexUsername($user);
        }
    }

    /**
     * Generates a random long password and sets the password hash for the user.
     */
    public function generateAndSetPasswordForUser(FrontendUser $user): void
    {
        if ($user->getPassword() !== '') {
            return;
        }

        $password = \bin2hex(\random_bytes(self::PASSWORD_LENGTH / 2));
        $passwordHash = $this->passwordHasher->getHashedPassword($password);
        $user->setPassword($passwordHash);
    }

    private function generateAndSetUsernameFromEmail(FrontendUser $user): void
    {
        $email = \trim($user->getEmail());
        $userByEmail = $this->userRepository->findOneByUsername($email);
        if ($userByEmail === null) {
            $user->setUsername($email);
            return;
        }

        $suffix = 1;
        do {
            $username = $email . '_' . $suffix;
            $userByEmail = $this->userRepository->findOneByUsername($username);
            $suffix++;
        } while ($userByEmail !== null);

        $user->setUsername($username);
    }

    private function generateAndSetRandomHexUsername(FrontendUser $user): void
    {
        $username = \bin2hex(\random_bytes(16));
        $user->setUsername($username);
    }
}
