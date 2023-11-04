<?php

namespace Onexer\FilamentTreeTable\Columns\Contracts;

interface Editable
{
    public function validate(mixed $input): void;

    public function updateState(mixed $state): mixed;
}
