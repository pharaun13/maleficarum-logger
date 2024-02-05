<?php

namespace Maleficarum\Logger;

interface RidProvider
{
    public function getRid(): string;
}