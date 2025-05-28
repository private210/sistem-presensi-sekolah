<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaliMuridResource\Pages;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliMurid;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class WaliMuridResource extends Resource
{
    protected static ?string $model = WaliMurid::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Wali Murid';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Data User
                Forms\Components\Section::make('Data Akun')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255),
                    ]),

                // Data Wali Murid
                Forms\Components\Section::make('Data Wali Murid')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('hubungan')
                            ->label('Hubungan dengan Siswa')
                            ->options([
                                'Ayah' => 'Ayah',
                                'Ibu' => 'Ibu',
                                'Wali' => 'Wali',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\Select::make('siswa_id')
                            ->label('Siswa')
                            ->relationship('siswa', 'nama_lengkap')
                            ->options(
                                Siswa::where('is_active', true)
                                    ->whereDoesntHave('waliMurid')
                                    ->orWhereHas('waliMurid', function ($query) use ($form) {
                                        // Exclude students that already have a wali murid, except the current one
                                        if ($form->getRecord()) {
                                            $query->where('wali_murids.id', $form->getRecord()->id);
                                        }
                                    })
                                    ->pluck('nama_lengkap', 'id')
                            )
                            // ->searchable()
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('siswa.kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hubungan')
                    ->label('Hubungan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                // Gunakan modal untuk edit
                Tables\Actions\EditAction::make()
                    ->slideOver() // atau ->modal()
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        // Data untuk ditampilkan di form
                        if ($record->user) {
                            $data['user']['name'] = $record->user->name;
                            $data['user']['email'] = $record->user->email;
                        }
                        return $data;
                    })
                    ->using(function ($record, array $data) {
                        // Handle update data di sini
                        return \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                            // Update user data
                            if (isset($data['user']) && $record->user) {
                                $record->user->update([
                                    'name' => $data['user']['name'],
                                    'email' => $data['user']['email'],
                                ]);

                                // Update password jika diisi
                                if (!empty($data['password'])) {
                                    $record->user->update([
                                        'password' => $data['password'],
                                    ]);
                                }

                                // Pastikan user memiliki role wali_kelas
                                if (!$record->user->hasRole('Wali Murid')) {
                                    $record->user->assignRole('Wali Murid');
                                }
                            }
                            // Update wali kelas data
                            $record->update([
                                'nama_lengkap' => $data['nama_lengkap'],
                                'hubungan' => $data['hubungan'],
                                'siswa_id' => $data['siswa_id'],
                                'is_active' => $data['is_active'],
                            ]);

                            return $record;
                        });
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListWaliMurids::route('/'),
            // 'create' => Pages\CreateWaliMurid::route('/create'),
            // 'view' => Pages\ViewWaliMurid::route('/{record}'),
            // 'edit' => Pages\EditWaliMurid::route('/{record}/edit'),
        ];
    }
}
