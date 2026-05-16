<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Service;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(CaptchaFactory::class)]
final class CaptchaFactoryTest extends FunctionalTestCase
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const ADDITIONAL_SECRET = 'onetimeaccount-captcha';

    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    private \DateTimeImmutable $now;

    private CaptchaFactory $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $now = $this->get(Context::class)->getPropertyFromAspect('date', 'full');
        self::assertInstanceOf(\DateTimeImmutable::class, $now);
        $this->now = $now;

        $this->subject = $this->get(CaptchaFactory::class);
    }

    #[Test]
    public function isAvailableViaContainer(): void
    {
        $subject = $this->get(CaptchaFactory::class);

        self::assertInstanceOf(CaptchaFactory::class, $subject);
    }

    #[Test]
    public function generateChallengeGeneratesCaptcha(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertInstanceOf(Captcha::class, $result);
    }

    #[Test]
    public function generateChallengeSetsValidUntilExactlyFiveMinutesInTheFuture(): void
    {
        $result = $this->subject->generateChallenge();
        $validUntil = $result->getValidUntil();

        self::assertInstanceOf(\DateTime::class, $validUntil);
        self::assertSame($this->now->getTimestamp() + 60 * 5, $validUntil->getTimestamp());
    }

    #[Test]
    public function generateChallengeSetsCorrectAnswerToFortyCharacterHexString(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertMatchesRegularExpression('/^[\\da-f]{40}$/', $result->getCorrectAnswer());
    }

    #[Test]
    public function generateChallengeSetsCorrectAnswerAsHashFromFormattedValidUntilDateWithEncryptionKey(): void
    {
        $result = $this->subject->generateChallenge();

        $validUntil = $result->getValidUntil();
        self::assertInstanceOf(\DateTime::class, $validUntil);
        $validUntilAsString = $validUntil->format(self::DATE_FORMAT);
        $expectedAnswer = GeneralUtility::hmac($validUntilAsString, self::ADDITIONAL_SECRET);

        self::assertSame($expectedAnswer, $result->getCorrectAnswer());
    }

    #[Test]
    public function generateChallengeSetsDecoyAnswerToFortyFourCharacterHexString(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertMatchesRegularExpression('/^[\\da-f]{40}$/', $result->getDecoyAnswer());
    }

    #[Test]
    public function generateChallengeSetsDecoyAnswerDifferentFromCorrectAnswer(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertNotSame($result->getCorrectAnswer(), $result->getDecoyAnswer());
    }

    #[Test]
    public function fillCorrectAnswerForCaptchaWithoutValiUntilKeepsCorrectAnswerEmpty(): void
    {
        $captcha = new Captcha();

        $this->subject->fillCorrectAnswer($captcha);

        self::assertSame('', $captcha->getCorrectAnswer());
    }

    #[Test]
    public function fillCorrectAnswerForCaptchaWithValiUntilSetsCorrectAnswerToFortyCharactersHexString(): void
    {
        $captcha = new Captcha();
        $captcha->setValidUntil(new \DateTime());

        $this->subject->fillCorrectAnswer($captcha);

        self::assertMatchesRegularExpression('/^[\\da-f]{40}$/', $captcha->getCorrectAnswer());
    }

    #[Test]
    public function fillCorrectAnswerForCaptchaWithValiUntilSetsCorrectAnswerToHashOfValidUntilAndSecrets(): void
    {
        $captcha = new Captcha();
        $validUntil = new \DateTime();
        $captcha->setValidUntil($validUntil);

        $this->subject->fillCorrectAnswer($captcha);

        $validUntilAsString = $validUntil->format(self::DATE_FORMAT);
        $expectedAnswer = GeneralUtility::hmac($validUntilAsString, self::ADDITIONAL_SECRET);
        self::assertSame($expectedAnswer, $captcha->getCorrectAnswer());
    }
}
