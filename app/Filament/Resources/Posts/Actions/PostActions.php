<?php

namespace App\Filament\Resources\Posts\Actions;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\PostHistory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostActions
{
    public static function sendToApproval(string $action)
    {
        return $action::make('send_to_approval')
            ->label('Onaya Gönder')
            ->icon('heroicon-m-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading(fn(Post $record) => $record->title)
            ->modalDescription('Gönderiniz admin onayına sunulacak ve bu süreçte gönderi üzerinde düzenleme yapamayacaksınız.')
            ->visible(fn(Post $record) => ! Auth::user()?->hasRole(['super_admin', 'admin']) && in_array($record->status, [0, 3]))
            ->action(function (Post $record) {
                DB::beginTransaction();

                try {
                    $record->update(['status' => 1]);

                    PostHistory::create([
                        'post_id'     => $record->id,
                        'user_id'     => Auth::id(),
                        'action'      => 'Onaya Gönderildi',
                        'description' => 'Admin onayına sunuldu.',
                    ]);

                    Notification::make()
                        ->title('Gönderiniz admin onayına sunulmuştur.')
                        ->success()
                        ->send();

                    $admins = User::role(['super_admin', 'admin'])->get();
                    Notification::make()
                        ->title('Gönderi Onay Bekliyor')
                        ->body("{$record->user->full_name}, *{$record->title}* başlıklı gönderisini onaya gönderdi.")
                        ->info()
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->label('Görüntüle')
                                ->url(PostResource::getUrl('view', ['record' => $record])),
                            Action::make('markAsRead')->label('Okundu')->color('secondary')->markAsRead(),
                        ])
                        ->sendToDatabase($admins);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Sistemsel bir hata oluştu!')
                        ->body('İşlem tamamlanamadı ve değişiklikler geri alındı. Lütfen yöneticiye bildirin.')
                        ->danger()
                        ->send();

                    Log::error('Hata : ' . $e->getMessage());
                }
            });
    }

    public static function approve(string $action)
    {
        return $action::make('approve')
            ->label(fn(Post $record) => $record->published_at?->isFuture() ? 'Planlamayı Onayla' : 'Onayla')
            ->icon(fn(Post $record) => $record->published_at?->isFuture() ? 'heroicon-m-calendar-days' : 'heroicon-m-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(fn(Post $record) => $record->title)
            ->modalDescription(
                fn(Post $record) => $record->published_at?->isFuture()
                    ? 'Bu gönderi ileri bir tarih için planlanmış. Onaylarsanız gönderi planlandığı gibi belirtilen tarihte otomatik olarak yayınlanacaktır. Onaylamak istediğinize emin misiniz?'
                    : 'Bu gönderiyi onaylarsanız hemen yayınlanacaktır. Onaylamak istediğinize emin misiniz?'
            )
            ->visible(fn(Post $record) => Auth::user()?->hasRole(['super_admin', 'admin']) && $record->status !== 2)
            ->action(function (Post $record) {
                DB::beginTransaction();

                try {
                    $isFuture = $record->published_at?->isFuture();
                    $publishedAt = $isFuture ? $record->published_at : now();

                    $record->update([
                        'status' => 2,
                        'published_at' => $publishedAt,
                    ]);

                    PostHistory::create([
                        'post_id'     => $record->id,
                        'user_id'     => Auth::id(),
                        'action'      => $isFuture ? 'Planlandı' : 'Yayınlandı',
                        'description' => $isFuture
                            ? "Onaylandı ve {$publishedAt->format('d/m/Y H:i')} tarihinde yayınlanacak."
                            : 'Onaylandı ve yayınlandı.',
                    ]);

                    Notification::make()
                        ->title($isFuture ? 'Gönderi Onaylandı ve Planlandı' : 'Gönderi Onaylandı ve Yayınlandı.')
                        ->success()
                        ->send();

                    if (Auth::id() !== $record->user_id) {
                        Notification::make()
                            ->title($isFuture ? 'Gönderiniz Onaylandı ve Planlandı' : 'Gönderiniz Onaylandı ve Yayınlandı')
                            ->body(
                                $isFuture
                                    ? "*{$record->title}* adlı gönderiniz onaylandı. {$publishedAt->format('d/m/Y H:i')} tarihinde otomatik olarak yayınlanacak."
                                    : "*{$record->title}* adlı gönderiniz onaylandı ve yayınlandı."
                            )
                            ->success()
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->label('Görüntüle')
                                    ->url(PostResource::getUrl('view', ['record' => $record])),
                                Action::make('markAsRead')->label('Okundu')->color('secondary')->markAsRead(),
                            ])
                            ->sendToDatabase($record->user);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Sistemsel bir hata oluştu!')
                        ->body('İşlem tamamlanamadı ve değişiklikler geri alındı. Lütfen yöneticiye bildirin.')
                        ->danger()
                        ->send();

                    Log::error('Hata : ' . $e->getMessage());
                }
            });
    }

    public static function publishNow(string $action)
    {
        return $action::make('publish_now')
            ->label('Hemen Yayınla')
            ->icon('heroicon-m-bolt')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(fn(Post $record) => $record->title)
            ->modalDescription('Bu gönderi ileri bir tarih için planlanmış. Planlamayı iptal hemen yayına almak istediğinize emin misiniz?')
            ->visible(fn(Post $record) => Auth::user()?->hasRole(['super_admin', 'admin']) && $record->status === 2 && $record->published_at?->isFuture())
            ->action(function (Post $record) {
                DB::beginTransaction();

                try {
                    $record->update([
                        'status' => 2,
                        'published_at' => now(),
                    ]);

                    PostHistory::create([
                        'post_id'     => $record->id,
                        'user_id'     => Auth::id(),
                        'action'      => 'Hemen Yayınlandı',
                        'description' => 'İleri tarihli planlama iptal edildi ve hemen yayınlandı.',
                    ]);

                    Notification::make()
                        ->title('Gönderi hemen yayınlandı.')
                        ->success()
                        ->send();

                    if (Auth::id() !== $record->user_id) {
                        Notification::make()
                            ->title('Gönderiniz Hemen Yayına Alındı')
                            ->body("*{$record->title}* adlı gönderinizin ileri tarihli planlaması iptal edilerek admin tarafından hemen yayınlandı.")
                            ->success()
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->label('Görüntüle')
                                    ->url(PostResource::getUrl('view', ['record' => $record])),
                                Action::make('markAsRead')
                                    ->label('Okundu')
                                    ->color('secondary')
                                    ->markAsRead(),
                            ])
                            ->sendToDatabase($record->user);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Sistemsel bir hata oluştu!')
                        ->body('İşlem tamamlanamadı ve değişiklikler geri alındı. Lütfen yöneticiye bildirin.')
                        ->danger()
                        ->send();

                    Log::error('Hata : ' . $e->getMessage());
                }
            });
    }

    public static function reject(string $action)
    {
        return $action::make('reject')
            ->label('Reddet')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->visible(fn(Post $record) => Auth::user()?->hasRole(['super_admin', 'admin']) && $record->status === 1)
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Reddetme Sebebi')
                    ->placeholder('Yazara iletilmek üzere ret sebebini yazın...')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (Post $record, array $data) {
                DB::beginTransaction();

                try {
                    $record->update(['status' => 3]);

                    PostHistory::create([
                        'post_id'     => $record->id,
                        'user_id'     => Auth::id(),
                        'action'      => 'Reddedildi',
                        'description' => 'Sebebi: ' . $data['rejection_reason'],
                    ]);

                    Notification::make()
                        ->title('Gönderi reddedildi.')
                        ->success()
                        ->send();

                    if (Auth::id() !== $record->user_id) {
                        Notification::make()
                            ->title('Gönderiniz Reddedildi')
                            ->body("*{$record->title}* adlı gönderiniz reddedildi. \n\n**Sebep:** {$data['rejection_reason']}")
                            ->danger()
                            ->actions([
                                Action::make('edit')
                                    ->button()
                                    ->label('Düzenle')
                                    ->url(PostResource::getUrl('view', ['record' => $record])),
                                Action::make('markAsRead')
                                    ->label('Okundu')
                                    ->color('secondary')
                                    ->markAsRead(),
                            ])
                            ->sendToDatabase($record->user);
                    }

                    $record->refresh();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Sistemsel bir hata oluştu!')
                        ->body('İşlem tamamlanamadı ve değişiklikler geri alındı. Lütfen yöneticiye bildirin.')
                        ->danger()
                        ->send();

                    Log::error('Hata : ' . $e->getMessage());
                }
            });
    }

    public static function unpublish(string $actionClass)
    {
        return $actionClass::make('unpublish')
            ->label(fn(Post $record) => $record->published_at?->isFuture() ? 'Planlamayı İptal Et' : 'Yayından Çek')
            ->icon(fn(Post $record) => $record->published_at?->isFuture() ? 'heroicon-m-calendar-days' : 'heroicon-m-arrow-down-tray')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(fn(Post $record) => $record->title)
            ->modalDescription(
                fn(Post $record) => $record->published_at?->isFuture()
                    ? 'Bu gönderinin ileri tarihli planlaması iptal edilecek ve taslak durumuna geri alınacaktır. Tekrar yayınlanması için onaya gönderilmesi gerekecektir. Yayından çekmek istediğinize emin misiniz?'
                    : 'Bu gönderi yayından kaldırılıp taslak durumuna alınacak. Tekrar yayınlanması için onaya gönderilmesi gerekecektir. Yayından çekmek istediğinize emin misiniz?'
            )
            ->visible(fn(Post $record) => Auth::user()?->hasRole(['super_admin', 'admin']) && $record->status === 2)
            ->action(function (Post $record) {
                DB::beginTransaction();

                try {
                    $isFuture = $record->published_at?->isFuture();

                    $record->update([
                        'status' => 0,
                        'published_at' => null,
                    ]);

                    PostHistory::create([
                        'post_id'     => $record->id,
                        'user_id'     => Auth::id(),
                        'action'      => $isFuture ? 'Planlama İptal Edildi' : 'Yayından Çekildi',
                        'description' => $isFuture
                            ? 'İleri tarihli planlama iptal edildi ve taslağa alındı.'
                            : 'Yayından çekildi ve taslağa alındı.'
                    ]);

                    Notification::make()
                        ->title($isFuture ? 'Gönderi planlaması iptal edildi ve taslağa alındı.' : 'Gönderi yayından çekildi ve taslağa alındı.')
                        ->success()
                        ->send();

                    if (Auth::id() !== $record->user_id) {
                        Notification::make()
                            ->title($isFuture ? 'Gönderinizin Planlaması İptal Edildi' : 'Gönderiniz Yayından Çekildi')
                            ->body(
                                $isFuture
                                    ? "*{$record->title}* adlı gönderinizin ileri tarihli yayın planlaması admin tarafından iptal edilerek taslağa alındı."
                                    : "*{$record->title}* adlı gönderiniz yayından çekilerek taslağa alındı."
                            )
                            ->warning()
                            ->actions([
                                Action::make('view')
                                    ->button()
                                    ->label('Görüntüle')
                                    ->url(PostResource::getUrl('view', ['record' => $record])),
                                Action::make('markAsRead')
                                    ->label('Okundu')
                                    ->color('secondary')
                                    ->markAsRead(),
                            ])
                            ->sendToDatabase($record->user);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Sistemsel bir hata oluştu!')
                        ->body('İşlem tamamlanamadı ve değişiklikler geri alındı. Lütfen yöneticiye bildirin.')
                        ->danger()
                        ->send();

                    Log::error('Hata : ' . $e->getMessage());
                }
            });
    }
}
