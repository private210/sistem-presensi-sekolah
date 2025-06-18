<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaliKelasResource\Pages;
use App\Models\Kelas;
use App\Models\User;
use App\Models\WaliKelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class WaliKelasResource extends Resource
{
    protected static ?string $model = WaliKelas::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Wali Kelas';

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
                            ->unique(
                                table: User::class,
                                column: 'email',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, $get, $record) {
                                    // Jika sedang edit, abaikan email user yang sedang diedit
                                    if ($record && $record->user) {
                                        return $rule->ignore($record->user->id);
                                    }
                                    return $rule;
                                }
                            ),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->hint('Minimal 8 karakter, maksimal 32 karakter')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(32),
                    ]),

                // Data Wali Kelas
                Forms\Components\Section::make('Data Wali Kelas')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(20),
                        Forms\Components\Select::make('kelas_id')
                            ->label('Kelas yang Dipegang')
                            ->relationship('kelas', 'nama_kelas')
                            ->options(
                                Kelas::where('is_active', true)
                                    ->whereDoesntHave('waliKelas')
                                    ->orWhereHas('waliKelas', function ($query) use ($form) {
                                        // Exclude classes that already have a wali kelas, except the current one
                                        if ($form->getRecord()) {
                                            $query->where('wali_kelas.id', $form->getRecord()->id);
                                        }
                                    })
                                    ->pluck('nama_kelas', 'id')
                            )
                            // ->searchable()
                            ->required(),
                        Forms\Components\FileUpload::make('foto')
                            ->label('Foto')
                            ->image()
                            ->directory('wali-kelas')
                            ->visibility('public')
                            ->hint('Unggah file dalam format JPEG/PNG/JPG dengan ukuran maksimal 2MB')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']),
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
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Wali Kelas')
                    ->sortable(),
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
                                if (!$record->user->hasRole('Wali Kelas')) {
                                    $record->user->assignRole('Wali Kelas');
                                }
                            }

                            // Update wali kelas data
                            $record->update([
                                'nama_lengkap' => $data['nama_lengkap'],
                                'nip' => $data['nip'] ?? $record->nip,
                                'kelas_id' => $data['kelas_id'],
                                'is_active' => $data['is_active'],
                            ]);

                            return $record;
                        });
                    }),
                Tables\Actions\ViewAction::make('View')
                    ->icon('heroicon-o-eye')
                    ->label('Detail')
                    ->modalHeading('Detail Kepala Sekolah')
                    ->form([
                        // Data User
                        Forms\Components\Section::make('Data Akun')
                            ->schema([
                                Forms\Components\TextInput::make('user.name')
                                    ->label('Nama Pengguna')
                                    ->disabled(),
                                Forms\Components\TextInput::make('user.email')
                                    ->label('Email')
                                    ->disabled(),
                                Forms\Components\TextInput::make('password_status')
                                    ->label('Status Password')
                                    ->disabled()
                                    ->placeholder('••••••••••••')
                                    ->helperText('Password tidak ditampilkan untuk keamanan'),
                            ]),

                        // Data Kepala Sekolah
                        Forms\Components\Section::make('Data Kepala Sekolah')
                            ->schema([
                                Forms\Components\TextInput::make('nip')
                                    ->label('NIP')
                                    ->disabled(),
                                Forms\Components\TextInput::make('pangkat')
                                    ->label('Pangkat')
                                    ->disabled(),
                                Forms\Components\TextInput::make('golongan')
                                    ->label('Golongan')
                                    ->disabled(),
                                Forms\Components\TextInput::make('nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->disabled(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->disabled(),
                            ]),
                    ])
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        // Menyiapkan data user untuk ditampilkan
                        if ($record->user) {
                            $data['user']['name'] = $record->user->name;
                            $data['user']['email'] = $record->user->email;
                            // Tampilkan status password (bukan password asli)
                            $data['password_status'] = $record->user->password ? '' : 'Password belum diatur';
                        }
                        return $data;
                    }),
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
            'index' => Pages\ListWaliKelas::route('/'),
            // 'create' => Pages\CreateWaliKelas::route('/create'),
            // 'view' => Pages\ViewWaliKelas::route('/{record}'),
            // 'edit' => Pages\EditWaliKelas::route('/{record}/edit'),
        ];
    }
}
