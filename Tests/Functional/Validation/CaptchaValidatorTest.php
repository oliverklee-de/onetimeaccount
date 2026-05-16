<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Validation;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(CaptchaValidator::class)]
final class CaptchaValidatorTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    protected array $coreExtensionsToLoad = ['extbase', 'fluid'];

    private \DateTimeImmutable $now;

    private CaptchaFactory $captchaFactory;

    private CaptchaValidator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');

        $now = $this->get(Context::class)->getPropertyFromAspect('date', 'full');
        self::assertInstanceOf(\DateTimeImmutable::class, $now);
        $this->now = $now;

        $this->captchaFactory = $this->get(CaptchaFactory::class);

        $this->subject = $this->get(CaptchaValidator::class);
    }

    #[Test]
    public function isAvailableViaContainer(): void
    {
        $subject = $this->get(CaptchaValidator::class);

        self::assertInstanceOf(CaptchaValidator::class, $subject);
    }

    private static function assertCaptchaValidationError(Result $result): void
    {
        self::assertTrue($result->hasErrors());
        $forProperty = $result->forProperty('givenAnswer');
        self::assertCount(1, $forProperty->getErrors());
        $firstError = $forProperty->getFirstError();
        self::assertInstanceOf(Error::class, $firstError);
        $expected = LocalizationUtility::translate('captcha.validationError', 'onetimeaccount');
        self::assertSame($expected, $firstError->getMessage());
    }

    #[Test]
    public function validateWithNullAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $result = $this->subject->validate(null);

        self::assertCaptchaValidationError($result);
    }

    #[Test]
    public function validateWithNonCaptchaObjectAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $result = $this->subject->validate(new \stdClass());

        self::assertCaptchaValidationError($result);
    }

    #[Test]
    public function validateWithCaptchaWithoutValidUntilAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $result = $this->subject->validate(new Captcha());

        self::assertCaptchaValidationError($result);
    }

    #[Test]
    public function validateWithFutureValidUntilAndEmptyAnswerAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $captcha = new Captcha();
        $captcha->setValidUntil(\DateTime::createFromImmutable($this->now)->modify('+1 second'));
        $captcha->setGivenAnswer('');

        $result = $this->subject->validate($captcha);

        self::assertCaptchaValidationError($result);
    }

    #[Test]
    public function validateWithFutureValidUntilAndOtherGivenAnswerAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $captcha = new Captcha();
        $captcha->setValidUntil(\DateTime::createFromImmutable($this->now)->modify('+1 second'));
        $captcha->setGivenAnswer('foo');

        $result = $this->subject->validate($captcha);

        self::assertCaptchaValidationError($result);
    }

    #[Test]
    public function validateWithFutureValidUntilAndGivenAnswerMatchingTheCorrectAnswerNotAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $captcha = new Captcha();
        $captcha->setValidUntil(\DateTime::createFromImmutable($this->now)->modify('+1 second'));
        $captcha->setGivenAnswer('foo');

        $expectedCaptcha = new Captcha();
        $expectedCaptcha->setValidUntil(\DateTime::createFromImmutable($this->now)->modify('+1 second'));
        $this->captchaFactory->fillCorrectAnswer($expectedCaptcha);
        $captcha->setGivenAnswer($expectedCaptcha->getCorrectAnswer());

        $result = $this->subject->validate($captcha);

        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function validateWithValidUntilRightNowAndGivenAnswerMatchingTheCorrectAnswerNotAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $validUntil = new \DateTime();
        $validUntil->setTimestamp($this->now->getTimestamp());

        $captcha = new Captcha();
        $captcha->setValidUntil($validUntil);
        $captcha->setGivenAnswer('foo');

        $expectedCaptcha = new Captcha();
        $expectedCaptcha->setValidUntil($validUntil);
        $this->captchaFactory->fillCorrectAnswer($expectedCaptcha);
        $captcha->setGivenAnswer($expectedCaptcha->getCorrectAnswer());

        $result = $this->subject->validate($captcha);

        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function validateWithPastValidUntilAndGivenAnswerMatchingTheCorrectAnswerAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $validUntil = new \DateTime();
        $validUntil->setTimestamp($this->now->getTimestamp() - 1);

        $captcha = new Captcha();
        $captcha->setValidUntil($validUntil);
        $captcha->setGivenAnswer('foo');

        $expectedCaptcha = new Captcha();
        $expectedCaptcha->setValidUntil($validUntil);
        $this->captchaFactory->fillCorrectAnswer($expectedCaptcha);
        $captcha->setGivenAnswer($expectedCaptcha->getCorrectAnswer());

        $result = $this->subject->validate($captcha);

        self::assertCaptchaValidationError($result);
    }
}
