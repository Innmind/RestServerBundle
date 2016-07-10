<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\Factory;

use Innmind\Rest\Server\{
    Formats,
    Format\Format,
    Format\MediaType
};
use Innmind\Immutable\{
    Map,
    Set
};

final class FormatsFactory
{
    public function make(array $formats): Formats
    {
        $map = new Map('string', Format::class);

        foreach ($formats as $name => $format) {
            $map = $map->put(
                $name,
                $this->makeFormat($name, $format)
            );
        }

        return new Formats($map);
    }

    private function makeFormat(string $name, array $config): Format
    {
        $mediaTypes = new Set(MediaType::class);

        foreach ($config['media_types'] as $mediaType => $priority) {
            $mediaTypes = $mediaTypes->add(
                new MediaType($mediaType, $priority)
            );
        }

        return new Format($name, $mediaTypes, $config['priority']);
    }
}
