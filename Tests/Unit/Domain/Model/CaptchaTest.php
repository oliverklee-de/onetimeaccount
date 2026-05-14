<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Domain\Model;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Captcha::class)]
final class CaptchaTest extends UnitTestCase
{
    private Captcha $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Captcha();
    }

    #[Test]
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    #[Test]
    public function getValidUntilInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getValidUntil());
    }

    #[Test]
    public function setValidUntilSetsValidUntil(): void
    {
        $validUntil = new \DateTime();
        $this->subject->setValidUntil($validUntil);

        self::assertSame($validUntil, $this->subject->getValidUntil());
    }

    #[Test]
    public function setValidUntilCanSetValidUntilToNull(): void
    {
        $this->subject->setValidUntil(null);

        self::assertNull($this->subject->getValidUntil());
    }

    #[Test]
    public function getCorrectAnswerInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getCorrectAnswer());
    }

    #[Test]
    public function setCorrectAnswerSetsCorrectAnswer(): void
    {
        $value = 'Club-Mate';
        $this->subject->setCorrectAnswer($value);

        self::assertSame($value, $this->subject->getCorrectAnswer());
    }

    #[Test]
    public function getDecoyAnswerInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getDecoyAnswer());
    }

    #[Test]
    public function setDecoyAnswerSetsDecoyAnswer(): void
    {
        $value = 'Club-Mate';
        $this->subject->setDecoyAnswer($value);

        self::assertSame($value, $this->subject->getDecoyAnswer());
    }

    #[Test]
    public function getGivenAnswerInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getGivenAnswer());
    }

    #[Test]
    public function setGivenAnswerSetsGivenAnswer(): void
    {
        $value = 'Club-Mate';
        $this->subject->setGivenAnswer($value);

        self::assertSame($value, $this->subject->getGivenAnswer());
    }
}
