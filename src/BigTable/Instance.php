<?php
namespace Unisharp\GoogleCloud\BigTable;

use Google\Cloud\Exception\NotFoundException;
use Unisharp\GoogleCloud\BigTable\Connection\ConnectionInterface;

class Instance extends BaseObject
{
    protected $name;
    protected $display_name;

    public function __construct(
        ConnectionInterface $connection,
        $projectId,
        $name,
        $display_name,
        $encode,
        $info = null
    ) {
        $this->name = $name;
        $this->display_name = $display_name;
        parent::__construct($connection, $projectId, $encode, $info);
    }

    public function tables()
    {
        $options['pageToken'] = null;

        do {
            $response = $this->connection->listSubscriptionsByTopic($options + [
                    'topic' => $this->name
                ]);

            foreach ($response['subscriptions'] as $subscription) {
                yield $this->subscriptionFactory($subscription);
            }

            // If there's a page token, we'll request the next page.
            $options['pageToken'] = isset($response['nextPageToken'])
                ? $response['nextPageToken']
                : null;
        } while ($options['pageToken']);
    }

    public function name()
    {
        return $this->name;
    }

    public function displayName()
    {
        return $this->display_name;
    }

    public function exists(array $options = [])
    {
        try {
            $this->info($options);
            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    public function reload(array $options = [])
    {
        try {
            $this->info = $this->connection->getInstance($options + [
                    'instance' => $this->name
                ]);
            $this->display_name = $this->info['displayName'];
            return $this->info;
        } catch (NotFoundException $e) {
            throw $e;
        }
    }

    public function info(array $options = [])
    {
        if (!$this->info) {
            $this->reload($options);
        }

        return $this->info;
    }

    public function subscriptions(array $options = [])
    {
        $options['pageToken'] = null;

        do {
            $response = $this->connection->listSubscriptionsByTopic($options + [
                    'topic' => $this->name
                ]);

            foreach ($response['subscriptions'] as $subscription) {
                yield $this->subscriptionFactory($subscription);
            }

            // If there's a page token, we'll request the next page.
            $options['pageToken'] = isset($response['nextPageToken'])
                ? $response['nextPageToken']
                : null;
        } while ($options['pageToken']);
    }
}
