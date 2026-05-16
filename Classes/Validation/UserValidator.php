<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\Gender;
use OliverKlee\Oelib\Validation\AbstractConfigurationDependentValidator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Checks that the fields that are configured to be required are filled in.
 *
 * @extends AbstractConfigurationDependentValidator<FrontendUser>
 */
#[Autoconfigure(public: true)]
class UserValidator extends AbstractConfigurationDependentValidator
{
    protected function getModelClassName(): string
    {
        return FrontendUser::class;
    }

    protected function isFieldFilledIn(string $field, AbstractEntity $model): bool
    {
        return $this->isIdentityFieldFilledInForUser($field, $model)
            && $this->isAddressFieldFilledInForUser($field, $model)
            && $this->isContactFieldFilledInForUser($field, $model)
            && $this->isMetaFieldFilledInForUser($field, $model);
    }

    private function isIdentityFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        return match ($field) {
            'name' => $user->getName() !== '',
            'firstName' => $user->getFirstName() !== '',
            'lastName' => $user->getLastName() !== '',
            'title' => $user->getTitle() !== '',
            'fullSalutation' => $user->getFullSalutation() !== '',
            'gender' => $user->getGender() !== Gender::notProvided(),
            'dateOfBirth' => $user->getDateOfBirth() instanceof \DateTimeInterface,
            'status' => $user->getStatus() !== FrontendUser::STATUS_NONE,
            default => true,
        };
    }

    private function isAddressFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        return match ($field) {
            'address' => $user->getAddress() !== '',
            'zip' => $user->getZip() !== '',
            'city' => $user->getCity() !== '',
            'zone' => $user->getZone() !== '',
            'country' => $user->getCountry() !== '',
            default => true,
        };
    }

    private function isContactFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        return match ($field) {
            'telephone' => $user->getTelephone() !== '',
            'email' => $user->getEmail() !== '',
            'www' => $user->getWww() !== '',
            default => true,
        };
    }

    private function isMetaFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        return match ($field) {
            'company' => $user->getCompany() !== '',
            'department' => $user->getDepartment() !== '',
            'vatIn' => $user->getVatIn() !== '',
            'privacy' => $user->getPrivacy(),
            'termsAcknowledged' => $user->hasTermsAcknowledged(),
            'status' => $user->getStatus() !== FrontendUser::STATUS_NONE,
            'comments' => $user->getComments() !== '',
            default => true,
        };
    }
}
