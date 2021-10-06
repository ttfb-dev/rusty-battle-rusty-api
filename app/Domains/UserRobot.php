<?php


namespace App\Domains;

use App\Modules\BaseModule;

class UserRobot extends BaseRobot
{
    function addModule(BaseModule $module, string $slot)
    {
        $module->setSlot($slot);
        $modules = $this->getModules();
        $modules[$slot] = $module;
        $this->setModules($modules);
    }
}
