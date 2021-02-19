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

        $this->container = new Container($yaml['services'] ?? [], $yaml['parameters'] ?? []);

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

        $data = $this->parseImports($data, \dirname($file));

        return $data;
    }

    private function parseImports(array $data, string $dir): array
    {
        foreach ($data['imports'] ?? [] as $key => $import) {
            $resource = $this->getImportResource($dir, $import);

            $extension = pathinfo($resource, \PATHINFO_EXTENSION);

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

    private function getImportResource(string $dir, array $import): string
    {
        $resource = $import['resource'];

        // Prefix the directory if resource is not an absolute path
        if (!$this->isAbsolutePath($resource)) {
            $resource = "$dir/$resource";
        }

        return $resource;
    }

    private function isAbsolutePath(string $file): bool
    {
        if ($file[0] === '/' || $file[0] === '\\'
            || (\strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] === ':'
                && ($file[2] === '\\' || $file[2] === '/')
            )
            || null !== parse_url($file, \PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}
