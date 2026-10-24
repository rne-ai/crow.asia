<?php

namespace App\Filament\Resources\PricingTiers;

use App\Filament\Resources\PricingTiers\Pages\CreatePricingTier;
use App\Filament\Resources\PricingTiers\Pages\EditPricingTier;
use App\Filament\Resources\PricingTiers\Pages\ListPricingTiers;
use App\Models\Book;
use App\Models\PricingTier;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricingTierResource extends Resource
{
    protected static ?string $model = PricingTier::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Bisnis & Penjualan';

    protected static ?string $navigationLabel = 'Paket Harga';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('Tier ID')
                    ->required()
                    ->placeholder('tier-book-1')
                    ->disabled(fn (?PricingTier $record) => $record !== null),
                KeyValue::make('names')
                    ->label('Nama Paket (id / en)')
                    ->required(),
                KeyValue::make('descriptions')
                    ->label('Deskripsi (id / en)')
                    ->required(),
                TextInput::make('price_idr')
                    ->label('Harga IDR')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('strike_price_idr')
                    ->label('Harga Coret IDR (Opsional)')
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('price_usd')
                    ->label('Harga USD')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Select::make('included_book_ids')
                    ->label('Buku yang Termasuk')
                    ->multiple()
                    ->options(fn () => Book::query()->pluck('volume_code', 'id'))
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(1),
                Toggle::make('is_best_value')
                    ->label('Tandai sebagai Best Value / Pilihan Terbaik')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                TagsInput::make('features')
                    ->label('Daftar Fitur Paket'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Tier ID')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('price_idr')
                    ->label('Harga IDR')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('strike_price_idr')
                    ->label('Harga Coret')
                    ->money('IDR', locale: 'id_ID'),
                TextColumn::make('price_usd')
                    ->label('Harga USD')
                    ->money('USD'),
                IconColumn::make('is_best_value')
                    ->label('Best Value')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListPricingTiers::route('/'),
            'create' => CreatePricingTier::route('/create'),
            'edit' => EditPricingTier::route('/{record}/edit'),
        ];
    }
}
