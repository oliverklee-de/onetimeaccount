<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Configuration;

use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * This class provides functions for building FlexForms.
 */
class FlexForms
{
    protected const AVAILABLE_FIELDS = [
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
        'membershipNumber',
        'dateOfBirth',
        'status',
        'vatIn',
        'comments',
        'privacy',
        'termsAcknowledged',
    ];

    /**
     * Sets the selectable items for the fields to display in `$configuration`.
     *
     * @param array<string, mixed> $configuration
     */
    public function buildFields(array &$configuration): void
    {
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $labelKey = 0;
            $valueKey = 1;
        } else {
            $labelKey = 'label';
            $valueKey = 'value';
        }

        $items = [];
        foreach (static::AVAILABLE_FIELDS as $fieldKey) {
            $label = 'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang.xlf:' . $fieldKey;
            $items[] = [
                $labelKey => $label,
                $valueKey => $fieldKey,
            ];
        }

        $configuration['items'] = $items;
    }
}
