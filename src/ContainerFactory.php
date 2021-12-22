<?php

namespace Palmtree\Container;

use Symfony\Component\Yaml\Yaml;

class ContainerFactory
{
    /** @var Container */
    private $container;
    /** @var array */
    private $phpImports = [];

    private function __construct(string $configFile)
    {
        $yaml = $this->parseYamlFile($configFile);

        $services = $yaml['services'] ?? [];

        if (isset($services['_config'])) {
            $config = $services['_config'];
            unset($services['_config']);
        }

        $this->container = new Container($services, $yaml['parameters'] ?? [], $config ?? []);

        foreach ($this->phpImports as $file) {
            self::requirePhpFile($file, $this->container);
        }

        $this->container->instantiateServices();
    }

    public static function create(string $configFile): Container
    {
        $factory = new self($configFile);

        return $factory->container;
    }

    private function parseYamlFile(string $file): array
    {
        $data = Yaml::parseFile($file) ?? [];

        $data = $this->parseImports($data, dirname($file));

        return $data;
    }

    private function parseImports(array $data, string $dir): array
    {
        foreach ($data['imports'] ?? [] as $key => $import) {
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

    private static function requirePhpFile(string $file, Container $container)
    {
        require $file;
    }

    private static function getImportResource(string $dir, array $import): string
    {
        $resource = $import['resource'];

        // Prefix the directory if resource is not an absolute path
        if ($resource[0] !== DIRECTORY_SEPARATOR && !preg_match('~\A[A-Z]:(?![^/\\\\])~i', $resource)) {
            $resource = "$dir/$resource";
        }

        return $resource;
    }
}
