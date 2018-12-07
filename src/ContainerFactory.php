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
    private static function parseYamlFile($file, $container = null)
    {
        $data = Yaml::parse(file_get_contents($file));

        if (isset($data['imports'])) {
            $data = static::parseImports($data, dirname($file), $container);
        }

        return $data;
    }

    /**
     * @param string    $file
     * @param Container $container
     */
    private static function parsePhpFile($file, Container $container)
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
    private static function parseImports($data, $dir, $container = null)
    {
        foreach ($data['imports'] as $key => $import) {
            $resource = self::getImportResource($dir, $import);

            $extension = pathinfo($resource, PATHINFO_EXTENSION);

            if ($extension === 'yml' || $extension === 'yaml') {
                $data = array_replace_recursive($data, static::parseYamlFile($resource));
                unset($data['imports'][$key]);
            } elseif ($extension === 'php' && $container instanceof Container) {
                static::parsePhpFile($resource, $container);
                unset($data['imports'][$key]);
            }
        }

        return $data;
    }

    private static function getImportResource($dir, $import)
    {
        $resource = $import['resource'];

        // Prefix the directory if resource is not an absolute path
        if ($resource[0] !== DIRECTORY_SEPARATOR && !preg_match('~\A[A-Z]:(?![^/\\\\])~i', $resource)) {
            $resource = "$dir/$resource";
        }

        return $resource;
    }
}
