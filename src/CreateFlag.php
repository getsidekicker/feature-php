<?php

namespace Sidekicker\Feature;

use Flagr\Client\Api\FlagApi;
use Flagr\Client\Api\TagApi;
use Flagr\Client\Model\CreateFlagRequest;
use Flagr\Client\Model\CreateTagRequest;
use Flagr\Client\Model\Flag;

class CreateFlag
{

    public function __construct(private FlagApi $flagApi, private TagApi $tagApi)
    {
    }

    public function createFlag(string $name, string $description, array $tags = []): Flag
    {
        $body = new CreateFlagRequest();

        $body->setKey($name);
        $body->setDescription($description);
        $body->setTemplate("simple_boolean_flag");

        $flag = $this->flagApi->createFlag($body);

        foreach ($tags as $tag) {
            $this->tagApi->createTag(
                new CreateTagRequest(['value' => $tag]),
                $flag->getId()
            );
        }

        return $flag;
    }
}
