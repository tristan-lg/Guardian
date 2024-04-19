<?php

namespace App\Entity\Interface;

interface NameableEntityInterface
{
    public static function getSingular(): string;
    public static function getPlural(): string;

    //TODO
}
