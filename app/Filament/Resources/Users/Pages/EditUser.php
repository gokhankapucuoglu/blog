<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
                ->tooltip(fn(User $record) => $record->id === Auth::id() ? 'Kendi hesabınızı silemezsiniz.' : 'Sil')
                ->disabled(fn(User $record) => $record->id === Auth::id()),
            RestoreAction::make()
                ->icon('heroicon-m-arrow-uturn-left')
                ->hiddenLabel()
                ->tooltip('Geri Yükle'),
        ];
    }
}
