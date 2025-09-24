<?php

namespace BaksDev\Services\Repository\AllServiceEventsByProjectProfile;

use Generator;

interface AllServiceEventsByProjectProfileInterface
{
    public function findAll(): Generator|false;
}