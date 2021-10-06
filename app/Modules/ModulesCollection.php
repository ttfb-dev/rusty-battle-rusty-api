<?php


namespace App\Modules;


use App\Modules\Core\EnergyCellModule;
use App\Modules\Core\StrengtheningModule;
use App\Modules\Garbage\GarbageModule;
use App\Modules\Weapon\HammerModule;
use App\Modules\Weapon\KnifeModule;
use App\Services\ConfigService;

class ModulesCollection
{
    const MODULES_LIST = [
        GarbageModule::class,
        KnifeModule::class,

        HammerModule::class,

        EnergyCellModule::class,
        StrengtheningModule::class,
    ];

    /** @return BaseModule[] */
    public static function get(): array {
        $modules_list = [];
        foreach (self::MODULES_LIST as $module_class) {
            array_push($modules_list, ...self::generateModuleList($module_class));
        }
        return $modules_list;
    }

    /** @return BaseModule[] */
    public static function getShuffled(): array {
        $modules = self::get();
        shuffle($modules);
        shuffle($modules);
        return $modules;
    }

    /**
     * @param $excluded
     * @return BaseModule[]
     */
    public static function getShuffledWithLimitAndExcluded(array $excluded, int $limit = 3): array {
        return array_slice(
            self::exclude(
                self::getShuffled(),
                $excluded
            ),
            0,
            $limit
        );
    }

    /**
     * @param BaseModule[] $excluded
     * @return BaseModule[]
     */
    public static function getShuffledWithLimitWithoutExcludedAndGarbage(array $excluded, int $limit = 3): array {
        return array_slice(
            self::exclude(
                self::filter(
                    self::getShuffled(),
                    [(new GarbageModule())]
                ),
                $excluded
        ), 0, $limit);
    }

    /** @return BaseModule[] */
    private static function generateModuleList(string $module_class): array {
        $modules = [];
        try {
            $module = (new $module_class);
            if ($module instanceof BaseModule) {
                $limit = ConfigService::getModuleLimit($module->getCode());
                $modules []= $module;
                for ($i = 1; $i < $limit; $i++) {
                    $modules []= (new $module_class);
                }
            }
        } catch(\Throwable $exception) {}

        return $modules;
    }

    /**
     * @param BaseModule[] $modules
     * @param BaseModule[] $modules_to_filter
     * @return BaseModule[]
     */
    public static function filter(array $modules, $modules_to_filter): array {
        foreach ($modules_to_filter as $exclude) {
            foreach ($modules as $key => $module) {
                if ($module->getCode() === $exclude->getCode()) {
                    unset($modules[$key]);
                }
            }
        }

        return array_values($modules);
    }

    /** @param BaseModule[] $modules */
    public static function toArrays(array $modules): array {
        return array_map(function ($module) { return $module->toArray();}, $modules);
    }

    /** @param BaseModule[] $modules */
    public static function toApiArrays(array $modules): array {
        return array_map(function ($module) { return $module->toApi();}, $modules);
    }

    /**
     * @param BaseModule[] $modules
     * @param BaseModule[] $modules_to_exclude
     * @return BaseModule[]
     */
    public static function exclude(array $modules, array $modules_to_exclude): array {
        foreach ($modules_to_exclude as $exclude) {
            $removed = false;
            foreach ($modules as $key => $module) {
                if ($removed) {
                    continue;
                }
                if ($module->getCode() === $exclude->getCode()) {
                    $removed = true;
                    unset($modules[$key]);
                }
            }
        }

       return array_values($modules);
    }

    public static function createFromCode(string $code): ?BaseModule {
        return self::createFromCodes([$code])[0] ?? null;
    }

    /** @return BaseModule[] */
    public static function createFromCodes(array $module_codes): array {
        $created = [];
        $module_list = self::get();
        foreach ($module_codes as $code) {
            foreach ($module_list as $module) {
                if ($module->getCode() === $code) {
                    $created []= $module;
                    break;
                }
            }
        }
        return $created;
    }
}
