<?php

namespace App\Models\Concerns;

use App\Models\ActionLog;

trait LogsActions
{
    public function actionLogs() {
        return $this->morphMany(ActionLog::class, 'subject')->latest();
    }

    public function logAction(string $action, array $properties = [], ?int $dossierId = null): ActionLog
    {
        return $this->actionLogs()->create([
            'dossier_id' => $dossierId,
            'causer_id'  => auth()->id(),
            'action'     => $action,
            'properties' => $properties ?: null,
            'ip'         => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
