<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\CursorPaginator;

/**
 * Trait HasCursorPagination
 *
 * Estandariza la paginación determinista de alto rendimiento (Keyset Pagination)
 * en controladores Eloquent de Project Saavedra.
 */
trait HasCursorPagination
{
    /**
     * Aplica paginación determinista por cursor con orden estable y preservación de query strings.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param int $perPage
     * @param string $orderColumn
     * @param string $direction
     * @param array $columns
     * @return CursorPaginator
     */
    public function paginateCursor(
        $query,
        int $perPage = 25,
        string $orderColumn = 'id',
        string $direction = 'desc',
        array $columns = ['*']
    ): CursorPaginator {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        if ($direction === 'desc') {
            $query->orderByDesc($orderColumn);
        } else {
            $query->orderBy($orderColumn);
        }

        // Si la columna de orden no es 'id', agregar 'id' como desempate determinista
        if ($orderColumn !== 'id') {
            $query->orderBy('id', $direction);
        }

        return $query->cursorPaginate($perPage, $columns)->withQueryString();
    }
}
