<?php

namespace App\Filament\Resources\SupportedLocales;

use App\Filament\Resources\SupportedLocales\Pages\CreateSupportedLocale;
use App\Filament\Resources\SupportedLocales\Pages\EditSupportedLocale;
use App\Filament\Resources\SupportedLocales\Pages\ListSupportedLocales;
use App\Models\Book;
use App\Models\SupportedLocale;
use App\Services\Content\LlmTranslationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SupportedLocaleResource extends Resource
{
    protected static ?string $model = SupportedLocale::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog & Konten';

    protected static ?string $navigationLabel = 'Bahasa & AI Translasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Bahasa (ISO)')
                    ->required()
                    ->placeholder('ja')
                    ->disabled(fn (?SupportedLocale $record) => $record !== null),
                TextInput::make('name')
                    ->label('Nama Bahasa (English)')
                    ->required()
                    ->placeholder('Japanese'),
                TextInput::make('native_name')
                    ->label('Nama Asli (Native Name)')
                    ->required()
                    ->placeholder('日本語'),
                TextInput::make('flag_emoji')
                    ->label('Emoji Bendera')
                    ->required()
                    ->placeholder('🇯🇵'),
                Toggle::make('has_dual_register')
                    ->label('Memiliki Ragam Ganda (Kasual/Standar)')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Aktif di Navbar Global')
                    ->default(false),
                Toggle::make('is_llm_translated')
                    ->label('Diterjemahkan via AI SDK')
                    ->default(false),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flag_emoji')
                    ->label('')
                    ->width('40px'),
                TextColumn::make('code')
                    ->label('Kode')
                    ->fontFamily('mono')
                    ->badge()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama (EN)')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('native_name')
                    ->label('Nama Asli')
                    ->sortable(),
                IconColumn::make('has_dual_register')
                    ->label('Dual Register')
                    ->boolean(),
                ToggleColumn::make('is_active')
                    ->label('Aktif di Navbar'),
                IconColumn::make('is_llm_translated')
                    ->label('AI Translated')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('trigger_ai_translation')
                    ->label('Translasi via AI')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->color('primary')
                    ->form([
                        Select::make('book_id')
                            ->label('Pilih Buku yang Ingin Diterjemahkan')
                            ->options(fn () => Book::query()->pluck('volume_code', 'id'))
                            ->default('book-1')
                            ->required(),
                    ])
                    ->modalHeading('Trigger Pipeline Translasi AI Bahasa Asia')
                    ->modalDescription('Proses ini akan men-dispatch job penerjemahan bab ke Laravel Queue menggunakan Laravel 13 AI SDK (Gemini 3.8 Flash) dan menyimpannya sebagai status REVIEW.')
                    ->action(function (SupportedLocale $record, array $data, LlmTranslationService $translationService) {
                        $count = $translationService->translateBook($data['book_id'], $record->code);
                        $record->is_llm_translated = true;
                        $record->save();

                        Notification::make()
                            ->title('Pipeline AI Berhasil Dijalankan')
                            ->body("Sebanyak {$count} bab dari buku {$data['book_id']} telah dijadwalkan untuk diterjemahkan ke {$record->native_name}.")
                            ->success()
                            ->send();
                    }),
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
            'index' => ListSupportedLocales::route('/'),
            'create' => CreateSupportedLocale::route('/create'),
            'edit' => EditSupportedLocale::route('/{record}/edit'),
        ];
    }
}
