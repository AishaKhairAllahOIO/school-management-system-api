<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getExceptionCode(Exception $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        $code = $e->getCode();

        if (is_numeric($code) && $code >= 400 && $code < 600) {
            return (int) $code;
        }

        return 500;
    }
}
