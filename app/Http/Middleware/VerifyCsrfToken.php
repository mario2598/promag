<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'proyectos/*',
        'mant/*',
        'gastos/*',
        'ingresos/*',
        'cxp/*',
        'informes/*',
        'perfil/*',
        'tm/*',
        'side_teme',
        'color_teme',
        'sticky',
        'tema_claro',
        'tema_oscuro',
        'restaurar_pc'
    ];
}
