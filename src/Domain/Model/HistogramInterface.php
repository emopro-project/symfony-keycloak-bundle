<?php

namespace KeycloakAuthBundle\Domain\Model;


interface HistogramInterface
{

    /**
     * Observe a value for a given histogram metric.
     *
     * @param array $name   The name of the histogram metric.
     * @param float  $value  The value to observe.
     * @param array  $labels Optional labels associated with the observation.
     */
    public function observe(array $name, float $value, array $labels = []): void;
}