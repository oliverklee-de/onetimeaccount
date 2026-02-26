<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Service;

use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory for building valid Captcha instances to be added to forms.
 */
class CaptchaFactory implements SingletonInterface
{
    private const ADDITIONAL_SECRET = 'onetimeaccount-captcha';
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Creates a new Captcha instance that is valid for exactly 5 minutes.
     */
    public function generateChallenge(): Captcha
    {
        $captcha = GeneralUtility::makeInstance(Captcha::class);

        $nowAsUnixTimestamp = $this->context->getPropertyFromAspect('date', 'timestamp');
        $validUntil = new \DateTime();
        $validUntil->setTimestamp($nowAsUnixTimestamp + 5 * Time::SECONDS_PER_MINUTE);
        $captcha->setValidUntil($validUntil);

        $this->fillCorrectAnswer($captcha);

        $decoyAnswer = \bin2hex(\random_bytes(20));
        $captcha->setDecoyAnswer($decoyAnswer);

        return $captcha;
    }

    /**
     * Fills in the correct answer for the given CAPTCHA, based on the valid-until date.
     *
     * (If none is set, the correct answer will not be set.)
     */
    public function fillCorrectAnswer(Captcha $captcha): void
    {
        $validUntil = $captcha->getValidUntil();
        if (!$validUntil instanceof \DateTime) {
            return;
        }

        $correctAnswer = GeneralUtility::hmac($validUntil->format(self::DATE_FORMAT), self::ADDITIONAL_SECRET);
        $captcha->setCorrectAnswer($correctAnswer);
    }
}
