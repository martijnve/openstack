<?php declare (strict_types=1);

namespace OpenStack\ObjectStore\v1;

use OpenCloud\Common\Error\BadResponseError;
use OpenCloud\Common\Service\AbstractService;
use OpenStack\ObjectStore\v1\Models\Account;
use OpenStack\ObjectStore\v1\Models\Container;

/**
 * @property \OpenStack\ObjectStore\v1\Api $api
 */
class Service extends AbstractService
{
    /**
     * Retrieves an Account object.
     *
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->model(Account::class);
    }

    /**
     * Retrieves a collection of container resources in a generator format.
     *
     * @param array         $options {@see \OpenStack\ObjectStore\v1\Api::getAccount}
     * @param callable|null $mapFn   Allows a function to be mapped over each element in the collection.
     *
     * @return \Generator
     */
    public function listContainers(array $options = [], callable $mapFn = null): \Generator
    {
        $options = array_merge($options, ['format' => 'json']);
        return $this->model(Container::class)->enumerate($this->api->getAccount(), $options, $mapFn);
    }

    /**
     * Retrieves a Container object and populates its name according to the value provided. Please note that the
     * remote API is not contacted.
     *
     * @param string $name The unique name of the container
     *
     * @return Container
     */
    public function getContainer(string $name = null): Container
    {
        return $this->model(Container::class, ['name' => $name]);
    }

    /**
     * Creates a new container according to the values provided.
     *
     * @param array $data {@see \OpenStack\ObjectStore\v1\Api::putContainer}
     *
     * @return Container
     */
    public function createContainer(array $data): Container
    {
        return $this->getContainer()->create($data);
    }

    /**
     * Checks the existence of a container.
     *
     * @param string $name The name of the container
     *
     * @return bool             TRUE if exists, FALSE if it doesn't
     * @throws BadResponseError Thrown for any non 404 status error
     */
    public function containerExists(string $name): bool
    {
        try {
            $this->execute($this->api->headContainer(), ['name' => $name]);
            return true;
        } catch (BadResponseError $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return false;
            }
            throw $e;
        }
    }
}
