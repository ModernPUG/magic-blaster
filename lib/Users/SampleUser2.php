<?php

declare(strict_types=1);

namespace Users;

class SampleUser2 extends SampleUser
{
    public function getName(): string
    {
        return '샘플유저2';
    }
}
