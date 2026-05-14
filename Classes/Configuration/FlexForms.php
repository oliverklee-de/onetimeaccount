<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Configuration;

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
        $items = [];
        foreach (static::AVAILABLE_FIELDS as $fieldKey) {
            $label = 'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang.xlf:' . $fieldKey;
            $items[] = [
                'label' => $label,
                'value' => $fieldKey,
            ];
        }

        $configuration['items'] = $items;
    }
}
