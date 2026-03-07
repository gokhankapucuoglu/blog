<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Comment;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManagePostComments extends ManageRelatedRecords
{
    protected static string $resource = PostResource::class;

    protected static string $relationship = 'comments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftRight;
    protected static ?string $pluralModelLabel = 'Yorumlar';
    protected static ?string $modelLabel = 'Yorum';

    public static function getNavigationLabel(): string
    {
        return 'Yorumlar';
    }

    public function getBreadcrumb(): string
    {
        return 'Yorumlar';
    }

    public function getHeading(): string
    {
        return '';
    }

    public static function getNavigationBadge(): ?string
    {
        $slug = request()->route('record');
        if (! $slug) return null;

        $post = Post::where('slug', $slug)->first();
        if (! $post) return null;

        $pendingCount = $post->comments()
            ->where('status', Comment::STATUS_PENDING)
            ->count();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $slug = request()->route('record');
        if (! $slug) return null;

        $post = Post::where('slug', $slug)->first();
        if (! $post) return null;

        $pendingCount = $post->comments()
            ->where('status', Comment::STATUS_PENDING)
            ->count();

        return $pendingCount > 0 ? 'warning' : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Yorum Sahibi')
                    ->searchable(['name', 'email']),
                TextColumn::make('type')
                    ->label('Tür')
                    ->state(fn(Comment $record): string => is_null($record->parent_id) ? 'Ana Yorum' : 'Yanıt')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Ana Yorum' => 'primary',
                        'Yanıt'     => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Ana Yorum' => 'heroicon-m-chat-bubble-bottom-center-text',
                        'Yanıt'     => 'heroicon-m-arrow-turn-down-right',
                    }),
                TextColumn::make('body')
                    ->label('Yorum')
                    ->formatStateUsing(function (Comment $record): string {
                        $indent = $record->parent_id ? '↳ ' : '';
                        return $indent . Str::limit($record->body, 80);
                    })
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->icon(fn(Comment $record): string => $record->status_icon)
                    ->color(fn(Comment $record): string => $record->status_color)
                    ->formatStateUsing(fn(Comment $record): string => $record->status_label),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        Comment::STATUS_APPROVED => 'Onaylandı',
                        Comment::STATUS_PENDING  => 'Onay Bekliyor',
                        Comment::STATUS_REJECTED => 'Reddedildi',
                        Comment::STATUS_SPAM     => 'Spam',
                    ]),
                TernaryFilter::make('type')
                    ->label('Tür')
                    ->placeholder('Tümü')
                    ->trueLabel('Sadece Ana Yorumlar')
                    ->falseLabel('Sadece Yanıtlar')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNull('parent_id'),
                        false: fn(Builder $query) => $query->whereNotNull('parent_id'),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('changeStatus')
                    ->hiddenLabel()
                    ->tooltip('Durum Değiştir')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->color('info')
                    ->modalIcon('heroicon-m-arrow-path')
                    ->modalWidth('md')
                    ->modalHeading('Yorum Durumunu Değiştir')
                    ->modalDescription('Bu yorumun durumunu aşağıdan seçerek güncelleyebilirsiniz.')
                    ->modalSubmitActionLabel('Kaydet')
                    ->fillForm(fn(Comment $record): array => [
                        'status' => $record->status,
                    ])
                    ->schema([
                        Select::make('status')
                            ->hiddenLabel()
                            ->options([
                                Comment::STATUS_PENDING  => 'Onay Bekliyor',
                                Comment::STATUS_APPROVED => 'Onayla',
                                Comment::STATUS_REJECTED => 'Reddet',
                                Comment::STATUS_SPAM     => 'Spam',
                            ])
                            ->required()
                            ->default(fn(Comment $record) => $record->status)
                            ->selectablePlaceholder(false),
                    ])
                    ->action(function (Comment $record, array $data): void {
                        $record->update(['status' => $data['status']]);

                        Notification::make()
                            ->title('Yorum durumu güncellendi.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeStatusBulk')
                        ->label('Durumları Güncelle')
                        ->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->modalIcon('heroicon-m-arrow-path')
                        ->modalWidth('md')
                        ->modalHeading('Toplu Durum Güncellemesi')
                        ->modalDescription('Seçili yorumların yeni durumunu belirleyin.')
                        ->modalSubmitActionLabel('Toplu Güncelle')
                        ->schema([
                            Select::make('status')
                                ->label('Yeni Durum')
                                ->options([
                                    Comment::STATUS_PENDING  => 'Onay Bekliyor',
                                    Comment::STATUS_APPROVED => 'Onayla',
                                    Comment::STATUS_REJECTED => 'Reddet',
                                    Comment::STATUS_SPAM     => 'Spam Olarak İşaretle',
                                ])
                                ->required()
                                ->selectablePlaceholder(false),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            DB::beginTransaction();

                            try {
                                $records->each(function ($record) use ($data) {
                                    $record->update(['status' => $data['status']]);
                                });

                                DB::commit();

                                Notification::make()
                                    ->title('Seçili yorumlar güncellendi.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                DB::rollBack();

                                Notification::make()
                                    ->title('Toplu işlem sırasında bir sorun oluştu.')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
                    ->reorder()
                    ->orderByRaw('COALESCE(parent_id, id) DESC')
                    ->orderByRaw('parent_id IS NOT NULL ASC')
                    ->orderBy('created_at', 'ASC')
            );
    }
}
