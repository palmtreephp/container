<?php

namespace Palmtree\Container\Tests\Fixtures\Service;

class PrivateServiceConsumer
{
    /** @var PrivateService */
    private $privateService;

    public function __construct(PrivateService $privateService)
    {
        $this->privateService = $privateService;
    }

    /**
     * @return PrivateService
     */
    public function getPrivateService()
    {
        return $this->privateService;
    }
}
