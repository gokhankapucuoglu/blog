<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kategori Adı')
                    ->searchable()
                    ->sortable()
                    ->description(function (Category $record): string {
                        if ($record->parent_id === null) {
                            return 'Ana Kategori';
                        }

                        return $record->getHierarchyText();
                    })
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Tür')
                    ->state(fn(Category $record): string => $record->parent_id ? 'Alt Kategori' : 'Ana Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Ana Kategori' => 'warning',
                        'Alt Kategori' => 'info',
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('parent_id', $direction);
                    }),
                ToggleColumn::make('is_visible')
                    ->label('Durum')
                    ->onIcon('heroicon-m-eye')
                    ->offIcon('heroicon-m-eye-slash')
                    ->onColor('success')
                    ->offColor('gray')
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title('Durum Güncellendi')
                            ->body(
                                $state
                                    ? "*{$record->name}* kategorisi görünür hale getirildi."
                                    : "*{$record->name}* kategorisi gizlendi."
                            )
                            ->success()
                            ->send();
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip('Düzenle'),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip(function (Category $record) {
                        if ($record->children()->exists()) {
                            return 'Bu kategoriye bağlı alt kategoriler var. Önce onları silmelisiniz.';
                        }

                        return 'Kategoriyi Sil';
                    })
                    ->disabled(fn(Category $record) => $record->children()->exists()),
                RestoreAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip('Geri Yükle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotification(null)
                        ->action(function (Collection $records) {
                            $deletableRecords = $records->filter(fn($record) => $record->children()->count() === 0);
                            $skippedRecords = $records->reject(fn($record) => $record->children()->count() === 0);
                            $deletableRecords->each->delete();

                            if ($skippedRecords->count() > 0) {
                                Notification::make()
                                    ->title('İşlem Tamamlandı')
                                    ->body(
                                        "Seçilenlerden <b>{$deletableRecords->count()}</b> tanesi silindi.<br>" .
                                            "Ancak <b>{$skippedRecords->count()}</b> kategorinin alt kategorileri olduğu için güvenlik gereği silinmedi."
                                    )
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Seçilen tüm kategoriler başarıyla silindi.')
                                    ->success()
                                    ->send();
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
