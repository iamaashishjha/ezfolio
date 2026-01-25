<?php

namespace App\Services\Contracts;

interface BlogPostInterface
{
    /**
     * Get all fields
     *
     * @param array $select
     * @return array
     */
    public function getAll(array $select = ['*']);

    /**
     * Store/update data
     *
     * @param array $data
     * @return array
     */
    public function store(array $data);

    /**
     * Fetch item by id
     *
     * @param int $id
     * @param array $select
     * @return array
     */
    public function getById(int $id, array $select = ['*']);

    /**
     * Fetch item by slug
     *
     * @param string $slug
     * @param array $select
     * @return array
     */
    public function getBySlug(string $slug, array $select = ['*']);

    /**
     * Get all fields with paginate
     *
     * @param array $data
     * @param array $select
     * @return array
     */
    public function getAllWithPaginate(array $data, array $select = ['*']);

    /**
     * Get published posts for frontend with filters
     *
     * @param array $filters
     * @return array
     */
    public function getFrontendPaginated(array $filters = []);

    /**
     * Fetch published post by slug
     *
     * @param string $slug
     * @return array
     */
    public function getFrontendBySlug(string $slug);

    /**
     * Delete items by id array
     *
     * @param array $ids
     * @return array
     */
    public function deleteByIds(array $ids);
}
