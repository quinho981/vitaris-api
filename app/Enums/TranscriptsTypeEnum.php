<?php

namespace App\Enums;

enum TranscriptsTypeEnum: int
{
    case CONSULTA_GERAL = 1;
    case RETORNO = 2;
    case URGENTE = 3;
}