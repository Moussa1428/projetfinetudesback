<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function superadmin_can_perform_crud_on_users()
    {
        // Crée un superadmin
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        Sanctum::actingAs($admin, ['*']);

        // Création
        $payload = [
            'name'      => 'Alice',
            'last_name' => 'Durand',
            'email'     => 'alice@example.com',
            'phone'     => '0600000000',
            'address'   => '123 rue Exemple'
        ];

        $this->postJson('/api/users', $payload)
             ->assertStatus(201)
             ->assertJsonFragment(['email' => 'alice@example.com']);

        $userId = $this->json('GET', '/api/users')
                       ->json()[0]['id'];

        // Affichage
        $this->getJson("/api/users/{$userId}")
             ->assertStatus(200)
             ->assertJsonFragment(['email' => 'alice@example.com']);

        // Mise à jour
        $this->putJson("/api/users/{$userId}", ['name' => 'Alice Modif'])
             ->assertStatus(200)
             ->assertJsonFragment(['name' => 'Alice Modif']);

        // Suppression
        $this->deleteJson("/api/users/{$userId}")
             ->assertStatus(200)
             ->assertJson(['message' => 'Utilisateur supprimé avec succès']);
    }

    /** @test */
    public function non_superadmin_cannot_access_user_endpoints()
    {
        $user = User::factory()->create();
        $user->assignRole('Assistant');  // non superadmin

        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/users', [])
             ->assertStatus(403);
    }
}
