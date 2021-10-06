<?php


namespace App\Services;


use Symfony\Component\Yaml\Yaml;

class ConfigService
{
    private static $map;

    public static function getGeneral(string $key, $value) {
        return self::getMap()['general'][$key] ?? $value;
    }

    public static function getModuleEnergyCoast(string $module, int $default = 0): int {
        return self::getMap()['module'][$module]['energyCoast'] ?? $default;
    }

    public static function getModuleEnergyBonus(string $module, int $default = 0): int {
        return self::getMap()['module'][$module]['energyBonus'] ?? $default;
    }

    public static function getModuleHealthBonus(string $module, int $default = 0): int {
        return self::getMap()['module'][$module]['healthBonus'] ?? $default;
    }

    public static function getModuleDamage(string $module, int $default = 0): int {
        return self::getMap()['module'][$module]['damage'] ?? $default;
    }

    public static function getModuleName(string $module, string $default = ''): string {
        return self::getMap()['module'][$module]['name'] ?? $default;
    }

    public static function getModuleDescription(string $module, string $default = ''): string {
        return self::getMap()['module'][$module]['description'] ?? $default;
    }

    public static function getModuleLimit(string $module, int $default = 0): int {
        return self::getMap()['module'][$module]['limit'] ?? $default;
    }

    public static function getModuleSlots(string $module, array $default = []): array {
        return self::getMap()['module'][$module]['slots'] ?? $default;
    }

    public static function getModulePassive(string $module, bool $default = false): bool {
        return self::getMap()['module'][$module]['passive'] ?? $default;
    }

    public static function getRobotBaseHealth(string $robot, int $default = 5): int {
        return self::getMap()['robots'][$robot]['baseHealth'] ?? $default;
    }

    public static function getRobotBaseEnergy(string $robot, int $default = 7): int {
        return self::getMap()['robots'][$robot]['baseEnergy'] ?? $default;
    }

    public static function getMap() {
        if (!self::$map) {
            self::$map = Yaml::parse(file_get_contents('/var/www/rusty-api/game-balance.config.yaml'));
        }
        return self::$map;
    }
}
