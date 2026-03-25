<?php

namespace App\Enums;

enum TranscriptsType: int
{
    case CONSULTA_GERAL = 1;
    case RETORNO = 2;
    case URGENTE = 3;
}