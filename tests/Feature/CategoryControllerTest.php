<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the index endpoint for listing categories.
     */
    public function test_can_list_categories()
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('categories')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJson(['message' => 'Categories retrieved successfully']);
    }

    /**
     * Test the store endpoint for creating a new category.
     */
    public function test_can_create_category()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('categories', ['name' => 'New Category'])
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Category created successfully',
                'data' => ['name' => 'New Category']
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test the store endpoint validation for creating a new category.
     */
    public function test_cannot_create_category_with_invalid_data()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('categories', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test the show endpoint for retrieving a single category.
     */
    public function test_can_retrieve_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson("categories/{$category->id}")
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Category retrieved successfully',
                'data' => ['id' => $category->id],
            ]);
    }

    /**
     * Test the show endpoint for retrieving a non-existent category.
     */
    public function test_cannot_retrieve_nonexistent_category()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('categories/999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Category not found']);
    }

    /**
     * Test the update endpoint for modifying an existing category.
     */
    public function test_can_update_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("categories/{$category->id}", ['name' => 'Updated Category'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Category updated successfully',
                'data' => ['name' => 'Updated Category'],
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /**
     * Test the update endpoint validation for modifying an existing category.
     */
    public function test_cannot_update_category_with_invalid_data()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("categories/{$category->id}", ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test the destroy endpoint for deleting a category.
     */
    public function test_can_delete_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson("categories/{$category->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Category deleted successfully']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test the destroy endpoint for deleting a non-existent category.
     */
    public function test_cannot_delete_nonexistent_category()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson('categories/999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Category not found']);
    }
}
