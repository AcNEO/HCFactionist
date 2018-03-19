<?php

namespace AcNEO\HCFactionist\DataProvider\Provider;

use AcNEO\HCFactionist\Main;

abstract class BaseProvider implements Provider {

    protected $main;

    public function __construct(Main $main) {
        $this->main = $main;
        $this->init();
    }

    public function init(): bool {
        return false;
    }

    public function getPlugin(): Main {
        return $this->main;
    }

}
?>