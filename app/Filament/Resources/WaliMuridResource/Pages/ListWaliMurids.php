<?php

namespace App\Filament\Resources\WaliMuridResource\Pages;

use App\Filament\Resources\WaliMuridResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ListWaliMurids extends ListRecords
{
    protected static string $resource = WaliMuridResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make() // Gunakan slideOver() untuk modal yang slide dari kanan
                ->mutateFormDataUsing(function (array $data): array {
                    // Proses pembuatan user dan penugasan role di sini
                    return DB::transaction(function () use ($data) {
                        // Buat user baru
                        $userData = [
                            'name' => $data['user']['name'] ?? '',
                            'email' => $data['user']['email'] ?? '',
                            'password' => $data['password'] ?? bcrypt('password'),
                        ];

                        $user = User::create($userData);

                        // Berikan role wali_kelas
                        $roleWaliMurid = \Spatie\Permission\Models\Role::where('name', 'Wali Murid')->first();
                        if ($roleWaliMurid) {
                            $user->assignRole($roleWaliMurid);
                        }

                        // Tambahkan user_id ke data
                        $data['user_id'] = $user->id;

                        // Hapus data yang tidak perlu
                        unset($data['user']);
                        unset($data['password']);

                        return $data;
                    });
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Wali Murid ditambahkan')
                        ->body('Data wali murid baru telah berhasil disimpan.')
                ),
        ];
    }
}
