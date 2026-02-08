<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->is_super_admin) {
            return parent::getEloquentQuery()->withoutGlobalScopes();
        }

        if (function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }

        return $query;
    }

    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'System';
    
    // Read-only resource
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->is_super_admin;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('action')
                    ->readOnly(),
                Forms\Components\TextInput::make('ip_address')
                    ->readOnly(),
                Forms\Components\KeyValue::make('old_values')
                    ->disabled(),
                Forms\Components\KeyValue::make('new_values')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
                Tables\Columns\TextColumn::make('action')->searchable(),
                Tables\Columns\TextColumn::make('ip_address'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $query = static::getEloquentQuery();
                        
                        $filename = 'audit-logs-' . now()->format('Y-m-d') . '.csv';
                        
                        return response()->streamDownload(function () use ($query) {
                            $handle = fopen('php://output', 'w');
                            
                            // Header row
                            fputcsv($handle, [
                                'ID', 'User', 'Action', 
                                'Old Values', 'New Values',
                                'IP Address', 'Created At'
                            ]);
                            
                            // Data rows
                            $query->chunk(100, function ($logs) use ($handle) {
                                foreach ($logs as $log) {
                                    fputcsv($handle, [
                                        $log->id,
                                        $log->user?->name ?? 'System',
                                        $log->action,
                                        is_array($log->old_values) ? json_encode($log->old_values) : $log->old_values,
                                        is_array($log->new_values) ? json_encode($log->new_values) : $log->new_values,
                                        $log->ip_address,
                                        $log->created_at?->format('Y-m-d H:i:s'),
                                    ]);
                                }
                            });
                            
                            fclose($handle);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
