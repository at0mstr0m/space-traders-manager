<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum FactionSymbols: string
{
    use EnumUtils;

    case COSMIC = 'COSMIC';
    case VOID = 'VOID';
    case GALACTIC = 'GALACTIC';
    case QUANTUM = 'QUANTUM';
    case DOMINION = 'DOMINION';
    case ASTRO = 'ASTRO';
    case CORSAIRS = 'CORSAIRS';
    case OBSIDIAN = 'OBSIDIAN';
    case AEGIS = 'AEGIS';
    case UNITED = 'UNITED';
    case SOLITARY = 'SOLITARY';
    case COBALT = 'COBALT';
    case OMEGA = 'OMEGA';
    case ECHO = 'ECHO';
    case LORDS = 'LORDS';
    case CULT = 'CULT';
    case ANCIENTS = 'ANCIENTS';
    case SHADOW = 'SHADOW';
    case ETHEREAL = 'ETHEREAL';
}
