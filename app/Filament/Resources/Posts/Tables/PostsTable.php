<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Posts\Actions\PostActions;
use Filament\Actions\Action;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn(Post $record): string => PostResource::getUrl('view', ['record' => $record]),
            )
            ->modifyQueryUsing(function ($query) {
                $user = Auth::user();

                if ($user?->hasRole('super_admin')) {
                    return $query;
                }

                if ($user?->hasRole('admin')) {
                    return $query->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhereHas('user', function ($userQuery) {
                                $userQuery->whereDoesntHave('roles', function ($roleQuery) {
                                    $roleQuery->whereIn('name', ['super_admin', 'admin']);
                                });
                            });
                    });
                }

                return $query->where('user_id', $user->id);
            })
            ->columns([
                ImageColumn::make('image')
                    ->label('Resim')
                    ->disk('public')
                    ->defaultImageUrl('https://placehold.co/800x500/1a1a1a/ffffff?text=Gorsel+Yok')
                    ->width(120),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('user.full_name')
                    ->label('Yazar')
                    ->searchable(['name', 'surname'])
                    ->sortable(['name', 'surname'])
                    ->color('gray')
                    ->visible(function ($livewire) {
                        if (! Auth::user()?->hasRole(['super_admin', 'admin'])) {
                            return false;
                        }

                        if (property_exists($livewire, 'activeTab') && $livewire->activeTab === 'my_posts') {
                            return false;
                        }

                        return true;
                    }),
                TextColumn::make('published_at')
                    ->label('Yayın/Plan Tarihi')
                    ->badge()
                    ->icon(fn(Post $record): string => $record->status_icon)
                    ->color(fn(Post $record): string => $record->status_color)
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->icon(fn(Post $record): string => $record->status_icon)
                    ->formatStateUsing(fn(Post $record): string => $record->status_label)
                    ->color(fn(Post $record): string => $record->status_color)
                    ->sortable(),
                TextColumn::make('view_count')
                    ->label('Görüntülenme')
                    ->color('info')
                    ->icon('heroicon-m-eye')
                    ->iconColor('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('like_count')
                    ->label('Beğeni')
                    ->color('danger')
                    ->icon('heroicon-m-heart')
                    ->iconColor('danger')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'admin']))
                    ->toggleable(isToggledHiddenByDefault: true),
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
                ActionGroup::make([
                    ViewAction::make(),
                    PostActions::sendToApproval(Action::class),
                    PostActions::approve(Action::class),
                    PostActions::publishNow(Action::class),
                    PostActions::reject(Action::class),
                    PostActions::unpublish(Action::class),
                    EditAction::make()
                        ->visible(function (Post $record) {
                            $user = Auth::user();
                            $isAdmin = $user?->hasRole(['super_admin', 'admin']);
                            $isOwner = $user?->id === $record->user_id;

                            // Admin her zaman düzenleyebilir. Yazar sadece taslak ve ret ise düzenleyebilir.
                            return $isAdmin || ($isOwner && in_array($record->status, [0, 3]));
                        }),
                    DeleteAction::make()
                        ->visible(function (Post $record) {
                            $user = Auth::user();
                            $isAdmin = $user?->hasRole(['super_admin', 'admin']);
                            $isOwner = $user?->id === $record->user_id;

                            // Admin her zaman silebilir. Yazar sadece taslak ve ret ise silebilir.
                            return $isAdmin || ($isOwner && in_array($record->status, [0, 3]));
                        }),
                ]),
            ]);
    }
}
