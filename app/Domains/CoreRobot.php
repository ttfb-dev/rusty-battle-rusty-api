<?php


namespace App\Domains;


use App\Modules\BaseModule;

class CoreRobot extends BaseRobot
{
    function addModule(BaseModule $module, string $slot)
    {
        $module->setSlot($slot);
        $modules = $this->getModules();
        $modules[] = $module;
        $this->setModules($modules);
    }
}
