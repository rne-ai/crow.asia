<?php

namespace App\Filament\Resources\Chapters;

use App\Filament\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Resources\Chapters\Pages\EditChapter;
use App\Filament\Resources\Chapters\Pages\ListChapters;
use App\Models\Book;
use App\Models\Chapter;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog & Konten';

    protected static ?string $navigationLabel = 'Bab & Remotion';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('book_id')
                    ->label('Buku')
                    ->options(fn () => Book::query()->pluck('volume_code', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('chapter_index')
                    ->label('Indeks Bab (0-14)')
                    ->numeric()
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->placeholder('ch-0'),
                Toggle::make('is_free_sample')
                    ->label('Sampel Gratis (Bab 0 & 1)')
                    ->default(false),
                TextInput::make('motif_icon')
                    ->label('Ikon Motif')
                    ->default('compass'),
                TextInput::make('illustration_path')
                    ->label('Path Ilustrasi (Opsional)')
                    ->placeholder('/illustrations/book-1/ch-0.webp'),

                // Remotion 16:9 Builder Config
                TextInput::make('remotion_composition_id')
                    ->label('ID Komposisi Remotion 16:9')
                    ->default('ChapterInteractiveStory16x9')
                    ->required(),
                TextInput::make('remotion_duration_frames')
                    ->label('Durasi Frame Remotion')
                    ->numeric()
                    ->default(540)
                    ->required(),
                TextInput::make('remotion_fps')
                    ->label('FPS Remotion')
                    ->numeric()
                    ->default(30)
                    ->required(),

                // Toggle YouTube Opsional
                Toggle::make('is_youtube_enabled')
                    ->label('Aktifkan Modul Video YouTube (Opsional)')
                    ->default(false)
                    ->reactive(),
                TextInput::make('youtube_unlisted_id')
                    ->label('YouTube Unlisted Video ID')
                    ->placeholder('dQw4w9WgXcQ')
                    ->visible(fn ($get) => (bool) $get('is_youtube_enabled')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book.volume_code')
                    ->label('Buku')
                    ->badge()
                    ->sortable(),
                TextColumn::make('chapter_index')
                    ->label('Bab #')
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->fontFamily('mono'),
                IconColumn::make('is_free_sample')
                    ->label('Gratis')
                    ->boolean(),
                IconColumn::make('is_youtube_enabled')
                    ->label('YouTube')
                    ->boolean(),
                TextColumn::make('motif_icon')
                    ->label('Motif'),
            ])
            ->filters([
                SelectFilter::make('book_id')
                    ->label('Filter Buku')
                    ->options(fn () => Book::query()->pluck('volume_code', 'id')),
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
            'index' => ListChapters::route('/'),
            'create' => CreateChapter::route('/create'),
            'edit' => EditChapter::route('/{record}/edit'),
        ];
    }
}
