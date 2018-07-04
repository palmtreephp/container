<?php

namespace Palmtree\Container;

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
        $yaml = static::parseYamlFile($configFile);

        if (!isset($yaml['services'])) {
            $yaml['services'] = [];
        }

        if (!isset($yaml['parameters'])) {
            $yaml['parameters'] = [];
        }

        $container = new Container($yaml['services'], $yaml['parameters']);

        // Parse again after the container again to import PHP files
        static::parseYamlFile($configFile, $container);

        $container->instantiateServices();

        return $container;
    }

    /**
     * @param string         $file
     * @param Container|null $container
     *
     * @return mixed
     */
    protected static function parseYamlFile($file, $container = null)
    {
        $data = Yaml::parse(file_get_contents($file));

        $data = static::parseImports($data, dirname($file), $container);

        return $data;
    }

    /**
     * @param string    $file
     * @param Container $container
     */
    protected static function parsePhpFile($file, Container $container)
    {
        require $file;
    }

    /**
     * @param array          $data
     * @param string         $dir
     * @param Container|null $container
     *
     * @return mixed
     */
    protected static function parseImports($data, $dir, $container = null)
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
                        $reference = &$data;
                    } else {
                        $reference = &$data[$key];
                    }

                    $extension = pathinfo($resource, PATHINFO_EXTENSION);

                    if ($extension === 'yml' || $extension === 'yaml') {
                        $reference = array_replace_recursive($reference, static::parseYamlFile($resource));
                        unset($reference['imports'][$importKey]);
                    } elseif ($extension === 'php' && $container instanceof Container) {
                        static::parsePhpFile($resource, $container);
                        unset($reference['imports'][$importKey]);
                    }
                }
            }
        }

        return $data;
    }
}
