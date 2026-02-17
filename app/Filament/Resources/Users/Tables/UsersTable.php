<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'super_admin' => 'Süper Admin',
                        'admin'       => 'Admin',
                        'editor'      => 'Editör',
                        'author'      => 'Yazar',
                        'user'        => 'Kullanıcı',
                        default       => $state,
                    }),
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
                IconColumn::make('is_active')
                    ->label('Aktif Mi?')
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
                SelectFilter::make('is_active')
                    ->label('Aktiflik Durumu')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Pasif',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip('Düzenle'),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->size('lg')
                    ->tooltip(fn(User $record) => $record->id === Auth::id() ? 'Kendi hesabınızı silemezsiniz.' : 'Sil')
                    ->disabled(fn(User $record) => $record->id === Auth::id()),
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
                            $others = $records->reject(fn($user) => $user->id === Auth::id());

                            $others->each->delete();

                            if ($records->count() !== $others->count()) {
                                Notification::make()
                                    ->title('Diğer kullanıcılar silindi.')
                                    ->body('Güvenlik gereği kendi hesabınız silinmedi.')
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Seçilen tüm kullanıcılar silindi')
                                    ->success()
                                    ->send();
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
