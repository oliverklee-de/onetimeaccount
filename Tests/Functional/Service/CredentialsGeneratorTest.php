<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(CredentialsGenerator::class)]
final class CredentialsGeneratorTest extends FunctionalTestCase
{
    private const FIXTURES_PATH = __DIR__ . '/Fixtures/CredentialsGenerator';

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    private CredentialsGenerator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(CredentialsGenerator::class);
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

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($email, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithUniqueEmailTrimsEmailAsUsername(): void
    {
        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail(' ' . $email . ' ');

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($email, $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithExistingEmailUsesEmailWithUniqueSuffixAsUsername(): void
    {
        $this->importCSVDataSet(self::FIXTURES_PATH . '/FrontendUser.csv');

        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail($email);

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($email . '_1', $user->getUsername());
    }

    #[Test]
    public function generateAndSetUsernameForUserWithExistingEmailWithSuffixUsesEmailWithNextSuffixAsUsername(): void
    {
        $this->importCSVDataSet(self::FIXTURES_PATH . '/TwoFrontendUsers.csv');

        $email = 'unique@example.com';
        $user = new FrontendUser();
        $user->setEmail($email);

        $this->subject->generateAndSetUsernameForUser($user);

        self::assertSame($email . '_2', $user->getUsername());
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

        $result = $this->subject->generateAndSetPasswordForUser($user);

        self::assertIsString($result);
        self::assertMatchesRegularExpression('/^\\w{32}$/', $result);
    }

    #[Test]
    public function generateAndSetPasswordForUserWithoutExistingPasswordSetsPasswordHash(): void
    {
        $user = new FrontendUser();

        $this->subject->generateAndSetPasswordForUser($user);

        self::assertStringStartsWith('$argon2i', $user->getPassword());
    }
}
