<?php

namespace SpacesAPI;

trait StringFunctions
{
    public function pascalCaseToCamelCase(string $name): string
    {
        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $name));
    }
}
