<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(UserValidator::class)]
final class UserValidatorTest extends FunctionalTestCase
{
    private const VALIDATABLE_FIELDS = [
        'company',
        'department',
        'gender',
        'fullSalutation',
        'name',
        'firstName',
        'lastName',
        'title',
        'address',
        'zip',
        'city',
        'zone',
        'country',
        'email',
        'telephone',
        'www',
        'dateOfBirth',
        'status',
        'comments',
        'privacy',
        'termsAcknowledged',
        'vatIn',
    ];

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    protected array $coreExtensionsToLoad = ['extbase', 'fluid'];

    private UserValidator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');

        $this->subject = new UserValidator();
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function fieldDataProvider(): array
    {
        $dataSets = [];
        foreach (self::VALIDATABLE_FIELDS as $field) {
            $dataSets[$field] = [$field];
        }

        return $dataSets;
    }

    /**
     * @param non-empty-string $field
     */
    #[Test]
    #[DataProvider('fieldDataProvider')]
    public function validateWithEmptyModelForSingleFieldRequiredAndShownAddsErrorForRequiredField(string $field): void
    {
        $this->subject->setSettings(['fieldsToShow' => $field, 'requiredFields' => $field]);

        $result = $this->subject->validate(new FrontendUser());

        self::assertTrue($result->hasErrors());
        $forProperty = $result->forProperty($field);
        self::assertCount(1, $forProperty->getErrors());
        $firstError = $forProperty->getFirstError();
        self::assertInstanceOf(Error::class, $firstError);
        $expected = LocalizationUtility::translate('validationError.fillInField', 'oelib');
        self::assertSame($expected, $firstError->getMessage());
    }

    #[Test]
    public function validateModelWithEmptyModelForAllFieldsRequiredAndVisibleReturnsErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => $concatenatedFields]);

        $result = $this->subject->validate(new FrontendUser());

        self::assertTrue($result->hasErrors());
    }
}
