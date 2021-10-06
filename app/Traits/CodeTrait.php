<?php


namespace App\Traits;


trait CodeTrait
{
    public function getCode(): string {
        return str_replace(
            ['module', 'action', 'robot'],
            '',
            strtolower(
                (new \ReflectionClass($this))->getShortName()
            )
        );
    }
}
