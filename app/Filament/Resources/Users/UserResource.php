<?php

namespace App\Filament\Resources\Users;

use App\Enums\ContentRegister;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Book;
use App\Models\SupportedLocale;
use App\Models\User;
use App\Models\UserEntitlement;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'Pengguna & Hak Akses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('role')
                    ->label('Peran (Role)')
                    ->options([
                        'reader' => 'Reader (Pembaca)',
                        'editor' => 'Editor (Pengelola Konten)',
                        'admin' => 'Admin (Akses Penuh)',
                    ])
                    ->default('reader')
                    ->required(),
                Select::make('preferred_locale')
                    ->label('Bahasa Pilihan')
                    ->options(fn () => SupportedLocale::query()->pluck('native_name', 'code'))
                    ->default('id')
                    ->required(),
                Select::make('preferred_id_register')
                    ->label('Gaya Bahasa Indonesia')
                    ->options([
                        ContentRegister::CASUAL->value => 'Kasual (Gue/Lo)',
                        ContentRegister::STANDARD->value => 'Standar (Saya/Kamu)',
                    ])
                    ->default(ContentRegister::CASUAL->value)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'editor' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('entitlements_count')
                    ->label('Akses Buku')
                    ->counts('entitlements')
                    ->badge()
                    ->color('success'),
                TextColumn::make('preferred_locale')
                    ->label('Bahasa')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'reader' => 'Reader',
                        'editor' => 'Editor',
                        'admin' => 'Admin',
                    ]),
            ])
            ->recordActions([
                // 1-Click Grant Access Action
                Action::make('grant_access')
                    ->label('Beri Akses Buku')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('primary')
                    ->form([
                        Select::make('book_id')
                            ->label('Pilih Buku Playbook')
                            ->options(fn () => Book::query()->pluck('volume_code', 'id'))
                            ->required(),
                    ])
                    ->modalHeading('1-Click Grant Access Playbook')
                    ->modalDescription('Pilih buku playbook yang ingin diberikan aksesnya langsung kepada pengguna ini.')
                    ->action(function (User $record, array $data) {
                        UserEntitlement::query()->firstOrCreate(
                            [
                                'user_id' => $record->id,
                                'book_id' => $data['book_id'],
                            ],
                            [
                                'is_active' => true,
                                'granted_at' => now(),
                            ]
                        );

                        Notification::make()
                            ->title('Hak Akses Diberikan')
                            ->body("Pengguna {$record->email} kini memiliki akses resmi ke {$data['book_id']}.")
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
