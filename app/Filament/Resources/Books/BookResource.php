<?php

namespace App\Filament\Resources\Books;

use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\EditBook;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Models\Book;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog & Konten';

    protected static ?string $navigationLabel = 'Buku Playbook';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('Book ID')
                    ->required()
                    ->placeholder('book-1')
                    ->disabled(fn (?Book $record) => $record !== null),
                TextInput::make('volume_number')
                    ->label('Nomor Volume')
                    ->numeric()
                    ->required(),
                TextInput::make('volume_code')
                    ->label('Kode Volume')
                    ->required()
                    ->placeholder('VOL. 01'),
                ColorPicker::make('accent_hex')
                    ->label('Warna Aksen')
                    ->default('#1D4ED8'),
                TextInput::make('price_single_idr')
                    ->label('Harga Satuan (IDR)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('price_single_usd')
                    ->label('Harga Satuan (USD)')
                    ->numeric()
                    ->prefix('$')
                    ->default(9.99)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
                KeyValue::make('titles')
                    ->label('Judul per Locale:Register')
                    ->helperText('Contoh key: id:casual, id:standard, en:standard')
                    ->required(),
                KeyValue::make('subtitles')
                    ->label('Subjudul per Locale:Register')
                    ->required(),
                TagsInput::make('target_roles')
                    ->label('Target Peran Pembaca'),
                TagsInput::make('key_frameworks')
                    ->label('Kerangka Kerja Kunci'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('volume_code')
                    ->label('Volume')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                TextColumn::make('id')
                    ->label('ID')
                    ->fontFamily('mono'),
                ColorColumn::make('accent_hex')
                    ->label('Warna'),
                TextColumn::make('price_single_idr')
                    ->label('Harga IDR')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('price_single_usd')
                    ->label('Harga USD')
                    ->money('USD')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'edit' => EditBook::route('/{record}/edit'),
        ];
    }
}
