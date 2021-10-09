<?php


namespace App\Battle\Robots;

use App\Battle\Member;
use App\Battle\Modules\BaseModule;
use Illuminate\Support\Facades\Log;

class UserRobot extends BaseRobot
{
    function getMemberOwner(): string
    {
        return Member::MEMBER_VK;
    }

    function addModule(BaseModule $module, string $slot)
    {
        $module->setId($this->module_id_index);
        $this->module_id_index++;
        $module->setSlot($slot);
        $modules = $this->getModules();
        $modules[$slot] = $module;
        $this->setModules($modules);
        $this->refreshStats();
    }
}
