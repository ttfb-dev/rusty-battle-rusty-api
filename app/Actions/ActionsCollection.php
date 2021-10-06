<?php


namespace App\Actions;


class ActionsCollection
{
    const AVAILABLE_ACTIONS = [
        DamageEnergyAction::class,
        DamageHealthAction::class,
    ];

    public static function getByCode(string $code, array $params): BaseAction {
        $actions = self::getList();
        foreach ($actions as $action) {
            if ($action->getCode() === $code) {
                $class = get_class($action);
                return new $class(...$params);
            }
        }
        throw new \Exception('Неизвестное действие');
    }

    /** @return BaseAction[] */
    public static function getList(): array {
        return array_map(function ($action_class) {
            return new $action_class;
        }, self::AVAILABLE_ACTIONS);
    }
}
