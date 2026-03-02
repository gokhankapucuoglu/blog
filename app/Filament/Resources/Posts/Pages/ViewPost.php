<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\Actions\PostActions;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PostActions::sendToApproval(Action::class),
            PostActions::approve(Action::class),
            PostActions::publishNow(Action::class),
            PostActions::reject(Action::class),
            PostActions::unpublish(Action::class),
        ];
    }
}
