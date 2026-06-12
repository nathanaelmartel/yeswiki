<?php

namespace YesWiki\Core\Service;

/**
 * Provide configuration file from environment.
 */
class ConfigurationFileProvider
{
    public static function getConfigFileFromEnv(): string
    {
        $wakkaConfigFile = getenv('WAKKA_CONFIG_FILE');
        if (false === $wakkaConfigFile) {
            return 'wakka.config.php';
        }

        return $wakkaConfigFile;
    }
}
