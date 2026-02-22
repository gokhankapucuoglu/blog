<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                if (Auth::user()?->hasRole(['super_admin', 'admin'])) {
                    return $query;
                }
                return $query->where('user_id', Auth::id());
            })
            ->columns([
                ImageColumn::make('image')
                    ->label('Resim')
                    ->disk('public')
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Gorsel+Yok&color=7F9CF5&background=EBF4FF')
                    ->width(120),
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('user.full_name')
                    ->label('Yazar')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-circle')
                    ->color('gray')
                    ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'admin'])),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->state(function (Post $record): string {
                        if ($record->status === 2 && $record->published_at?->isFuture()) {
                            return 'Zamanlandı';
                        }

                        return match ($record->status) {
                            0 => 'Taslak',
                            1 => 'Onay Bekliyor',
                            2 => 'Yayında',
                            3 => 'Reddedildi',
                            default => 'Bilinmiyor',
                        };
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Yayında' => 'success',
                        'Zamanlandı' => 'warning',
                        'Onay Bekliyor' => 'info',
                        'Reddedildi' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('view_count')
                    ->label('Okunma Sayısı')
                    ->icon('heroicon-m-eye')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                ToggleColumn::make('is_featured')
                    ->label('Öne Çıkan')
                    ->onIcon('heroicon-m-star')
                    ->offIcon('heroicon-m-minus')
                    ->onColor('warning')
                    ->offColor('gray')
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title('Vitrin Durumu Güncellendi')
                            ->body($state ? "*{$record->title}* öne çıkanlara eklendi." : "*{$record->title}* öne çıkanlardan kaldırıldı.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'admin'])),
                TextColumn::make('published_at')
                    ->label('Yayın Tarihi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Onayla')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->hiddenLabel()
                    ->tooltip('Yazıyı Onayla ve Yayınla')
                    // Sadece Adminlere ve sadece durumu "Onay Bekliyor" (1) olanlara görünür
                    ->visible(
                        fn(Post $record) =>
                        Auth::user()?->hasRole(['super_admin', 'admin']) && $record->status === 1
                    )
                    ->action(function (Post $record) {
                        $record->update(['status' => 2]);
                        Notification::make()
                            ->title('İşlem Başarılı')
                            ->body("*{$record->title}* onaylandı ve yayına alındı.")
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Düzenle')
                    ->size('lg'),
            ]);
    }
}
