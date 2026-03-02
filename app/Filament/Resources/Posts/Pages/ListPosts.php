<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Yeni Gönderi')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        $isAdmin = $user?->hasRole(['super_admin', 'admin']);

        if ($isAdmin) {
            $onlyAuthorsQuery = function (Builder $query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['super_admin', 'admin']);
                    });
                });
            };

            return [
                'my_posts' => Tab::make('Gönderilerim')
                    ->icon('heroicon-m-user')
                    ->badge(fn() => Post::where('user_id', $user->id)->count())
                    ->badgeColor('info')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', $user->id)),
                'pending_approval' => Tab::make('Onay Bekleyenler')
                    ->icon('heroicon-m-question-mark-circle')
                    ->badge(fn() => Post::where('status', 1)->where($onlyAuthorsQuery)->count())
                    ->badgeColor('warning')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 1)->where($onlyAuthorsQuery)),
                'approved' => Tab::make('Onaylananlar')
                    ->icon('heroicon-m-check-circle')
                    ->badge(fn() => Post::where('status', 2)->where($onlyAuthorsQuery)->count())
                    ->badgeColor('success')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 2)->where($onlyAuthorsQuery)),
                'rejected' => Tab::make('Reddedilenler')
                    ->icon('heroicon-m-x-circle')
                    ->badge(fn() => Post::where('status', 3)->where($onlyAuthorsQuery)->count())
                    ->badgeColor('danger')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 3)->where($onlyAuthorsQuery)),
                'drafts' => Tab::make('Taslaklar')
                    ->icon('heroicon-m-document-text')
                    ->badge(fn() => Post::where('status', 0)->where($onlyAuthorsQuery)->count())
                    ->badgeColor('gray')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 0)->where($onlyAuthorsQuery)),
            ];
        }

        return [
            'all' => Tab::make('Tümü')
                ->icon('heroicon-m-list-bullet')
                ->badge(fn() => Post::where('user_id', $user->id)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', $user->id)),
            'my_pending' => Tab::make('Onaya Gönderilenler')
                ->icon('heroicon-m-question-mark-circle')
                ->badge(fn() => Post::where('user_id', $user->id)->where('status', 1)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 1)),
            'my_approved' => Tab::make('Onaylananlar')
                ->icon('heroicon-m-check-circle')
                ->badge(fn() => Post::where('user_id', $user->id)->where('status', 2)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 2)),
            'my_rejected' => Tab::make('Reddedilenler')
                ->icon('heroicon-m-x-circle')
                ->badge(fn() => Post::where('user_id', $user->id)->where('status', 3)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 3)),
            'my_drafts' => Tab::make('Taslaklar')
                ->icon('heroicon-m-document-text')
                ->badge(fn() => Post::where('user_id', $user->id)->where('status', 0)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 0)),
        ];
    }
}
