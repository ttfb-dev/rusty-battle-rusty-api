<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
use App\Battle\Modules\BaseModule;

class DamageMultiplicationNextMdlAction extends BaseAction
{
    public function getSort(): int
    {
        return self::SORT_DAMAGE_BONUS;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $modules = $fightRound->getModules();
        $author_modules = $modules[$this->author->toString()];

        if (count($author_modules) === 0) {
            return true;
        }

        $damage_modules = array_filter($author_modules, function (BaseModule $module) {
            return $module->getDamage() > 0;
        });

        $first_damage_module = $damage_modules[0];

        $actions_sorted = $fightRound->getActions();
        foreach ($actions_sorted as $actions) {
            foreach ($actions as $action) {
                if (
                    $action->getModule($battle)->getCode() === $first_damage_module->getCode() &&
                    $action->isActive()
                ) {
                    $action->setDamage($action->getDamage() * 2);
                }
            }
        }

        return true;
    }
}