<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Pages\ViewPost;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Newspaper;
    protected static ?string $pluralModelLabel = 'Gönderiler';
    protected static ?string $modelLabel = 'Gönderi';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();
        $user = Auth::user();

        $navigationItems = [
            ViewPost::class,
        ];

        $isAdmin = $user?->hasRole(['super_admin', 'admin']);
        $isOwner = $user?->id === $record->user_id;

        // Sekme kuralı: Admin her zaman, yazar taslak ve ret ise düzenleme sekmesini görür
        if ($isAdmin || ($isOwner && in_array($record->status, [0, 3]))) {
            $navigationItems[] = EditPost::class;
        }

        return $page->generateNavigationItems($navigationItems);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view' => ViewPost::route('/{record}'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->columnSpanFull()->components([
                Group::make()->columnSpan(2)->components([
                    Section::make('Gönderi Detayları')
                        ->icon('heroicon-m-information-circle')
                        ->components([
                            Grid::make(3)->components([
                                Group::make()->columnSpan(2)->components([
                                    TextEntry::make('title')
                                        ->label('Başlık')
                                        ->weight('bold')
                                        ->columnSpanFull(),
                                    Grid::make(2)
                                        ->components([
                                            TextEntry::make('user.full_name')
                                                ->label('Yazar')
                                                ->color('gray'),
                                            TextEntry::make('category.name')
                                                ->label('Kategori')
                                                ->color('gray'),
                                            TextEntry::make('status')
                                                ->label('Durum')
                                                ->icon(fn(Post $record): string => $record->status_icon)
                                                ->formatStateUsing(fn(Post $record): string => $record->status_label)
                                                ->badge()
                                                ->color(fn(Post $record): string => $record->status_color),
                                            TextEntry::make('published_at')
                                                ->label(fn(Post $record): string => $record->status_label === 'Yayında' ? 'Yayın Tarihi' : 'Yayınlanacak Tarih')
                                                ->icon(fn(Post $record): string => $record->status_icon)
                                                ->dateTime('d/m/Y H:i')
                                                ->badge()
                                                ->color(fn(Post $record): string => $record->status_color),
                                            Grid::make(3)->components([
                                                TextEntry::make('view_count')->hiddenLabel()->icon('heroicon-m-eye')->iconColor('info')->color('info'),
                                                TextEntry::make('like_count')->hiddenLabel()->icon('heroicon-m-heart')->iconColor('danger')->color('danger'),
                                            ]),
                                        ]),
                                ]),

                                Group::make()->columnSpan(1)->components([
                                    ImageEntry::make('image')
                                        ->hiddenLabel()
                                        ->defaultImageUrl(url("https://placehold.co/800x500/f8f9fa/a1a1aa?text=Gorsel+Yok"))
                                        ->disk('public')
                                        ->imageWidth('100%'),
                                    TextEntry::make('tags')
                                        ->label('Etiketler')
                                        ->badge()
                                        ->separator(',')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        ]),

                    Section::make('Yazı İçeriği')
                        ->icon('heroicon-m-document-text')
                        ->collapsible()
                        ->components([
                            TextEntry::make('content')
                                ->hiddenLabel()
                                ->html(),
                        ]),
                ]),

                Group::make()->columnSpan(1)->components([
                    Section::make('İşlem Geçmişi')
                        ->icon('heroicon-m-document-magnifying-glass')
                        ->components([
                            RepeatableEntry::make('histories')
                                ->hiddenLabel()
                                ->components([
                                    TextEntry::make('action')
                                        ->hiddenLabel()
                                        ->badge()
                                        ->color(fn($state): string => match ($state) {
                                            'Planlandı', 'Yayınlandı', 'Hemen Yayınlandı'               => 'success',
                                            'Oluşturuldu', 'Güncellendi'                                => 'info',
                                            'Onaya Gönderildi'                                          => 'warning',
                                            'Reddedildi', 'Planlama İptal Edildi', 'Yayından Çekildi'   => 'danger',
                                            default                                                     => 'gray',
                                        }),
                                    TextEntry::make('created_at')
                                        ->label('Tarih')
                                        ->icon('heroicon-m-calendar-days')
                                        ->dateTime('d M Y - H:i'),
                                    TextEntry::make('user.full_name')
                                        ->label('İşlemi Yapan')
                                        ->icon('heroicon-s-user-circle'),
                                    TextEntry::make('user.roles.name')
                                        ->label('Kullanıcı Rolü')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'super_admin', 'admin' => 'danger',
                                            'editor'               => 'warning',
                                            'author'               => 'info',
                                            default                => 'gray',
                                        })
                                        ->formatStateUsing(fn(string $state): string => __("roles.{$state}")),
                                    TextEntry::make('description')
                                        ->label('Açıklama')
                                        ->color('gray'),
                                ])
                                ->columns(1),
                        ]),
                ]),
            ]),
        ]);
    }
}