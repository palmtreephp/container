<?php

namespace Palmtree\ServiceContainer;

use Symfony\Component\Yaml\Yaml;

class ContainerFactory
{
    /**
     * @param string $configFile
     *
     * @return Container
     */
    public static function create($configFile)
    {
        $yaml = static::parseYaml($configFile);

        $container = new Container($yaml['services'], $yaml['parameters']);

        return $container;
    }

    protected static function parseYaml($file)
    {
        $data = Yaml::parse(file_get_contents($file));

        $data = static::parseYamlImports($data, dirname($file));

        return $data;
    }

    protected static function parseYamlImports($data, $dir)
    {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $imports = null;
            $root    = false;

            if ($key === 'imports') {
                $imports = $value;
                $root    = true;
            } elseif (isset($value['imports'])) {
                $imports = $value['imports'];
            }

            if ($imports) {
                foreach ($imports as $importKey => $import) {
                    $resource = $import['resource'];

                    if (strpos($resource, '/') === false) {
                        $resource = sprintf('%s/%s', $dir, $resource);
                    }

                    if ($root) {
                        $data = array_replace_recursive($data, static::parseYaml($resource));
                        unset($data['imports']);
                    } else {
                        $data[$key] = array_replace_recursive($data[$key], static::parseYaml($resource));
                        unset($data[$key]['imports']);
                    }
                }
            }
        }

        return $data;
    }
}
