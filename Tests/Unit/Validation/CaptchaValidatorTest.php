<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Validation;

use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CaptchaValidator::class)]
final class CaptchaValidatorTest extends UnitTestCase
{
    private CaptchaValidator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $context = new Context();

        $this->subject = new CaptchaValidator($context, new CaptchaFactory($context));
    }

    #[Test]
    public function isValidator(): void
    {
        self::assertInstanceOf(ValidatorInterface::class, $this->subject);
        self::assertInstanceOf(AbstractValidator::class, $this->subject);
    }

    #[Test]
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    #[Test]
    public function validateWithNullAndCaptchaNotEnabledReturnsNoErrors(): void
    {
        $result = $this->subject->validate(null);

        self::assertFalse($result->hasErrors());
    }
}
