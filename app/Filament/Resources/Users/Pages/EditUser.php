<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
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
                ->tooltip('Sil'),
            RestoreAction::make()
                ->icon('heroicon-m-arrow-uturn-left')
                ->hiddenLabel()
                ->tooltip('Geri Yükle'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->getRecord()->id === Auth::id() && isset($data['status']) && ! $data['status']) {
            Notification::make()
                ->title('İşlem Engellendi')
                ->body('Kendi hesabınızı pasif yapamazsınız.')
                ->warning()
                ->send();

            throw new Halt();
        }

        return $data;
    }
}
