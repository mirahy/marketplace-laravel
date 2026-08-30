<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Pages\ViewActivityLog;
use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getModelLabel(): string
    {
        return __('Log de Atividade');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Logs de Atividade');
    }

    public static function getNavigationLabel(): string
    {
        return __('Logs de Atividade');
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function subjectTypeLabel(?string $state): string
    {
        return match ($state) {
            User::class => __('Usuário'),
            Listing::class => __('Anúncio'),
            Category::class => __('Categoria'),
            Conversation::class => __('Conversa'),
            default => $state ?? '—',
        };
    }

    public static function eventLabel(?string $state): string
    {
        return match ($state) {
            'created' => __('Criado'),
            'updated' => __('Atualizado'),
            'deleted' => __('Excluído'),
            'restored' => __('Restaurado'),
            default => $state ?? '—',
        };
    }

    public static function eventColor(?string $state): string
    {
        return match ($state) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            default => 'gray',
        };
    }
}
