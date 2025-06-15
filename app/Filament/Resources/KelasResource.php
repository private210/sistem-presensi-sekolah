<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kelas;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\KelasResource\Pages;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';
    protected static ?string $navigationLabel = 'Daftar Kelas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(function () {
                        return Kelas::distinct('tahun_ajaran')
                            ->pluck('tahun_ajaran', 'tahun_ajaran')
                            ->toArray();
                    }),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                // Bulk action untuk mengaktifkan kelas
                Tables\Actions\BulkAction::make('activate')
                ->label('Aktifkan Kelas')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function (Collection $records) {
                    $count = $records->count();
                    $records->each(function ($record) {
                        $record->update(['is_active' => true]);
                    });

                    Notification::make()
                        ->title('Berhasil mengaktifkan ' . $count . ' kelas')
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->modalHeading('Aktifkan Kelas')
                ->modalDescription('Apakah Anda yakin ingin mengaktifkan kelas yang dipilih?')
                ->modalSubmitActionLabel('Ya, Aktifkan'),

            // Bulk action untuk menonaktifkan kelas
            Tables\Actions\BulkAction::make('deactivate')
                ->label('Nonaktifkan Kelas')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function (Collection $records) {
                    $count = $records->count();
                    $records->each(function ($record) {
                        $record->update(['is_active' => false]);
                    });

                    Notification::make()
                        ->title('Berhasil menonaktifkan ' . $count . ' kelas')
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->modalHeading('Nonaktifkan Kelas')
                ->modalDescription('Apakah Anda yakin ingin menonaktifkan kelas yang dipilih?')
                ->modalSubmitActionLabel('Ya, Nonaktifkan'),

            // Bulk action untuk toggle status (alternatif)
            Tables\Actions\BulkAction::make('toggle_status')
                ->label('Toggle Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function (Collection $records) {
                    $count = $records->count();
                    $records->each(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                    });

                    Notification::make()
                        ->title('Berhasil mengubah status ' . $count . ' kelas')
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->modalHeading('Toggle Status Kelas')
                ->modalDescription('Apakah Anda yakin ingin mengubah status kelas yang dipilih?')
                ->modalSubmitActionLabel('Ya, Ubah Status'),
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
            'index' => Pages\ListKelas::route('/'),
            // 'create' => Pages\CreateKelas::route('/create'),
            // 'view' => Pages\ViewKelas::route('/{record}'),
            // 'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }

    // Membatasi akses resource berdasarkan role
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->user()->hasRole('Wali Kelas'), function ($query) {
                $waliKelas = auth()->user()->waliKelas;
                if ($waliKelas) {
                    return $query->where('id', $waliKelas->kelas_id);
                }
                return $query->where('id', 0); // No results if not assigned to a class
            });
    }
}
