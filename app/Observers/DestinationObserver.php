<?php

namespace App\Observers;

use App\Models\Destination;

class DestinationObserver
{
    public function created(Destination $destination): void
    {
        \App\Models\ActivityLog::log(
            'create',
            "Menambahkan destinasi baru: {$destination->name}",
            $destination,
            null,
            $destination->toArray()
        );
    }

    public function updated(Destination $destination): void
    {
        \App\Models\ActivityLog::log(
            'update',
            "Mengupdate destinasi: {$destination->name}",
            $destination,
            $destination->getOriginal(),
            $destination->getChanges()
        );
    }

    public function deleted(Destination $destination): void
    {
        \App\Models\ActivityLog::log(
            'delete',
            "Menghapus destinasi: {$destination->name}",
            $destination,
            $destination->toArray(),
            null
        );
    }
}
