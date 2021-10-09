<?php


namespace App\Battle\Robots;

use App\Battle\Member;
use App\Battle\Modules\BaseModule;

class CoreRobot extends BaseRobot
{
    function getMemberOwner(): string
    {
        return Member::MEMBER_CORE;
    }

    function addModule(BaseModule $module, string $slot)
    {
        $module->setId($this->module_id_index);
        $this->module_id_index++;
        $module->setSlot($slot);
        $modules = $this->getModules();
        $modules[] = $module;
        $this->setModules($modules);
        $this->refreshStats();
    }
}
