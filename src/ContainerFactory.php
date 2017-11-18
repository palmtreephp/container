<?php

namespace Palmtree\Container;

use Symfony\Component\Yaml\Yaml;

class ContainerFactory
{
    /**
     * @param string $configFile
     * @param bool $build
     *
     * @return Container
     */
    public static function create($configFile, $build = true)
    {
        $yaml = static::parseYamlFile($configFile);

        $container = new Container($yaml['services'], $yaml['parameters']);

        if ($build) {
            $container->build();
        }

        return $container;
    }

    protected static function parseYamlFile($file)
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
            $root = false;

            if ($key === 'imports') {
                $imports = $value;
                $root = true;
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
                        $reference = &$data;
                    } else {
                        $reference = &$data[$key];
                    }

                    $reference = array_replace_recursive($reference, static::parseYamlFile($resource));
                    unset($reference['imports']);
                }
            }
        }

        return $data;
    }
}
