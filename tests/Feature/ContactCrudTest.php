<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Contact;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ContactCrudTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_contact_requires_an_account(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $this->actingAs($user)
            ->from('/contacts/create')
            ->post('/contacts', [
                'last_name' => 'Nguyen',
            ])
            ->assertRedirect('/contacts/create')
            ->assertSessionHasErrors(['account_id']);
    }

    public function test_sales_representative_can_create_a_contact(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/contacts', [
            'account_id' => $account->id,
            'last_name' => 'Nguyen',
            'first_name' => 'Lan',
            'email' => 'lan@example.com',
        ]);

        $contact = Contact::query()->firstOrFail();
        $response->assertRedirect(route('contacts.show', $contact));
        $this->assertSame($account->id, $contact->account_id);
    }
}
