<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendEmailRequest;
use App\Services\GitHubService;
use Illuminate\Http\Request;
use App\Traits\ApiTrait;

/**
 * @OA\Info(
 *     title="GitHub Repo",
 *     version="1.0.0",
 *     description="API for fetching GitHub repositories"
 * )
 * @OA\Tag(
 *     name="Repositories",
 *     description="API Endpoints of Repositories"
 * )
 * @OA\Schema(
 *     schema="Repository",
 *     type="object",
 *     description="A GitHub repository",
 *     @OA\Property(property="name", type="string", description="The name of the repository"),
 *     @OA\Property(property="stars", type="integer", description="The number of stars the repository has"),
 *     @OA\Property(property="language", type="string", nullable=true, description="The programming language of the repository"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The creation date of the repository"),
 *     @OA\Property(property="url", type="string", description="The URL of the repository")
 * )
 * @OA\Schema(
 *     schema="RepositoriesResponse",
 *     type="object",
 *     @OA\Property(property="status", type="boolean"),
 *     @OA\Property(property="code", type="integer"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Repository")
 *     ),
 *     @OA\Property(property="pagination", type="string", nullable=true),
 *     @OA\Property(property="total", type="string", nullable=true)
 * )
 */

class RepositoryController extends Controller
{
    protected $gitHubService;
    use ApiTrait;

    public function __construct(GitHubService $gitHubService)
    {
        $this->gitHubService = $gitHubService;
    }

    /**
     * @OA\Get(
     *     path="/api/repositories",
     *     tags={"Repositories"},
     *     summary="Get Repositories",
     *     description="Fetch a list of GitHub repositories based on query parameters",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of repositories to return",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="created_after",
     *         in="query",
     *         description="Filter repositories created after this date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         description="Filter repositories by programming language",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/RepositoriesResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $queryParams = $this->gitHubService->buildQueryParams($request);
            $repositories = $this->gitHubService->getRepositories($queryParams);
            return $this->ApiResponseData($repositories, true, 200, 'Repositories Retrieved successfully.');
        } catch (\Exception $e) {
            return $this->ApiResponseMessage($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/repositories/email",
     *     tags={"Repositories"},
     *     summary="Send Repositories Email",
     *     description="Fetch repositories and send the result as an email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 description="Email address to send the repositories to",
     *                 example="example@example.com"
     *             ),
     *             @OA\Property(
     *                 property="limit",
     *                 type="integer",
     *                 description="Number of repositories to return",
     *                 example=10
     *             ),
     *             @OA\Property(
     *                 property="created_after",
     *                 type="string",
     *                 format="date",
     *                 description="Filter repositories created after this date",
     *                 example="2021-01-01"
     *             ),
     *             @OA\Property(
     *                 property="language",
     *                 type="string",
     *                 description="Filter repositories by programming language",
     *                 example="PHP"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email Sent successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public function sendMail(SendEmailRequest $request)
    {
        try {
            $data = $request->validated();
            $queryParams = $this->gitHubService->buildQueryParams($request);
            $this->gitHubService->fetchAndSendEmail($queryParams, $data['email']);
            return $this->ApiResponseMessage('Email Sent successfully', 200);
        } catch (\Exception $e) {
            return $this->ApiResponseMessage($e->getMessage(), 500);
        }
    }
}
