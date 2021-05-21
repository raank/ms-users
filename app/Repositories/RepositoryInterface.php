<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Getting all documents.
     *
     * @param integer $perPage
     *
     * @return LengthAwarePaginator
     */
    public function all(int $perPage = 20): LengthAwarePaginator;

    /**
     * Storing the document.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data);

    /**
     * Retrieve specified a document.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function find(string $id);

    /**
     * Updating specified a document.
     *
     * @param string $id
     * @param array $data
     *
     * @return mixed
     */
    public function update(string $id, array $data);

    /**
     * Destroy specified a document.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function destroy(string $id);

    /**
     * Filtering the documents.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function search(Request $request): LengthAwarePaginator;
}