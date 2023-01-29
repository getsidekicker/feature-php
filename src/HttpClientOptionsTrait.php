<?php

namespace Sidekicker\FlagrFeature;

trait HttpClientOptionsTrait
{
    /**
     * @return array<mixed>
     */
    protected function createHttpClientOption(): array
    {
        $options = parent::createHttpClientOption();

        if ($this->config->getUsername() && $this->config->getPassword()) {
            $options['auth'] = [
                $this->config->getUsername(),
                $this->config->getPassword()
            ];
        }

        return $options;
    }
}
