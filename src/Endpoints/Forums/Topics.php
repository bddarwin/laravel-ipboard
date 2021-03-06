<?php

namespace Alawrence\Ipboard;

use Alawrence\Ipboard\Exceptions\IpboardThrottled;
use Alawrence\Ipboard\Exceptions\IpboardInvalidApiKey;
use Alawrence\Ipboard\Exceptions\IpboardMemberIdInvalid;

trait Topics
{
    /**
     * Fetch all forum topics that match the given search criteria.
     *
     * @param array $searchCriteria The search criteria topics should match.
     * @param int   $page           The page number to retrieve (default 1).
     *
     * @throws Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function getForumTopicsByPage($searchCriteria, $page = 1)
    {
        $validator = \Validator::make($searchCriteria, [
            'forums'        => 'string|is_csv_numeric',
            'authors'       => 'string|is_csv_numeric',
            'hasBestAnswer' => 'in:1,0',
            'hasPoll'       => 'in:1,0',
            'locked'        => 'in:1,0',
            'hidden'        => 'in:1,0',
            'pinned'        => 'in:1,0',
            'featured'      => 'in:1,0',
            'archived'      => 'in:1,0',
            'sortBy'        => 'in:id,date,title',
            'sortDir'       => 'in:asc,desc',
        ], [
            'is_csv_numeric' => 'The :attribute must be a comma separated string of IDs.',
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));

            throw new Exceptions\InvalidFormat($message);
        }

        return $this->getRequest('forums/topics', array_merge($searchCriteria, ['page' => $page]));
    }

    /**
     * Fetch all forum topics that match the given search criteria.
     *
     * @param int $searchCriteria The search criteria topics should match.
     *
     * @throws Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function getForumTopicsAll($searchCriteria)
    {
        $allTopics = [];

        $currentPage = 1;
        do {
            $response = $this->getForumTopicsByPage($searchCriteria, $currentPage);
            $allTopics = array_merge($allTopics, $response->results);
            $currentPage++;
        } while ($currentPage <= $response->totalPages);

        return $allTopics;
    }

    /**
     * Get a specific forum topic given the ID.
     *
     * @param int $topicId The ID of the forum topic to retrieve.
     *
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function getForumTopicById($topicId)
    {
        return $this->getRequest('forums/topics/'.$topicId);
    }

    /**
     * Get a specific forum topic given the ID.
     *
     * @param int   $topicId        The ID of the forum topic to retrieve.
     * @param array $searchCriteria The search criteria posts should match.
     * @param int   $page           The page number to retrieve (default 1).
     *
     * @throws Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function getForumTopicPosts($topicId, $searchCriteria = [], $page = 1)
    {
        $validator = \Validator::make($searchCriteria, [
            'hidden'        => 'in:1,0',
            'sortDir'       => 'in:asc,desc',
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));

            throw new Exceptions\InvalidFormat($message);
        }

        return $this->getRequest('forums/topics/'.$topicId.'/posts', array_merge($searchCriteria, ['page' => $page]));
    }

    /**
     * Create a forum topic with the given data.
     *
     * @param $forumID
     * @param int    $authorID The ID of the author for the topic (if set to 0, author_name is used)
     * @param string $title    The title of the topic.
     * @param string $post     The HTML content of the post.
     * @param array  $extra
     *
     * @throws Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function createForumTopic($forumID, $authorID, $title, $post, $extra = [])
    {
        $data = ['forum' => $forumID, 'author' => $authorID, 'title' => $title, 'post' => $post];
        $data = array_merge($data, $extra);

        $validator = \Validator::make($data, [
            'forum'       => 'required|numeric',
            'author'      => 'required|numeric',
            'title'       => 'required|string',
            'post'        => 'required|string',
            'author_name' => 'required_if:author,0|string',
            'prefix'      => 'string',
            'tags'        => 'string|is_csv_alphanumeric',
            'date'        => 'date_format:YYYY-mm-dd H:i:s',
            'ip_address'  => 'ip',
            'locked'      => 'in:0,1',
            'open_time'   => 'date_format:YYYY-mm-dd H:i:s',
            'close_time'  => 'date_format:YYYY-mm-dd H:i:s',
            'hidden'      => 'in:-1,0,1',
            'pinned'      => 'in:0,1',
            'featured'    => 'in:0,1',
        ], [
            'is_csv_alphanumeric' => 'The :attribute must be a comma separated string.',
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));

            throw new Exceptions\InvalidFormat($message);
        }

        return $this->postRequest('forums/topics', $data);
    }

    /**
     * Update a forum topic with the given ID.
     *
     * @param int   $topicID The ID of the topic to update.
     * @param array $data    The data to edit.
     *
     * @throws Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function updateForumTopic($topicID, $data = [])
    {
        $validator = \Validator::make($data, [
            'forum'       => 'numeric',
            'author'      => 'numeric',
            'author_name' => 'required_if:author,0|string',
            'title'       => 'string',
            'post'        => 'string',
            'prefix'      => 'string',
            'tags'        => 'string|is_csv_alphanumeric',
            'date'        => 'date_format:YYYY-mm-dd H:i:s',
            'ip_address'  => 'ip',
            'locked'      => 'in:0,1',
            'open_time'   => 'date_format:YYYY-mm-dd H:i:s',
            'close_time'  => 'date_format:YYYY-mm-dd H:i:s',
            'hidden'      => 'in:-1,0,1',
            'pinned'      => 'in:0,1',
            'featured'    => 'in:0,1',
        ], [
            'is_csv_alphanumeric' => 'The :attribute must be a comma separated string.',
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));

            throw new Exceptions\InvalidFormat($message);
        }

        return $this->postRequest('forums/topics/'.$topicID, $data);
    }

    /**
     * Delete a forum topic given it's ID.
     *
     * @param int $topicId The ID of the topic to delete.
     *
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     *
     * @return mixed
     */
    public function deleteForumTopic($topicId)
    {
        return $this->deleteRequest('forums/topics/'.$topicId);
    }
}
