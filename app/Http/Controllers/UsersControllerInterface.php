<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

interface UsersControllerInterface
{
    /**
     * The list all of users.
     * With pagination.
     *
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request): array;

    /**
     * Storing a new User.
     *
     * @param Request $request
     *
     * @return array
     */
    public function store(Request $request): array;

    /**
     * Show specified user.
     *
     * @param string $id
     *
     * @return array
     */
    public function show(string $id): array;

    /**
     * Update an specified user.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return array
     */
    public function update(Request $request, string $id): array;

    /**
     * Destroy user specified.
     *
     * @param string $id
     *
     * @return array
     */
    public function destroy(string $id): array;
}