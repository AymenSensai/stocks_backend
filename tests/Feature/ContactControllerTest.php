<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_list_contacts_for_authenticated_user()
    {
        $user = User::factory()->create();
        Contact::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson(route('contacts.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name', 'phone_number', 'contact_type', 'created_at', 'updated_at'],
                ],
            ]);
    }

    /** @test */
    public function can_create_a_contact_for_authenticated_user()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'John Doe',
            'phone_number' => '123456789',
            'contact_type' => 'Customer',
        ];

        $response = $this->actingAs($user)->postJson(route('contacts.store'), $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Contact created successfully',
                'data' => $data,
            ]);

        $this->assertDatabaseHas('contacts', array_merge($data, ['user_id' => $user->id]));
    }

    /** @test */
    public function can_retrieve_a_contact_for_authenticated_user()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson(route('contacts.show', $contact->id));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contact retrieved successfully',
                'data' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone_number' => $contact->phone_number,
                    'contact_type' => $contact->contact_type,
                ],
            ]);
    }

    /** @test */
    public function cannot_retrieve_contact_of_another_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->getJson(route('contacts.show', $contact->id));

        $response->assertStatus(404)
            ->assertJson(['message' => 'Contact not found']);
    }

    /** @test */
    public function can_update_a_contact_for_authenticated_user()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $data = [
            'name' => 'Updated Name',
            'phone_number' => '987654321',
            'contact_type' => 'Vendor',
        ];

        $response = $this->actingAs($user)->putJson(route('contacts.update', $contact->id), $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contact updated successfully',
                'data' => $data,
            ]);

        $this->assertDatabaseHas('contacts', array_merge($data, ['id' => $contact->id]));
    }

    /** @test */
    public function cannot_update_contact_of_another_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->putJson(route('contacts.update', $contact->id), [
            'name' => 'Invalid Update',
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Contact not found']);
    }

    /** @test */
    public function can_delete_a_contact_for_authenticated_user()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('contacts.destroy', $contact->id));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Contact deleted successfully']);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function cannot_delete_contact_of_another_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $contact = Contact::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->deleteJson(route('contacts.destroy', $contact->id));

        $response->assertStatus(404)
            ->assertJson(['message' => 'Contact not found']);
    }
}
