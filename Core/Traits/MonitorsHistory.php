<?php

namespace App\Core\Traits;

use App\Models\History;

/**
 * Só deve ser usado em modelos que precisem de acesso rápido e não estático a estas funcionalidades.
 */
trait MonitorsHistory
{
    // TODO: Melhorar isto para receber os objetos diretamente mas não há tempo e nem todos estão "standardizados".
    /**
     * Publica um novo evento no histórico.
     * @param string $message A mensagem de histórico.
     * @param string $action A ação tomada (create, update, delete)
     * @param string $type O tipo de objeto em que a ação foi tomada
     * @param int $target O ID do objeto onde a ação foi tomada
     * @throws \Exception
     */
    protected function publishEvent(string $message, string $action, string $type, int $target): bool
    {
        return new History($action, $type, $message, current_id(), $target)->save();
    }
}