<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon('heroicon-m-trash')
                ->hiddenLabel()
                ->tooltip('Sil'),
            RestoreAction::make()
                ->icon('heroicon-m-arrow-uturn-left')
                ->hiddenLabel()
                ->tooltip('Geri Yükle'),
        ];
    }
}
