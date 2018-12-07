<?php

namespace Palmtree\Container;

use Symfony\Component\Yaml\Yaml;

class ContainerFactory
{
    /** @var Container */
    private $container;
    /** @var array */
    private $phpImports = [];

    public function __construct($configFile)
    {
        $yaml = $this->parseYamlFile($configFile);

        if (!isset($yaml['services'])) {
            $yaml['services'] = [];
        }

        if (!isset($yaml['parameters'])) {
            $yaml['parameters'] = [];
        }

        $this->container = new Container($yaml['services'], $yaml['parameters']);

        foreach ($this->phpImports as $file) {
            self::requirePhpFile($file, $this->container);
        }

        $this->container->instantiateServices();
    }

    /**
     * @param string $configFile
     *
     * @return Container
     */
    public static function create($configFile)
    {
        $factory = new self($configFile);

        return $factory->container;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private function parseYamlFile($file)
    {
        $data = Yaml::parseFile($file);

        if (isset($data['imports'])) {
            $data = $this->parseImports($data, dirname($file));
        }

        return $data;
    }

    /**
     * @param array  $data
     * @param string $dir
     *
     * @return array
     */
    private function parseImports($data, $dir)
    {
        foreach ($data['imports'] as $key => $import) {
            $resource = self::getImportResource($dir, $import);

            $extension = pathinfo($resource, PATHINFO_EXTENSION);

            if ($extension === 'yml' || $extension === 'yaml') {
                $data = array_replace_recursive($data, $this->parseYamlFile($resource));
                unset($data['imports'][$key]);
            } elseif ($extension === 'php') {
                $this->phpImports[] = $resource;
                unset($data['imports'][$key]);
            }
        }

        return $data;
    }

    /**
     * @param string    $file
     * @param Container $container
     */
    private static function requirePhpFile($file, $container)
    {
        require($file);
    }

    /**
     * @param string $dir
     * @param array  $import
     *
     * @return string
     */
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
