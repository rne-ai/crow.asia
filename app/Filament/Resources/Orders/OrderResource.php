<?php

namespace App\Filament\Resources\Orders;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\PricingTier;
use App\Services\Payment\EntitlementSyncService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Bisnis & Penjualan';

    protected static ?string $navigationLabel = 'Pesanan & Invoice';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Nomor Order')
                    ->required()
                    ->disabled(),
                TextInput::make('customer_email')
                    ->label('Email Pelanggan')
                    ->email()
                    ->required(),
                Select::make('pricing_tier_id')
                    ->label('Paket Harga')
                    ->options(fn () => PricingTier::query()->pluck('id', 'id'))
                    ->required(),
                TextInput::make('amount_idr')
                    ->label('Total (IDR)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        PaymentStatus::PENDING->value => 'PENDING',
                        PaymentStatus::PAID->value => 'PAID',
                        PaymentStatus::CLOSED->value => 'CLOSED',
                        PaymentStatus::EXPIRED->value => 'EXPIRED',
                    ])
                    ->required(),
                TextInput::make('mayar_invoice_id')
                    ->label('Mayar Invoice ID')
                    ->disabled(),
                TextInput::make('mayar_transaction_id')
                    ->label('Mayar Transaction ID')
                    ->disabled(),
                DateTimePicker::make('paid_at')
                    ->label('Waktu Pembayaran'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Order')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('pricing_tier_id')
                    ->label('Paket')
                    ->badge(),
                TextColumn::make('amount_idr')
                    ->label('Total')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::PAID => 'success',
                        PaymentStatus::PENDING => 'warning',
                        PaymentStatus::CLOSED, PaymentStatus::EXPIRED => 'danger',
                    }),
                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Status')
                    ->options([
                        PaymentStatus::PENDING->value => 'PENDING',
                        PaymentStatus::PAID->value => 'PAID',
                        PaymentStatus::EXPIRED->value => 'EXPIRED',
                    ]),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Force Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Order $record) => !$record->isPaid())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran Manual')
                    ->modalDescription('Tindakan ini akan menandai pesanan sebagai PAID dan otomatis memberikan hak akses buku ke akun pembeli.')
                    ->action(function (Order $record, EntitlementSyncService $syncService) {
                        $record->payment_status = PaymentStatus::PAID;
                        $record->paid_at = now();
                        $record->save();

                        $granted = $syncService->syncEntitlements($record);

                        Notification::make()
                            ->title('Pesanan Disetujui')
                            ->body("Hak akses playbook (" . implode(', ', $granted) . ") berhasil diberikan ke {$record->customer_email}.")
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
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
