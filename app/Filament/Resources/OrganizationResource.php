<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Platform Administration';

    public static function canViewAny(): bool
    {
        if (! auth()->check()) {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            redirect('/admin/login')->send();
            exit;
        }

        return auth()->user()->is_super_admin;
    }

    public static function canCreate(): bool
    {
        if (! auth()->check()) {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            redirect('/admin/login')->send();
            exit;
        }

        return auth()->user()->is_super_admin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('subdomain')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('custom_domain')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (\Filament\Forms\Get $get) => !app(\App\Services\PlanLimitService::class)->getPlanLimits($get('subscription_plan') ?? 'free')['custom_domain'])
                    ->helperText(fn (\Filament\Forms\Get $get) => !app(\App\Services\PlanLimitService::class)->getPlanLimits($get('subscription_plan') ?? 'free')['custom_domain'] ? 'Requires Basic plan or higher.' : null),
                Forms\Components\FileUpload::make('logo_path')
                    ->image()
                    ->disk('public')
                    ->directory('organizations/logos')
                    ->visibility('public'),
                Forms\Components\Select::make('timezone')
                    ->options(array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers()))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Select::make('subscription_plan')
                    ->options([
                        'free' => 'Free',
                        'basic' => 'Basic',
                        'premium' => 'Premium',
                        'enterprise' => 'Enterprise',
                    ])
                    ->required()
                    ->default('free')
                    ->live(),
                Forms\Components\DatePicker::make('subscription_expires_at'),
                Forms\Components\Toggle::make('sms_enabled')
                    ->disabled(fn (\Filament\Forms\Get $get) => !app(\App\Services\PlanLimitService::class)->getPlanLimits($get('subscription_plan') ?? 'free')['sms_enabled'])
                    ->helperText(fn (\Filament\Forms\Get $get) => !app(\App\Services\PlanLimitService::class)->getPlanLimits($get('subscription_plan') ?? 'free')['sms_enabled'] ? 'Requires Premium plan or higher.' : null),
                Forms\Components\TextInput::make('sms_sender_id')
                    ->maxLength(11),
                Forms\Components\TextInput::make('max_voters')
                    ->numeric()
                    ->default(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('subdomain')->searchable(),
                Tables\Columns\TextColumn::make('custom_domain')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'inactive' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('subscription_plan'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
