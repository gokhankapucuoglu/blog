<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

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
        if (isset($data['menu_type']) && $data['menu_type'] === 'main') {
            $data['parent_id'] = null;
        }

        unset($data['menu_type']);

        if (isset($data['parent_id'])) {
            $record = $this->getRecord();
            $newParentId = (int) $data['parent_id'];

            if ($newParentId === $record->id || in_array($newParentId, $record->getAllChildrenIds())) {
                Notification::make()
                    ->title('Geçersiz İşlem')
                    ->body('Kategori kendi içine veya alt kategorisine taşınamaz.')
                    ->danger()
                    ->send();

                throw new Halt();
            }
        }

        return $data;
    }
}
