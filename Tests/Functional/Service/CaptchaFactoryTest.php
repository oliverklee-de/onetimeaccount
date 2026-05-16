<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Service;

use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(CaptchaFactory::class)]
final class CaptchaFactoryTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    #[Test]
    public function isAvailableViaContainer(): void
    {
        $subject = $this->get(CaptchaFactory::class);

        self::assertInstanceOf(CaptchaFactory::class, $subject);
    }
}
