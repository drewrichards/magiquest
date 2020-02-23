<?php

namespace App\Renderers;

interface Renderer
{
    public function __construct();

    public function render(array $state);
}