<?php

namespace Sagar290\Logable\Classes;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Console\Helper\Table;

class LogTableO extends Table
{

    public function __construct($output)
    {
        parent::__construct($output);
    }



}
