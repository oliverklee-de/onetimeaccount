<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\Gender;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Validation\UserValidator
 */
final class UserValidatorTest extends UnitTestCase
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

    private UserValidator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new UserValidator();
    }

    private function buildFullModel(): FrontendUser
    {
        $user = new FrontendUser();
        $user->setName('Ben Best');
        $user->setFirstName('Ben');
        $user->setLastName('Best');
        $user->setAddress('At the multi-core machine 3');
        $user->setTelephone('+49 1111 2222222');
        $user->setEmail('ben@example.com');
        $user->setTitle('developer');
        $user->setZip('12345');
        $user->setCity('Development Hill');
        $user->setCountry('Buthan');
        $user->setWww('https://example.com');
        $user->setCompany('Lumon');
        $user->setDepartment('Choreography and Merriment');
        $user->setZone('ABC');
        $user->setPrivacy(true);
        $user->setTermsAcknowledged(true);
        $user->setFullSalutation('Yo Ben!');
        $user->setGender(Gender::male());
        $user->setStatus(FrontendUser::STATUS_STUDENT);
        $user->setComments('Wonderful!');
        $user->setDateOfBirth(new \DateTime('now'));
        $user->setVatIn('DE123456789');

        return $user;
    }

    /**
     * @test
     */
    public function isValidator(): void
    {
        self::assertInstanceOf(ValidatorInterface::class, $this->subject);
        self::assertInstanceOf(AbstractValidator::class, $this->subject);
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function validateWithNonUserValueReturnsNoErrors(): void
    {
        $result = $this->subject->validate(new \stdClass());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithEmptyUserForNoShowOrRequiredFieldsSettingReturnsNoErrors(): void
    {
        $result = $this->subject->validate(new FrontendUser());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithEmptyUserForEmptyRequiredAndVisibleFieldsReturnsNoErrors(): void
    {
        $this->subject->setSettings(['fieldsToShow' => '', 'requiredFields' => '']);

        $result = $this->subject->validate(new FrontendUser());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithEmptyUserForNoRequiredFieldsButAllVisibleReturnsNoErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => '']);

        $result = $this->subject->validate(new FrontendUser());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateModelWithFullModelForAllFieldsRequiredAndVisibleReturnsNoErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => $concatenatedFields]);

        $result = $this->subject->validate($this->buildFullModel());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateModelWithFullModelForNoFieldsRequiredAndAllVisibleReturnsNoErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => '']);

        $result = $this->subject->validate($this->buildFullModel());

        self::assertFalse($result->hasErrors());
    }
}
