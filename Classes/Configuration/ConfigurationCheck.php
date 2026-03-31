<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;

/**
 * Checks the plugin configuration for errors.
 *
 * @internal
 */
class ConfigurationCheck extends AbstractConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $fieldsToShow = $this->configuration->getAsTrimmedArray('fieldsToShow');

        $this->checkIfMultiInSetOrEmpty(
            'requiredFields',
            'These values specify which fields are required.
            Those may only include fields that are configured to be show.
            If you mark non-shown fields as required, the user will not be able submit the form.',
            $fieldsToShow,
        );
    }
}
