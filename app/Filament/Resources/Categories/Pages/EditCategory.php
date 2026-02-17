<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

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
                ->tooltip(function (Category $record) {
                    if ($record->children()->exists()) {
                        return 'Bu kategoriye bağlı alt kategoriler var. Önce onları silmelisiniz.';
                    }

                    return 'Kategoriyi Sil';
                })
                ->disabled(fn(Category $record) => $record->children()->exists()),
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

        return $data;
    }
}
