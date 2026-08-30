<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsAllChanges
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return $this->baseActivitylogOptions();
    }

    protected function baseActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => __('criado'),
                'updated' => __('atualizado'),
                'deleted' => __('excluído'),
                'restored' => __('restaurado'),
                default => $eventName,
            });
    }
}
