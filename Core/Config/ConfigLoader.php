<?php

declare(strict_types=1);

namespace Core\Config;

use Exception;

final class ConfigLoader implements ConfigInterface
{
	
	/**
     * @var array Cache para armazenar as configurações já carregadas.
     */
	private array $loadedConfigs = [];
	
    /**
     * The main public method to get a configuration value.
     *
     * @param  string  $path  The full path to the property (e.g., 'database.host').
     */
    public function get(string $path): mixed
    {
        $config = $this->loadConfigFile($path);
        $keys = $this->getKeys($path);
        $value = $this->findValue($config, $keys);

        return $value;
    }

    /**
     * Loads the configuration array from the correct file.
     *
     * @param  string  $path  The full path string.
     * @return array
     */
    private function loadConfigFile(string $path)
    {
        $configFilePath = $this->getFilePath($path);

        return require $configFilePath;
    }

    /**
     * Determines the correct file path from the full path string.
     *
     * @param  string  $path  The full path string.
     * @return string
     *
     * @throws Exception If the config file doesn't exist.
     */
    private function getFilePath(string $path)
    {
        $pathArray = explode('.', $path);
        $file = $pathArray[0];
        $configFilePath = base_path('config/'.$file.'.php');

        if (file_exists($configFilePath)) {
            return $configFilePath;
        } else {
            throw new Exception("Config file {$file}.php doesn't exist!");
        }
    }

    /**
     * Extracts the nested array keys from the full path string.
     *
     * @param  string  $path  The full path string.
     * @return array
     */
    private function getKeys(string $path)
    {
        $pathArray = explode('.', $path);

        return array_slice($pathArray, 1);
    }

    /**
     * Traverses the config array using the keys to find the value.
     *
     * @param  array  $config  The configuration array.
     * @param  array  $keys  The keys to traverse.
     * @return mixed|null The found value or null if not found.
     */
    private function findValue(array $config, array $keys)
    {
        $newValue = $config;

        foreach ($keys as $key) {
            if (! is_array($newValue) || ! isset($newValue[$key])) {
                return null;
            }

            $newValue = $newValue[$key];
        }

        return $newValue;
    }
}
