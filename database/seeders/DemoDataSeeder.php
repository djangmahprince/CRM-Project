<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\OrgSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        OrgSetting::put('default_sharing', config('crm.default_sharing'));

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@northstar.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Password1!'),
            ],
        );
        $admin->syncRoles(['System Administrator']);

        $rep = User::query()->updateOrCreate(
            ['email' => 'rep@northstar.test'],
            [
                'name' => 'Sales Representative',
                'password' => Hash::make('Password1!'),
            ],
        );
        $rep->syncRoles(['Sales Representative']);

        $service = User::query()->updateOrCreate(
            ['email' => 'service@northstar.test'],
            [
                'name' => 'Service Representative',
                'password' => Hash::make('Password1!'),
            ],
        );
        $service->syncRoles(['Service Representative']);

        Lead::factory()->count(8)->create([
            'owner_id' => $rep->id,
            'created_by' => $rep->id,
            'updated_by' => $rep->id,
        ]);

        Lead::factory()->count(3)->create([
            'owner_id' => $admin->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $accounts = Account::factory()->count(5)->create([
            'owner_id' => $rep->id,
            'created_by' => $rep->id,
            'updated_by' => $rep->id,
        ]);

        foreach ($accounts as $account) {
            Contact::factory()->count(2)->create([
                'account_id' => $account->id,
                'owner_id' => $rep->id,
                'created_by' => $rep->id,
                'updated_by' => $rep->id,
            ]);

            Opportunity::factory()->count(2)->create([
                'account_id' => $account->id,
                'owner_id' => $rep->id,
                'created_by' => $rep->id,
                'updated_by' => $rep->id,
            ]);

            CrmCase::factory()->create([
                'account_id' => $account->id,
                'owner_id' => $service->id,
                'created_by' => $service->id,
                'updated_by' => $service->id,
            ]);
        }
    }
}
