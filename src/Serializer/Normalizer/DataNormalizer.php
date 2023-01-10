<?php

namespace App\Serializer\Normalizer;

use App\Entity\Data;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DataNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private ObjectNormalizer $normalizer)
    {
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Data) {
            throw new InvalidArgumentException(sprintf('The object must implement "%s".', Data::class));
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        if (isset($data['anneeDebut'])) {
            $data['anneeDebut'] = $object->getAnneeDebut()->format('Y');
        }

        if (isset($data['anneeSeparation'])) {
            $data['anneeSeparation'] = $object->getAnneeSeparation()->format('Y');
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Data;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
