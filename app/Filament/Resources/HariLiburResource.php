<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HariLiburResource\Pages;
use App\Models\HariLibur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HariLiburResource extends Resource
{
    protected static ?string $model = HariLibur::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Manajemen Sekolah';
    protected static ?string $navigationLabel = 'Hari Libur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_hari_libur')
                    ->label('Nama Hari Libur')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Hari Kemerdekaan RI'),

                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                        // Jika tanggal_selesai kosong atau lebih kecil dari tanggal_mulai, set sama dengan tanggal_mulai
                        if (!$get('tanggal_selesai') || $state > $get('tanggal_selesai')) {
                            $set('tanggal_selesai', $state);
                        }
                    }),

                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->minDate(fn(Forms\Get $get) => $get('tanggal_mulai'))
                    ->helperText('Kosongkan jika hanya 1 hari libur'),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_hari_libur')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-')
                    ->description(
                        fn($record) =>
                        $record->tanggal_selesai ?
                            'Total: ' . $record->tanggal_mulai->diffInDays($record->tanggal_selesai) + 1 . ' hari' :
                            'Hanya 1 hari'
                    ),

                Tables\Columns\TextColumn::make('rentang_tanggal')
                    ->label('Rentang Tanggal')
                    ->state(function ($record) {
                        if (!$record->tanggal_selesai || $record->tanggal_mulai->equalTo($record->tanggal_selesai)) {
                            return $record->tanggal_mulai->format('d M Y');
                        }
                        return $record->tanggal_mulai->format('d M Y') . ' - ' . $record->tanggal_selesai->format('d M Y');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('status_libur')
                    ->label('Status')
                    ->badge()
                    ->state(function ($record) {
                        $today = now()->startOfDay();
                        $start = $record->tanggal_mulai->startOfDay();
                        $end = $record->tanggal_selesai ? $record->tanggal_selesai->startOfDay() : $start;

                        if ($today->lt($start)) {
                            return 'Akan Datang';
                        } elseif ($today->between($start, $end)) {
                            return 'Sedang Berlangsung';
                        } else {
                            return 'Sudah Lewat';
                        }
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Akan Datang' => 'info',
                        'Sedang Berlangsung' => 'success',
                        'Sudah Lewat' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_mulai', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Libur')
                    ->options([
                        'akan_datang' => 'Akan Datang',
                        'sedang_berlangsung' => 'Sedang Berlangsung',
                        'sudah_lewat' => 'Sudah Lewat',
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }

                        $today = now()->startOfDay();

                        return match ($data['value']) {
                            'akan_datang' => $query->where('tanggal_mulai', '>', $today),
                            'sedang_berlangsung' => $query->where('tanggal_mulai', '<=', $today)
                                ->where(function ($q) use ($today) {
                                    $q->whereNull('tanggal_selesai')
                                        ->orWhere('tanggal_selesai', '>=', $today);
                                }),
                            'sudah_lewat' => $query->where(function ($q) use ($today) {
                                $q->where('tanggal_mulai', '<', $today)
                                    ->where(function ($sub) use ($today) {
                                        $sub->whereNull('tanggal_selesai')
                                            ->orWhere('tanggal_selesai', '<', $today);
                                    });
                            }),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('tahun')
                    ->form([
                        Forms\Components\Select::make('tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year),
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['tahun']) {
                            return $query;
                        }

                        return $query->whereYear('tanggal_mulai', $data['tahun']);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
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
            'index' => Pages\ListHariLiburs::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('tanggal_mulai', '<=', now())
            ->where(function ($query) {
                $query->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', now());
            })
            ->count();

        return $count > 0 ? $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
