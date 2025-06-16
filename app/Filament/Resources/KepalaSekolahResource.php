<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\KepalaSekolah;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KepalaSekolahResource\Pages;
use App\Filament\Resources\KepalaSekolahResource\RelationManagers;

class KepalaSekolahResource extends Resource
{
    protected static ?string $model = KepalaSekolah::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Kepala Sekolah';

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
                            ->maxLength(32),
                    ]),

                // Data  Kepala Sekolah
                Forms\Components\Section::make('Data Kepala Sekolah')
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('pangkat')
                            ->label('Pangkat')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('golongan')
                            ->label('Golongan')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pangkat')
                    ->label('Pangkat')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('golongan')
                    ->label('Golongan')
                    ->toggleable(isToggledHiddenByDefault: true),
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

                                // Pastikan user memiliki role Kepala Sekolah
                                if (!$record->user->hasRole('Kepala Sekolah')) {
                                    $record->user->assignRole('Kepala Sekolah');
                                }
                            }

                            // Update wali kelas data
                            $record->update([
                                'nip' => $data['nip'] ?? $record->nip,
                                'nama_lengkap' => $data['nama_lengkap'],
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
            'index' => Pages\ListKepalaSekolahs::route('/'),
            // 'create' => Pages\CreateWaliKelas::route('/create'),
            // 'view' => Pages\ViewWaliKelas::route('/{record}'),
            // 'edit' => Pages\EditWaliKelas::route('/{record}/edit'),
        ];
    }
}
