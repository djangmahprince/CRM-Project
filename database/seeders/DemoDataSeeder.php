<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\OrgSetting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        OrgSetting::put('default_sharing', config('crm.default_sharing'));

        $admin = $this->user('admin@northstar.test', 'System Administrator', 'System Administrator');
        $manager = $this->user('manager@northstar.test', 'Sales Manager', 'Sales Manager');
        $rep = $this->user('rep@northstar.test', 'Sales Representative', 'Sales Representative');
        $service = $this->user('service@northstar.test', 'Service Representative', 'Service Representative');
        $readonly = $this->user('readonly@northstar.test', 'Read-Only User', 'Read-Only User');

        // Quiet reference so static analysis keeps the read-only demo user intentional.
        unset($readonly);

        Lead::factory()->count(6)->create([
            'owner_id' => $rep->id,
            'created_by' => $rep->id,
            'updated_by' => $rep->id,
            'lead_status' => 'New',
        ]);

        Lead::factory()->count(3)->create([
            'owner_id' => $manager->id,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
            'lead_status' => 'Working',
        ]);

        Lead::factory()->count(2)->create([
            'owner_id' => $admin->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'lead_status' => 'Qualified',
        ]);

        $parent = Account::factory()->create([
            'name' => 'Northstar Holdings',
            'owner_id' => $manager->id,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $accounts = Account::factory()->count(4)->create([
            'owner_id' => $rep->id,
            'created_by' => $rep->id,
            'updated_by' => $rep->id,
            'parent_account_id' => $parent->id,
        ]);

        $stages = array_keys(config('crm.opportunity_stages'));
        $caseStatuses = config('crm.case_statuses');
        $sources = config('crm.lead_sources');

        foreach ($accounts as $index => $account) {
            $contact = Contact::factory()->create([
                'account_id' => $account->id,
                'owner_id' => $rep->id,
                'created_by' => $rep->id,
                'updated_by' => $rep->id,
            ]);

            Contact::factory()->create([
                'account_id' => $account->id,
                'reports_to_id' => $contact->id,
                'owner_id' => $rep->id,
                'created_by' => $rep->id,
                'updated_by' => $rep->id,
            ]);

            foreach ($stages as $stageIndex => $stage) {
                Opportunity::factory()->create([
                    'account_id' => $account->id,
                    'owner_id' => $rep->id,
                    'created_by' => $rep->id,
                    'updated_by' => $rep->id,
                    'stage' => $stage,
                    'lead_source' => $sources[$stageIndex % count($sources)],
                    'amount' => 5000 + ($index * 1000) + ($stageIndex * 750),
                    'close_date' => now()->addDays(10 + $stageIndex * 7)->toDateString(),
                ]);
            }

            CrmCase::factory()->create([
                'account_id' => $account->id,
                'contact_id' => $contact->id,
                'owner_id' => $service->id,
                'created_by' => $service->id,
                'updated_by' => $service->id,
                'status' => $caseStatuses[$index % count($caseStatuses)],
                'priority' => config('crm.priorities')[$index % 3],
            ]);

            Task::factory()->create([
                'owner_id' => $rep->id,
                'assigned_to_id' => $rep->id,
                'created_by' => $rep->id,
                'updated_by' => $rep->id,
                'related_type' => 'account',
                'related_id' => $account->id,
                'contact_id' => $contact->id,
                'due_date' => now()->toDateString(),
                'status' => 'Not Started',
                'subject' => 'Follow up '.$account->name,
            ]);

            Event::factory()->create([
                'owner_id' => $rep->id,
                'assigned_to_id' => $rep->id,
                'created_by' => $rep->id,
                'updated_by' => $rep->id,
                'related_type' => 'account',
                'related_id' => $account->id,
                'contact_id' => $contact->id,
                'subject' => 'Meeting '.$account->name,
                'starts_at' => now()->setTime(10 + $index, 0),
                'ends_at' => now()->setTime(11 + $index, 0),
            ]);
        }

        Task::factory()->create([
            'owner_id' => $rep->id,
            'assigned_to_id' => $rep->id,
            'created_by' => $rep->id,
            'updated_by' => $rep->id,
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'In Progress',
            'subject' => 'Overdue proposal review',
            'priority' => 'High',
        ]);
    }

    private function user(string $email, string $name, string $role): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('Password1!'),
            ],
        );
        $user->syncRoles([$role]);

        return $user;
    }
}
