<?php

namespace App\Transformers;

class RepositoryTransformer
{
    /**
     * Transform a repository array to a desired format.
     *
     * @param array $repository
     * @return array
     */
    public static function transform(array $repository): array
    {
        return [
            'name' => $repository['name'],
            'stars' => $repository['stargazers_count'],
            'language' => $repository['language'],
            'created_at' => $repository['created_at'],
            'url' => $repository['html_url'],
        ];
    }

    /**
     * Transform a collection of repositories.
     *
     * @param array $repositories
     * @return array
     */
    public static function transformCollection(array $repositories): array
    {
        return array_map([self::class, 'transform'], $repositories);
    }
}
