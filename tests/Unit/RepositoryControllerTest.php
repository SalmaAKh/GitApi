<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class RepositoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsRepositoriesSuccessfully()
    {
        $queryParams = [
            'limit' => 10,
            'created_after' => '2021-01-01',
            'language' => 'PHP',
        ];

        // Make a GET request to the endpoint
        $response = $this->get('/api/repositories?' . http_build_query($queryParams));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'code',
                'message',
                'data' => [
                    '*' => [
                        'name',
                        'stars',
                        'language',
                        'created_at',
                        'url'
                    ]
                ]
            ]);
    }

    public function testSendMailSuccessfully()
    {
        // Mock request
        $requestData = [
            'email' => 'test@example.com',
            'limit' => 10,
            'created_after' => '2021-01-01',
            'language' => 'PHP',
        ];

        // Make a POST request to the endpoint
        $response = $this->postJson('/api/repositories/email', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email Sent successfully',
            ]);
    }

    public function testIndexHandlesException()
    {
        // Use invalid query parameter to trigger an error
        $queryParams = [
            'limit' => 10,
            'created_after' => 'invalid-date-format', // Invalid date format
            'language' => 'PHP',
        ];

        $response = $this->get('/api/repositories?' . http_build_query($queryParams));

        $response->assertStatus(500)
            ->assertJson([
                'code' => 500,
                'message' => 'Error fetching repositories',
            ]);
    }

    public function testSendMailHandlesValidationError()
    {
        // Use invalid email to trigger a validation error
        $requestData = [
            'email' => 'invalid-email',
            'limit' => 10,
            'created_after' => '2021-01-01',
            'language' => 'PHP',
        ];

        $response = $this->postJson('/api/repositories/email', $requestData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'code' => 422,
                'message' => 'Validation errors',
                'errors' => [
                    'email' => [
                        'The email field must be a valid email address.'
                    ]
                ],
            ]);
    }


    public function testReturnsCorrectNumberOfRepositoriesBasedOnLimit()
    {
        $queryParams = [
            'limit' => 10,
            'created_after' => '2021-01-01',
            'language' => 'PHP',
        ];

        $response = $this->get('/api/repositories?' . http_build_query($queryParams));

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }

    public function testReturnsCorrectNumberOfRepositoriesAndCorrectLanguage()
    {
        $queryParams = [
            'limit' => 5,
            'created_after' => '2021-01-01',
            'language' => 'PHP',
        ];

        $response = $this->get('/api/repositories?' . http_build_query($queryParams));

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        // Check that all repositories have the correct language
        $responseData = $response->json('data');
        foreach ($responseData as $repo) {
            $this->assertEquals('PHP', $repo['language']);
        }
    }
}
