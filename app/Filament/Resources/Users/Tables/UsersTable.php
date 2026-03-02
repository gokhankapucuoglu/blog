<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('id', '!=', Auth::id());
            })
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->placeholder('-')
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin'       => 'warning',
                        'editor'      => 'info',
                        'author'      => 'success',
                        'user'        => 'gray',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => __('roles.' . $state)),
                TextColumn::make('username')
                    ->label('Kullanıcı Adı')
                    ->color('gray')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('fullName')
                    ->label('Ad Soyad')
                    ->searchable(['name', 'surname']),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                IconColumn::make('status')
                    ->label('Durum')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Güncellenme Tarihi')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Silinme Tarihi')
                    ->dateTime()
                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Pasif',
                    ]),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip('Düzenle'),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip('Sil'),
                RestoreAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip('Geri Yükle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
