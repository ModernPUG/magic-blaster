<?php

namespace App;

interface BaseEntity
{
    public function getId(): int;
    public function getRectangle(): Rectangle;
}
