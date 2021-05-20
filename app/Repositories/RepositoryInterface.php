<?php

namespace App\Repositories;

use Illuminate\Http\Request;

interface RepositoryInterface
{
    /**
     * Getting all documents.
     *
     * @param integer $perPage
     *
     * @return array
     */
    public function all(int $perPage = 20): array;

    /**
     * Storing the document.
     *
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array;

    /**
     * Retrieve specified a document.
     *
     * @param string $id
     *
     * @return array
     */
    public function find(string $id): array;

    /**
     * Updating specified a document.
     *
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function update(string $id, array $data): array;

    /**
     * Destroy specified a document.
     *
     * @param string $id
     *
     * @return array
     */
    public function destroy(string $id): bool;

    /**
     * Filtering the documents.
     *
     * @param Request $request
     *
     * @return array
     */
    public function search(Request $request): array;
}