<?php
namespace App\Services;

use App\Exports\ReposExport;
use App\Mail\RepoResultsMail;
use App\Transformers\RepositoryTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class GitHubService
{
    protected string $baseUrl = 'https://api.github.com';

    /**
     * Build query parameters for the GitHub API request based on the request inputs.
     *
     * @param Request $request
     * @return array
     */
    public function buildQueryParams(Request $request): array
    {
        // Default query parameters
        $queryParams = [
            'q' => 'stars:>0', // Filter repositories with more than 0 stars
            'sort' => 'stars', // Sort by the number of stars
            'order' => 'desc', // Order in descending order
            'per_page' => $request->get('limit', 10), // Number of repositories per page, default is 10
        ];

        // Add created_after filter if provided
        if ($request->has('created_after')) {
            $queryParams['q'] .= ' created:>' . $request->get('created_after');
        }

        // Add language filter if provided
        if ($request->has('language')) {
            $queryParams['q'] .= ' language:' . $request->get('language');
        }

        return $queryParams;
    }

    /**
     * Fetch repositories from GitHub API based on the query parameters.
     *
     * @param array $queryParams
     * @return array
     * @throws Exception
     */
    public function getRepositories(array $queryParams): array
    {
        try {
            // Make a GET request to the GitHub API with the query parameters
            $response = Http::get("{$this->baseUrl}/search/repositories", $queryParams);

            $repositories = $response->json()['items'];

            // Return Transformed Collection
            return RepositoryTransformer::transformCollection($repositories);
        } catch (Exception $e) {
            throw new Exception("Error fetching repositories");
        }

    }

    /**
     * Fetch repositories and send the result as an email.
     *
     * @param array $queryParams
     * @param string $email
     * @throws Exception
     */
    public function fetchAndSendEmail(array $queryParams, string $email): void
    {
        try {
            // Fetch repositories from GitHub
            $repositories = $this->getRepositories($queryParams);

            // Generate Excel file with the repository data
            $fileName = 'repositories.xlsx';
            Excel::store(new ReposExport($repositories), $fileName, 'local');

            // Send email with the generated Excel file
            Mail::to($email)->send(new RepoResultsMail($fileName));
            Log::info("Email sent successfully to {$email} with file {$fileName}");
        } catch (Exception $e) {
            // Log error if email sending fails and rethrow the exception
            Log::error("Failed to send email to {$email}: " . $e->getMessage());
            throw new Exception("Failed to send email: " . $e->getMessage());
        }
    }
}
