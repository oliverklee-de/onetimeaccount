<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(CredentialsGenerator::class)]
final class CredentialsGeneratorTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    private CredentialsGenerator $subject;

    private FrontendUserRepository&MockObject $userRepositoryMock;

    private PasswordHashInterface&MockObject $passwordHasherMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasherMock = $this->createMock(PasswordHashInterface::class);
        $passwordHashFactoryMock = $this->createMock(PasswordHashFactory::class);
        $passwordHashFactoryMock->method('getDefaultHashInstance')->with('FE')->willReturn($this->passwordHasherMock);

        GeneralUtility::addInstance(PasswordHashFactory::class, $passwordHashFactoryMock);

        $this->userRepositoryMock = $this->createMock(FrontendUserRepository::class);
        $this->subject = new CredentialsGenerator($this->userRepositoryMock);
    }

    #[Test]
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @return \Generator<string, array<int, FrontendUser>>
     */
    public static function userWithUsernameDataProvider(): \Generator
    {
        $userWithUsernameWithoutEmail = new FrontendUser();
        $userWithUsernameWithoutEmail->setUsername('max');
        yield 'with username, without email' => [$userWithUsernameWithoutEmail];

        $userWithUsernameAndWithEmail = new FrontendUser();
        $userWithUsernameAndWithEmail->setUsername('max');
        $userWithUsernameAndWithEmail->setEmail('max@exampl.com');
        yield 'with username, with email' => [$userWithUsernameAndWithEmail];
    }

    #[Test]
    #[DataProvider('userWithUsernameDataProvider')]
    public function generateAndSetUsernameForUserWithUsernameKeepsUsernameUnchanged(FrontendUser $user): void
    {
        $existingUsername = $user->getUsername();

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($existingUsername, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithUniqueEmailUsesEmailAsUsername(): void
    {
        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail($email);
        $this->userRepositoryMock->method('findOneByUsername')->with($email)->willReturn(null);

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($email, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithUniqueEmailTrimsEmailAsUsername(): void
    {
        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail(' ' . $email . ' ');
        $this->userRepositoryMock->method('findOneByUsername')->with($email)->willReturn(null);

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($email, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithExistingEmailUsesEmailWithUniqueSuffixAsUsername(): void
    {
        $email = 'unique@example.com';
        $emailWithSuffix = $email . '_1';
        $user = new FrontendUser();
        $user->setEmail($email);
        $this->userRepositoryMock->method('findOneByUsername')->willReturnMap([
            [$email, $user],
            [$emailWithSuffix, null],
        ]);

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($emailWithSuffix, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithExistingEmailWithSuffixUsesEmailWithNextSuffixAsUsername(): void
    {
        $email = 'unique@example.com';
        $emailWithSuffix1 = $email . '_1';
        $emailWithSuffix2 = $email . '_2';
        $user = new FrontendUser();
        $user->setEmail($email);

        $this->userRepositoryMock->method('findOneByUsername')->willReturnMap([
            [$email, $user],
            [$emailWithSuffix1, $user],
            [$emailWithSuffix2, null],
        ]);

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($emailWithSuffix2, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithoutEmailUsesUuidAsUsername(): void
    {
        $user = new FrontendUser();

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertMatchesRegularExpression('/^[a-z\\d]{32}$/', $user->getUsername());
    }

    #[Test]
    public function generateAndSetPasswordForUserWithExistingPasswordKeepsOldPassword(): void
    {
        $user = new FrontendUser();
        $existingPassword = 'gzuio134tfgzuiobft1234';
        $user->setPassword($existingPassword);

        $this->subject->generateAndSetPasswordForUser($user);

        self::assertSame($existingPassword, $user->getPassword());
    }

    #[Test]
    public function generateAndSetPasswordForUserWithExistingPasswordReturnsNull(): void
    {
        $user = new FrontendUser();
        $existingPassword = 'gzuio134tfgzuiobft1234';
        $user->setPassword($existingPassword);

        $result = $this->subject->generateAndSetPasswordForUser($user);

        self::assertNull($result);
    }

    #[Test]
    public function generateAndSetPasswordForUserWithoutExistingPasswordReturnsTwelveCharacterPassword(): void
    {
        $user = new FrontendUser();
        $this->passwordHasherMock->method('getHashedPassword')->with(self::anything())->willReturn('');

        $result = $this->subject->generateAndSetPasswordForUser($user);

        self::assertIsString($result);
        self::assertMatchesRegularExpression('/^\\w{32}$/', $result);
    }

    #[Test]
    public function generateAndSetPasswordForUserWithoutExistingPasswordSetsHashOfTwelveCharacterPassword(): void
    {
        $passwordHash
            = '$argon2i$v=19$m=65536,t=16,p=1$ODBXYmZrYkQ2akMwa1lHYg$iWz2uY5XHXAhjqG69uFSQDWvy/y1G931gk/s19sfBxo';
        $this->passwordHasherMock->method('getHashedPassword')->with(self::isType('string'))->willReturn($passwordHash);
        $user = new FrontendUser();

        $this->subject->generateAndSetPasswordForUser($user);

        self::assertSame($passwordHash, $user->getPassword());
    }
}
